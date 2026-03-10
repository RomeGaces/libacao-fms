<script setup>
import api from "@fms/utils/api";
import { onMounted, ref, computed, watch } from 'vue'
import DeleteObrModal from './DeleteObrModal.vue'
import ObrModal from './ObrModal.vue'
import ProcessObrModal from './ProcessObr.vue'

const obrs = ref([])
const loading = ref(true)

const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showProcessModal = ref(false)
const selectedObr = ref(null)

// Filter state - true = archived, false = active
const showArchived = ref(false)

// Pagination state
const currentPage = ref(1)
const lastPage = ref(1)
const totalItems = ref(0)

// Fetch OBRs with pagination
const fetchObrs = async (page = 1) => {
  loading.value = true
  try {
    const response = await api.get('/obr-requests', {
      params: {
        page: page,
        view_mode: showArchived.value ? 'archived' : 'active'
      }
    })
    obrs.value = response.data.data
    currentPage.value = response.data.current_page
    lastPage.value = response.data.last_page
    totalItems.value = response.data.total
  } catch (error) {
    console.error('Failed to fetch OBRs:', error)
    obrs.value = []
    totalItems.value = 0
  } finally {
    loading.value = false
  }
}

// Watch for view mode changes
watch(showArchived, () => {
  fetchObrs(1)
})

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
}

// Calculate total amount for an OBR
const calculateTotal = (obrObjects) => {
    if (!obrObjects || obrObjects.length === 0) return 0;
    return obrObjects.reduce((sum, item) => sum + parseFloat(item.amount), 0);
}

// Get the requesting department
const getRequestingDepartment = (obr) => {
  return obr?.pao_request?.office_code?.description || 'N/A';
}

// Get the approving department from the latest status
const getApprovingDepartment = (obr) => {
  return obr?.latest_status?.internal_step?.step?.office_code?.description || 'N/A';
}

// Helper function for status color
const getStatusColor = (statusTitle) => {
  if (!statusTitle) {
    return 'grey';
  }
  return statusTitle.toLowerCase() === 'draft' ? 'grey' : 'success';
};

// Check if OBR is in draft status
const isDraft = (obr) => {
  return obr.latest_status?.internal_step?.approval_title?.toLowerCase() === 'draft';
};

// Check if OBR is archived
const isArchived = (obr) => {
  return obr.is_archived === true || obr.is_archived === 1;
}

const emptyStateMessage = computed(() => {
  if (showArchived.value) {
    return "No archived OBRs found.";
  }
  return "No active OBRs found.";
});

const handleEdit = (obr) => {
  // Allow viewing all OBRs (read-only mode will be handled in the modal)
  selectedObr.value = obr ? JSON.parse(JSON.stringify(obr)) : null
  showEditModal.value = true
}

const handleSave = async (updatedObr, done) => {
  try {
    if (updatedObr.id) {
      await api.put(`/obr-requests/${updatedObr.id}`, updatedObr)
    } else {
      await api.post(`/obr-requests`, updatedObr)
    }
    await fetchObrs(currentPage.value)
    done(true)
  } catch (error) {
    console.error('Failed to save OBR:', error)
    done(false)
  }
}

const handleDelete = (obr, event) => {
  event.stopPropagation()
  selectedObr.value = obr
  showDeleteModal.value = true
}

const confirmDelete = async (done) => {
  if (!selectedObr.value) return
  try {
    await api.delete(`/obr-requests/${selectedObr.value.id}`)
    if (obrs.value.length === 1 && currentPage.value > 1) {
        await fetchObrs(currentPage.value - 1)
    } else {
        await fetchObrs(currentPage.value)
    }
    done(true)
  } catch (error) {
    console.error('Failed to delete OBR:', error)
    done(false)
  }
}

const handleProcess = (obr) => {
  selectedObr.value = obr
  showProcessModal.value = true
}

const confirmProcess = async (done) => {
  if (!selectedObr.value) return
  try {
    await api.post(`/obr-requests/${selectedObr.value.id}/process`)
    await fetchObrs(currentPage.value)
    done(true)
  } catch (error) {
    console.error('Failed to process OBR:', error)
    done(false)
  }
}

const goToPage = (page) => {
  if (page < 1 || page > lastPage.value) return
  fetchObrs(page)
}

onMounted(() => {
  fetchObrs()
})
</script>

<template>
  <div class="d-flex justify-space-between align-center mb-4 flex-wrap gap-3">
    <!-- Toggle Switch for Archived Items -->
    <div class="d-flex align-center gap-2">
      <VSwitch
        v-model="showArchived"
        color="warning"
        hide-details
        density="compact"
      >
        <template v-slot:label>
          <span class="text-body-2 font-weight-medium">
            {{ showArchived ? 'Archived Items' : 'Active Items' }}
          </span>
        </template>
      </VSwitch>
    </div>

    <!-- Add Button -->
    <VBtn variant="outlined" color="primary" @click="handleEdit(null)">+ Add OBR</VBtn>
  </div>

  <div class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-x-auto">
    <VTable class="bg-grey-lighten-5 text-body-2 min-w-full" style="border-collapse: separate; border-spacing: 0">
      <thead>
        <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
          <th class="text-left px-6 py-3">Requesting Department</th>
          <th class="text-left px-6 py-3">Approving Department</th>
          <th class="text-left px-6 py-3">Current Status</th>
          <th class="text-left px-6 py-3">OBR Number</th>
          <th class="text-left px-6 py-3">Office Address</th>
          <th class="text-left px-6 py-3">Total Amount</th>
          <th class="text-left px-6 py-3">Created At</th>
          <th class="text-center px-6 py-3" style="width: 80px;">Actions</th>
        </tr>
      </thead>
      
      <tbody v-if="!loading">
        <tr 
          v-for="(obr, index) in obrs" 
          :key="obr.id" 
          :class="[
            'hover-row', 
            index % 2 === 0 ? 'bg-grey-lighten-5' : '',
            isArchived(obr) ? 'archived-row' : ''
          ]" 
          @click="handleEdit(obr)"
        >
          <td class="px-6 py-3">
            <div class="d-flex align-center gap-2">
              <span>{{ getRequestingDepartment(obr) }}</span>
              <VChip
                v-if="isArchived(obr)"
                color="warning"
                size="x-small"
                variant="tonal"
                label
              >
                Archived
              </VChip>
            </div>
          </td>

          <td class="px-6 py-3">
            {{ getApprovingDepartment(obr) }}
          </td>

          <td class="px-6 py-3">
            <div v-if="obr.latest_status && obr.latest_status.internal_step">
              <VChip
                :color="getStatusColor(obr.latest_status.internal_step.approval_title)"
                variant="tonal"
                size="small"
                label
              >
                {{ obr.latest_status.internal_step.approval_title }}
              </VChip>
            </div>
            <div v-else class="text-grey-darken-1">
              N/A
            </div>
          </td>
          
          <td class="px-6 py-3">{{ obr.obr_no }}</td>
          <td class="px-6 py-3">{{ obr.office_address }}</td>
          <td class="px-6 py-3 font-weight-medium">{{ formatCurrency(calculateTotal(obr.obr_objects)) }}</td>
          <td class="px-6 py-3">{{ formatDate(obr.created_at) }}</td>
          
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
        <tr v-if="obrs.length === 0">
          <td colspan="8" class="text-center py-6 text-grey-darken-2">
            {{ emptyStateMessage }}
          </td>
        </tr>
      </tbody>
      
      <tbody v-else>
        <tr>
          <td colspan="8" class="text-center py-10">
            <VProgressCircular indeterminate color="primary" size="32" width="4" />
            <div class="mt-2 text-grey-darken-2">Loading OBRs...</div>
          </td>
        </tr>
      </tbody>
    </VTable>
  </div>

  <!-- Pagination -->
  <div v-if="!loading && obrs.length > 0" class="d-flex flex-wrap justify-between items-center mt-4 gap-4 pagination-wrapper">
    <div class="text-grey-darken-2 text-sm">
      Showing {{ obrs.length }} of {{ totalItems }} results
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

  <ObrModal v-model="showEditModal" :obr="selectedObr" @save="handleSave" />
  <DeleteObrModal v-model="showDeleteModal" :obr="selectedObr" @confirm="confirmDelete" />
  <ProcessObrModal v-model="showProcessModal" :obr="selectedObr" @confirm="confirmProcess" />
</template>

<style scoped>
.hover-row {
  transition: background-color 0.2s ease;
  cursor: pointer;
}
.hover-row:hover {
  background-color: #f3f4ff;
}

/* Archived row styling */
.archived-row {
  background-color: #fff3e0 !important;
  opacity: 0.9;
}

.archived-row:hover {
  background-color: #ffe0b2 !important;
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

.responsive-add {
  flex-wrap: wrap;
}
.responsive-add .v-btn {
  margin-left: auto;
}
</style>