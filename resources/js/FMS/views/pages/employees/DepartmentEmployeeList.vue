<script setup>
import api from "@fms/utils/api";
import { onMounted, ref, computed, watch } from "vue";

const employees = ref([]);
const internalSteps = ref([]);
const loading = ref(true);
const officeId = ref(null);
const setId = ref(null);
const sets = ref([]);
const loadingSets = ref(true);

// Filters
const searchQuery = ref('');

// Toast notification state
const toast = ref({
  show: false,
  message: '',
  color: 'success',
  icon: ''
});

const currentPage = ref(1);
const lastPage = ref(1);
const totalItems = ref(0);

// --- Fetch Office ID from User Info ---
const fetchOfficeId = async () => {
  try {
    const userInfoStr = localStorage.getItem("user_info");
    if (!userInfoStr) {
      console.error("User info not found in localStorage.");
      return;
    }

    const userInfo = JSON.parse(userInfoStr);
    const departmentName = userInfo?.department_details?.name;

    if (!departmentName) {
      console.error("Department name not found in user info.");
      return;
    }

    // Fetch office code by description
    const officeResponse = await api.get(
      `/office-codes/find-by-description/${encodeURIComponent(departmentName)}`
    );
    
    if (officeResponse.data && officeResponse.data.id) {
      officeId.value = officeResponse.data.id;
    }

  } catch (error) {
    console.error("Failed to fetch office ID:", error);
  }
};

// --- Fetch Available Sets ---
const fetchSets = async () => {
  loadingSets.value = true;
  try {
    const response = await api.get('/sets');
    sets.value = response.data;
    
    // Auto-select first set if available
    if (sets.value.length > 0 && !setId.value) {
      setId.value = sets.value[0].id;
    }
  } catch (error) {
    console.error("Failed to fetch sets:", error);
  } finally {
    loadingSets.value = false;
  }
};

// --- Fetch Department Employees with Step Access ---
const fetchEmployees = async (page = 1) => {
  if (!officeId.value || !setId.value) {
    loading.value = false;
    return;
  }

  loading.value = true;
  try {
    const params = {
      office_id: officeId.value,
      set_id: setId.value,
      page: page
    };

    if (searchQuery.value) params.search = searchQuery.value;

    const response = await api.get('/department-employees', { params });
    
    employees.value = response.data.employees.data;
    internalSteps.value = response.data.internal_steps;
    currentPage.value = response.data.employees.current_page;
    lastPage.value = response.data.employees.last_page;
    totalItems.value = response.data.employees.total;

  } catch (error) {
    console.error("Failed to fetch employees:", error);
    employees.value = [];
    internalSteps.value = [];
    totalItems.value = 0;
  } finally {
    loading.value = false;
  }
};

// --- Toggle Step Access ---
const toggleStepAccess = async (employeeId, internalStepId, currentAccess) => {
  try {
    await api.post('/department-employees/step-access', {
      employee_id: employeeId,
      internal_step_id: internalStepId,
      has_access: !currentAccess
    });

    showToast('Access updated successfully', 'success');
    
    // Update local state
    const employee = employees.value.find(e => e.id === employeeId);
    if (employee && employee.access_map) {
      employee.access_map[internalStepId] = !currentAccess;
    }

  } catch (error) {
    console.error("Failed to update step access:", error);
    showToast('Failed to update access', 'error');
  }
};

// --- Watchers ---
watch(officeId, (newOfficeId) => {
  if (newOfficeId) {
    fetchSets();
  }
});

watch([officeId, setId], ([newOfficeId, newSetId]) => {
  if (newOfficeId && newSetId) {
    fetchEmployees(1);
  }
});

// Watch for search changes with debounce
let searchTimeout = null;
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    if (officeId.value && setId.value) {
      fetchEmployees(1);
    }
  }, 500);
});

const emptyStateMessage = computed(() => {
  if (!officeId.value) return "Loading office configuration...";
  if (!setId.value) return "Please select a set to view employees.";
  if (searchQuery.value) {
    return "No employees found matching your search.";
  }
  return "No employees found in your department.";
});

const getFullName = (employee) => {
  let name = employee.first_name;
  if (employee.middle_name) {
    name += ' ' + employee.middle_name;
  }
  name += ' ' + employee.last_name;
  if (employee.name_extension) {
    name += ' ' + employee.name_extension;
  }
  return name;
};

const clearFilters = () => {
  searchQuery.value = '';
};

const showToast = (message, type = 'success') => {
  const config = {
    success: { color: 'success', icon: 'bx-check-circle' },
    error: { color: 'error', icon: 'bx-error-circle' },
    warning: { color: 'warning', icon: 'bx-error' },
    info: { color: 'info', icon: 'bx-info-circle' }
  };
  
  const { color, icon } = config[type] || config.success;
  
  toast.value = {
    show: true,
    message,
    color,
    icon
  };
};

const goToPage = (page) => {
  if (page < 1 || page > lastPage.value) return;
  fetchEmployees(page);
};

onMounted(() => {
  fetchOfficeId();
});
</script>

<template>
  <div>
    <!-- Set Selector and Filters -->
    <VCard class="mb-6" elevation="0" style="border: 1px solid #e0e0e0;">
      <VCardText>
        <VRow>
          <VCol cols="12" md="4">
            <VTextField
              v-model="searchQuery"
              label="Search Name or Employee Number"
              variant="outlined"
              density="comfortable"
              clearable
              prepend-inner-icon="bx-search"
              hide-details
            />
          </VCol>

          <VCol cols="12" md="2">
            <VBtn
              color="grey"
              variant="outlined"
              block
              size="default"
              @click="clearFilters"
              :disabled="!setId"
            >
              <VIcon icon="bx-refresh" class="me-1" size="18" />
              Clear
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Table -->
    <div v-if="setId" class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-x-auto">
      <VTable
        class="bg-grey-lighten-5 text-body-2"
        style="border-collapse: separate; border-spacing: 0"
        density="compact"
      >
        <thead>
          <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
            <th class="text-left px-4 py-2" style="min-width: 200px;">Employee Name</th>
            <th 
              v-for="step in internalSteps" 
              :key="step.id" 
              class="text-center px-3 py-2"
              style="min-width: 120px;"
            >
              {{ step.approval_title }}
            </th>
          </tr>
        </thead>

        <tbody v-if="!loading">
          <tr
            v-for="(employee, i) in employees"
            :key="employee.id"
            :class="[i % 2 === 0 ? 'bg-grey-lighten-5' : 'bg-white']"
          >
            <td class="px-4 py-2">
              <span class="text-body-2">{{ getFullName(employee) }}</span>
            </td>
            
            <td 
              v-for="step in internalSteps" 
              :key="step.id" 
              class="px-3 py-2"
            >
              <div class="d-flex justify-center align-center">
                <VSwitch
                  :model-value="employee.access_map[step.id]"
                  color="primary"
                  hide-details
                  density="default"
                  @update:model-value="toggleStepAccess(employee.id, step.id, employee.access_map[step.id])"
                  class="centered-switch"
                />
              </div>
            </td>
          </tr>

          <tr v-if="employees.length === 0">
            <td :colspan="internalSteps.length + 1" class="text-center py-6 text-grey-darken-2">
              {{ emptyStateMessage }}
            </td>
          </tr>
        </tbody>

        <tbody v-else>
          <tr>
            <td :colspan="internalSteps.length + 1" class="text-center py-10">
              <VProgressCircular indeterminate color="primary" size="32" width="4" />
              <div class="mt-2 text-grey-darken-2">Loading employees...</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </div>

    <!-- Empty state when no set selected -->
    <VCard v-else class="text-center py-10">
      <VCardText>
        <VIcon icon="bx-filter" size="48" color="grey-lighten-1" class="mb-3" />
        <p class="text-h6 text-grey-darken-2 mb-2">No Set Selected</p>
        <p class="text-body-2 text-grey-darken-1">Please select a set to view employee access permissions</p>
      </VCardText>
    </VCard>

    <!-- Pagination -->
    <div v-if="!loading && employees.length > 0 && setId" class="d-flex flex-wrap justify-between items-center mt-4 gap-4 pagination-wrapper">
      <div class="text-grey-darken-2 text-sm">
        Showing {{ employees.length }} of {{ totalItems }} results
      </div>

      <div class="d-flex gap-2 items-center ms-auto flex-wrap justify-center">
        <VBtn
          class="pagination-btn"
          size="small"
          elevation="0"
          :disabled="currentPage === 1"
          @click="goToPage(currentPage - 1)"
        >
          ‹ Previous
        </VBtn>

        <VBtn
          v-for="page in lastPage"
          :key="page"
          size="small"
          elevation="0"
          :class="page === currentPage ? 'pagination-active' : 'pagination-btn'"
          @click="goToPage(page)"
        >
          {{ page }}
        </VBtn>

        <VBtn
          class="pagination-btn"
          size="small"
          elevation="0"
          :disabled="currentPage === lastPage"
          @click="goToPage(currentPage + 1)"
        >
          Next ›
        </VBtn>
      </div>
    </div>

    <!-- Toast Notification -->
    <VSnackbar v-model="toast.show" :color="toast.color" location="top right" :timeout="3000">
      <div class="d-flex align-center">
        <VIcon :icon="toast.icon" class="me-2" />
        {{ toast.message }}
      </div>
      <template v-slot:actions>
        <VBtn variant="text" icon size="small" color="white" @click="toast.show = false">
          <VIcon icon="bx-x" />
        </VBtn>
      </template>
    </VSnackbar>
  </div>
</template>

<style scoped>
/* Center the switch */
.centered-switch {
  margin: 0 auto;
}

/* Make switch bigger by removing compact density */
.centered-switch :deep(.v-selection-control) {
  min-height: auto;
}

/* Pagination */
.pagination-wrapper {
  flex-direction: row;
}

@media (max-width: 640px) {
  .pagination-wrapper {
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }
}

.pagination-btn {
  background-color: #f3f4f6 !important;
  color: #374151 !important;
  border-radius: 9999px !important;
  min-width: 36px;
  height: 36px;
  padding: 0 12px;
  font-weight: 500;
  text-transform: none !important;
  transition: all 0.2s ease;
  border: none !important;
  box-shadow: none !important;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #2563eb !important;
  color: #ffffff !important;
}

.pagination-active {
  background-color: #2563eb !important;
  color: #ffffff !important;
  border-radius: 9999px !important;
  min-width: 36px;
  height: 36px;
  padding: 0 14px;
  font-weight: 600;
  border: none !important;
  box-shadow: none !important;
}

.pagination-btn:disabled {
  opacity: 0.5;
  pointer-events: none;
}
</style>