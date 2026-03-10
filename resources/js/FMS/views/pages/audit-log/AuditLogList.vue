<template>
  <div>
    <!-- Filters Section -->
    <VCard class="mb-4" variant="outlined">
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <VTextField
              v-model="filters.search"
              label="Search"
              placeholder="Search by remarks or ID"
              prepend-inner-icon="bx-search"
              density="compact"
              clearable
              @input="debouncedFetchLogs"
            />
          </VCol>

          <VCol cols="12" md="3">
            <VSelect
              v-model="filters.auditable_type"
              label="Model Type"
              :items="modelTypes"
              item-title="label"
              item-value="value"
              density="compact"
              clearable
              @update:model-value="fetchLogs"
            />
          </VCol>

          <VCol cols="12" md="2">
            <VSelect
              v-model="filters.updated_by"
              label="User"
              :items="users"
              item-title="name"
              item-value="employee_id"
              density="compact"
              clearable
              @update:model-value="fetchLogs"
            />
          </VCol>

          <VCol cols="12" md="2">
            <VTextField
              v-model="filters.date_from"
              label="From Date"
              type="date"
              density="compact"
              clearable
              @update:model-value="fetchLogs"
            />
          </VCol>

          <VCol cols="12" md="2">
            <VTextField
              v-model="filters.date_to"
              label="To Date"
              type="date"
              density="compact"
              clearable
              @update:model-value="fetchLogs"
            />
          </VCol>
        </VRow>

        <VRow>
          <VCol cols="12" class="d-flex justify-end">
            <VBtn
              variant="text"
              color="secondary"
              size="small"
              @click="clearFilters"
            >
              <VIcon icon="bx-reset" class="me-1" />
              Clear Filters
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Data Table -->
    <div class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-x-auto">
      <VTable 
        class="bg-grey-lighten-5 text-body-2 min-w-full" 
        style="border-collapse: separate; border-spacing: 0"
      >
        <thead>
          <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
            <th class="text-left px-6 py-3">Model Type</th>
            <th class="text-left px-6 py-3">Record ID</th>
            <th class="text-left px-6 py-3">Remarks</th>
            <th class="text-left px-6 py-3">User</th>
            <th class="text-left px-6 py-3">Date/Time</th>
            <th class="text-center px-6 py-3">Actions</th>
          </tr>
        </thead>
        <tbody v-if="!loading">
          <tr 
            v-for="(log, index) in auditLogs" 
            :key="log.id" 
            :class="['hover-row', index % 2 === 0 ? 'bg-grey-lighten-5' : '']"
          >
            <!-- Model Type Column -->
            <td class="px-6 py-3">
              <VChip
                size="small"
                color="primary"
                variant="tonal"
              >
                {{ log.model_name }}
              </VChip>
            </td>

            <!-- Record ID Column -->
            <td class="px-6 py-3">
              <span class="font-weight-medium">#{{ log.auditable_id }}</span>
            </td>

            <!-- Remarks Column -->
            <td class="px-6 py-3">
              {{ log.remarks }}
            </td>

            <!-- User Column -->
            <td class="px-6 py-3">
              {{ log.user?.name || 'Unknown' }}
            </td>

            <!-- Date/Time Column -->
            <td class="px-6 py-3">
              <div>{{ formatDate(log.updated_at) }}</div>
              <div class="text-caption text-grey">{{ formatTime(log.updated_at) }}</div>
            </td>

            <!-- Actions Column -->
            <td class="px-6 py-3 text-center whitespace-nowrap">
              <VBtn
                variant="text"
                color="info"
                size="small"
                @click="openDetailsModal(log)"
              >
                <VIcon icon="bx-show" class="me-1" />
                View
              </VBtn>
            </td>
          </tr>
          <tr v-if="auditLogs.length === 0">
            <td colspan="6" class="text-center py-6 text-grey-darken-2">
              No audit logs found.
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="6" class="text-center py-10">
              <VProgressCircular indeterminate color="primary" size="32" width="4" />
              <div class="mt-2 text-grey-darken-2">Loading audit logs...</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </div>

    <!-- Pagination -->
    <div v-if="!loading" class="d-flex flex-wrap justify-between items-center mt-4 gap-4 pagination-wrapper">
      <!-- LEFT: total -->
      <div class="text-grey-darken-2 text-sm">
        Showing {{ auditLogs.length }} of {{ totalItems }} results
      </div>

      <!-- RIGHT: navigation -->
      <div class="d-flex gap-2 items-center ms-auto flex-wrap justify-center">
        <!-- Previous -->
        <VBtn
          class="pagination-btn"
          size="small"
          elevation="0"
          :disabled="currentPage === 1"
          @click="goToPage(currentPage - 1)"
        >
          ‹ Previous
        </VBtn>

        <!-- Pages -->
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

        <!-- Next -->
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

    <!-- Details Modal -->
    <AuditLogDetailsModal
      v-model="showDetailsModal"
      :audit-log="selectedLog"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@fms/utils/api';
import AuditLogDetailsModal from './AuditLogDetailsModal.vue';

// --- State ---
const auditLogs = ref([]);
const modelTypes = ref([]);
const users = ref([]);
const loading = ref(false);
const totalItems = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);

const showDetailsModal = ref(false);
const selectedLog = ref(null);

// --- Filters ---
const filters = ref({
  search: '',
  auditable_type: null,
  updated_by: null,
  date_from: null,
  date_to: null,
});

// --- API Calls ---
const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page: page,
      per_page: 15,
      ...filters.value,
    };

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === '') {
        delete params[key];
      }
    });

    const response = await api.get('/audit-logs', { params });
    auditLogs.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    totalItems.value = response.data.total;
  } catch (error) {
    console.error('Failed to fetch audit logs:', error);
  } finally {
    loading.value = false;
  }
};

const fetchModelTypes = async () => {
  try {
    const response = await api.get('/audit-logs/types');
    modelTypes.value = response.data;
  } catch (error) {
    console.error('Failed to fetch model types:', error);
  }
};

const fetchUsers = async () => {
  try {
    const response = await api.get('/audit-logs/users');
    users.value = response.data;
  } catch (error) {
    console.error('Failed to fetch users:', error);
  }
};

// --- Handlers ---
const goToPage = (page) => {
  if (page < 1 || page > lastPage.value) return;
  fetchLogs(page);
};

const clearFilters = () => {
  filters.value = {
    search: '',
    auditable_type: null,
    updated_by: null,
    date_from: null,
    date_to: null,
  };
  fetchLogs(1);
};

const openDetailsModal = (log) => {
  selectedLog.value = log;
  showDetailsModal.value = true;
};

// --- Utilities ---
const formatDate = (datetime) => {
  if (!datetime) return '-';
  const date = new Date(datetime);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatTime = (datetime) => {
  if (!datetime) return '-';
  const date = new Date(datetime);
  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

// --- Debounced Search ---
let searchTimeout;
const debouncedFetchLogs = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchLogs(1);
  }, 500);
};

// --- Lifecycle ---
onMounted(() => {
  fetchLogs();
  fetchModelTypes();
  fetchUsers();
});
</script>

<style scoped>
.hover-row {
  transition: background-color 0.2s ease;
  cursor: pointer;
}
.hover-row:hover {
  background-color: #f3f4f6;
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

/* Pagination base button */
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

/* Active state */
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

/* Disabled */
.pagination-btn:disabled {
  opacity: 0.5;
  pointer-events: none;
}
</style>