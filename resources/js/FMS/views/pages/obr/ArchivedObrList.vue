<script setup>
import api from "@fms/utils/api";
import { onMounted, ref, computed, watch } from "vue";
import ViewArchivedObrModal from './ViewArchivedObrModal.vue';
import DeleteArchivedModal from './DeleteArchivedModal.vue';

const archivedObrs = ref([]);
const loading = ref(true);
const officeId = ref(null);
const selectedObr = ref(null);
const showViewModal = ref(false);
const showDeleteModal = ref(false);

// Filters
const searchQuery = ref('');
const selectedArchiver = ref(null);
const dateFrom = ref(null);
const dateTo = ref(null);
const archivers = ref([]);

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

// --- Fetch Office ID from Description ---
const fetchOfficeId = async () => {
  try {
    const userInfoStr = localStorage.getItem("user_info");
    if (!userInfoStr) {
      console.error("User info not found in localStorage.");
      return;
    }

    const userInfo = JSON.parse(userInfoStr);
    const description = userInfo?.department_details?.name;

    if (!description) {
      console.error("Department name not found in user info.");
      return;
    }

    const response = await api.get(
      `/office-codes/find-by-description/${encodeURIComponent(description)}`
    );
    
    if (response.data && response.data.id) {
      officeId.value = response.data.id;
    } else {
      console.error("Office ID not found for description:", description);
    }
  } catch (error) {
    console.error("Failed to fetch office ID:", error);
  }
};

// --- Fetch Archivers for Filter ---
const fetchArchivers = async () => {
  try {
    const response = await api.get('/archived-obrs/filters/archivers');
    archivers.value = response.data;
  } catch (error) {
    console.error("Failed to fetch archivers:", error);
  }
};

// --- Fetch Archived OBRs ---
const fetchArchivedObrs = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page: page
    };

    // Add filters to params only if they have values
    if (searchQuery.value) params.search = searchQuery.value;
    if (selectedArchiver.value) params.archived_by = selectedArchiver.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;

    const response = await api.get('/archived-obrs', { params });
    
    archivedObrs.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    totalItems.value = response.data.total;
  } catch (error) {
    console.error("Failed to fetch archived OBRs:", error);
    archivedObrs.value = [];
    totalItems.value = 0;
  } finally {
    loading.value = false;
  }
};

// --- Watchers ---
watch(officeId, (newOfficeId) => {
  if (newOfficeId) {
    fetchArchivers();
    fetchArchivedObrs(1);
  }
});

// Watch for filter changes with debounce for search
let searchTimeout = null;
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchArchivedObrs(1);
  }, 500); // 500ms debounce
});

watch([selectedArchiver, dateFrom, dateTo], () => {
  fetchArchivedObrs(1);
});

const emptyStateMessage = computed(() => {
  if (!officeId.value) return "Loading office configuration...";
  if (searchQuery.value || selectedArchiver.value || dateFrom.value || dateTo.value) {
    return "No archived OBRs found matching your filters.";
  }
  return "No archived OBRs found.";
});

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
  });
};

const formatCurrency = (v) =>
  new Intl.NumberFormat("en-PH", { style: "currency", currency: "PHP" }).format(
    v
  );

const calculateTotal = (obrObjects) =>
  obrObjects?.reduce((s, i) => s + parseFloat(i.amount), 0) || 0;

const getInitials = (name) => {
  if (!name) return 'U';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return name[0].toUpperCase();
};

const handleRowClick = (obr) => {
  selectedObr.value = obr ? JSON.parse(JSON.stringify(obr)) : null;
  showViewModal.value = true;
};

const handleDelete = (obr, event) => {
  // Prevent row click when clicking delete button
  event.stopPropagation();
  selectedObr.value = obr;
  showDeleteModal.value = true;
};

const confirmDelete = async (done) => {
  try {
    await api.delete(`/archived-obrs/${selectedObr.value.id}`);
    
    showToast('OBR permanently deleted successfully', 'success');
    
    await fetchArchivedObrs(currentPage.value);
    done(true);
  } catch (error) {
    console.error("Failed to delete archived OBR:", error);
    showToast('Failed to delete archived OBR', 'error');
    done(false);
  }
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedArchiver.value = null;
  dateFrom.value = null;
  dateTo.value = null;
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
  fetchArchivedObrs(page);
};

onMounted(() => {
  fetchOfficeId();
});
</script>

<template>
  <div>
    <!-- Filters -->
    <VCard class="mb-6" elevation="0" style="border: 1px solid #e0e0e0;">
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <VTextField
              v-model="searchQuery"
              label="Search OBR Number or Address"
              variant="outlined"
              density="compact"
              clearable
              prepend-inner-icon="bx-search"
              hide-details
            />
          </VCol>

          <VCol cols="12" md="3">
            <VSelect
              v-model="selectedArchiver"
              :items="archivers"
              item-title="archiver_name"
              item-value="archived_by"
              label="Archived By"
              variant="outlined"
              density="compact"
              clearable
              hide-details
            />
          </VCol>

          <VCol cols="12" md="2">
            <VTextField
              v-model="dateFrom"
              label="Date From"
              type="date"
              variant="outlined"
              density="compact"
              hide-details
            />
          </VCol>

          <VCol cols="12" md="2">
            <VTextField
              v-model="dateTo"
              label="Date To"
              type="date"
              variant="outlined"
              density="compact"
              hide-details
            />
          </VCol>

          <VCol cols="12" md="2">
            <VBtn
              color="grey"
              variant="outlined"
              block
              @click="clearFilters"
            >
              <VIcon icon="bx-refresh" class="me-1" size="18" />
              Clear
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Table -->
    <div class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-x-auto">
      <VTable
        class="bg-grey-lighten-5 text-body-2 min-w-full"
        style="border-collapse: separate; border-spacing: 0"
      >
        <thead>
          <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
            <th class="text-left px-6 py-3">Archived Date</th>
            <th class="text-left px-6 py-3">OBR Number</th>
            <th class="text-left px-6 py-3">Office</th>
            <th class="text-left px-6 py-3">Total Amount</th>
            <th class="text-left px-6 py-3">Archived By</th>
            <th class="text-center px-6 py-3" style="width: 80px;">Actions</th>
          </tr>
        </thead>

        <tbody v-if="!loading">
          <tr
            v-for="(obr, i) in archivedObrs"
            :key="obr.id"
            :class="['hover-row', i % 2 === 0 ? 'bg-grey-lighten-5' : '']"
            @click="handleRowClick(obr)"
          >
            <td class="px-6 py-3">
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ formatDate(obr.latest_archive?.archived_at) }}</span>
                <span class="text-caption text-grey-darken-1">{{ formatTime(obr.latest_archive?.archived_at) }}</span>
              </div>
            </td>
            <td class="px-6 py-3 font-weight-medium text-primary">{{ obr.obr_no }}</td>
            <td class="px-6 py-3">{{ obr.pao_request?.office_code?.description || 'N/A' }}</td>
            <td class="px-6 py-3 font-weight-medium">
              {{ formatCurrency(calculateTotal(obr.obr_objects)) }}
            </td>
            <td class="px-6 py-3">
              <div class="d-flex align-center gap-2">
                <VAvatar size="32" color="primary" class="user-avatar">
                  <span class="text-caption font-weight-bold">{{ getInitials(obr.latest_archive?.archived_by_user?.name) }}</span>
                </VAvatar>
                <span>{{ obr.latest_archive?.archived_by_user?.name || 'Unknown' }}</span>
              </div>
            </td>
            <td class="px-6 py-3 text-center">
              <VBtn
                icon
                size="small"
                variant="text"
                color="error"
                class="delete-icon-btn"
                @click="handleDelete(obr, $event)"
              >
                <VIcon icon="bx-trash" size="20" />
              </VBtn>
            </td>
          </tr>

          <tr v-if="archivedObrs.length === 0">
            <td colspan="6" class="text-center py-6 text-grey-darken-2">
              {{ emptyStateMessage }}
            </td>
          </tr>
        </tbody>

        <tbody v-else>
          <tr>
            <td colspan="6" class="text-center py-10">
              <VProgressCircular indeterminate color="primary" size="32" width="4" />
              <div class="mt-2 text-grey-darken-2">Loading archived OBRs...</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && archivedObrs.length > 0" class="d-flex flex-wrap justify-between items-center mt-4 gap-4 pagination-wrapper">
      <div class="text-grey-darken-2 text-sm">
        Showing {{ archivedObrs.length }} of {{ totalItems }} results
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

    <!-- Modals -->
    <ViewArchivedObrModal v-model="showViewModal" :obr="selectedObr" />
    <DeleteArchivedModal v-model="showDeleteModal" :obr="selectedObr" @confirm="confirmDelete" />

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
.hover-row {
  transition: background-color 0.2s ease;
  cursor: pointer;
}

.hover-row:hover {
  background-color: #f3f4ff;
}

/* User Avatar - Blue circle with bold white letters */
.user-avatar {
  background-color: #2196F3 !important;
}

.user-avatar .text-caption {
  color: white !important;
  font-weight: 700 !important;
  font-size: 0.75rem !important;
}

/* Delete icon button with circular background on hover */
.delete-icon-btn {
  transition: all 0.2s ease;
}

.delete-icon-btn:hover {
  background-color: rgba(244, 67, 54, 0.1) !important;
  border-radius: 50%;
}

.delete-icon-btn:hover .v-icon {
  color: #f44336 !important;
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