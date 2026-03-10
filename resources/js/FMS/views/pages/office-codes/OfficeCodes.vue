<script setup>
import api from "@fms/utils/api";
import { onMounted, ref } from 'vue'
import DeleteOfficeCodesModal from './DeleteOfficeCodesModal.vue'
import OfficeCodesModal from './OfficeCodesModal.vue'

const officeCodes = ref([])
const loading = ref(true)

const showEditModal = ref(false)
const showDeleteModal = ref(false)

const selectedOfficeCode = ref(null)
// Pagination state
const currentPage = ref(1)
const lastPage = ref(1)
const totalItems = ref(0)

// Fetch Office Codes with pagination
const fetchOfficeCodes = async (page = 1) => {
  loading.value = true
  try {
    const response = await api.get(`/office-codes?page=${page}`)
    officeCodes.value = response.data.data
    currentPage.value = response.data.current_page
    lastPage.value = response.data.last_page
    totalItems.value = response.data.total
  } catch (error) {
    console.error('Failed to fetch office codes:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const handleEdit = (officeCode) => {
  selectedOfficeCode.value = officeCode ? JSON.parse(JSON.stringify(officeCode)) : { office_code: '', description: '' }
  showEditModal.value = true
}

const handleSave = async (updatedOfficeCode, done) => {
  try {
    if (updatedOfficeCode.id) {
      await api.put(`/office-codes/${updatedOfficeCode.id}`, updatedOfficeCode)
    } else {
      await api.post(`/office-codes`, updatedOfficeCode)
    }
    await fetchOfficeCodes(currentPage.value)
    done(true)
  } catch (error) {
    console.error('Failed to save office code:', error)
    done(false)
  }
}

const handleDelete = (officeCode) => {
  selectedOfficeCode.value = officeCode
  showDeleteModal.value = true
}

const confirmDelete = async (done) => {
  if (!selectedOfficeCode.value) return
  try {
    await api.delete(`/office-codes/${selectedOfficeCode.value.id}`)
    await fetchOfficeCodes(currentPage.value)
    done(true)
  } catch (error) {
    console.error('Failed to delete office code:', error)
    done(false)
  }
}

const goToPage = (page) => {
  if (page < 1 || page > lastPage.value) return
  fetchOfficeCodes(page)
}

onMounted(() => {
  fetchOfficeCodes()
})
</script>

<template>
  <!-- Add button row -->
  <div class="d-flex justify-end mb-4 responsive-add">
    <VBtn variant="outlined" color="primary" @click="handleEdit(null)">+ Add Office Code</VBtn>
  </div>

  <!-- Table -->
  <div class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-x-auto">
    <VTable class="bg-grey-lighten-5 text-body-2 min-w-full" style="border-collapse: separate; border-spacing: 0">
      <thead>
        <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
          <th class="text-left px-6 py-3">Office Code</th>
          <th class="text-left px-6 py-3">Description</th>
          <th class="text-left px-6 py-3">Created At</th>
          <th class="text-left px-6 py-3">Updated At</th>
          <th class="text-left px-6 py-3">Actions</th>
        </tr>
      </thead>
      <tbody v-if="!loading">
        <tr v-for="(officeCode, index) in officeCodes" :key="officeCode.id" :class="['hover-row', index % 2 === 0 ? 'bg-grey-lighten-5' : '']" @click="handleEdit(officeCode)">
          <td class="px-6 py-3">{{ officeCode.office_code }}</td>
          <td class="px-6 py-3">{{ officeCode.description }}</td>
          <td class="px-6 py-3">{{ formatDate(officeCode.created_at) }}</td>
          <td class="px-6 py-3">{{ formatDate(officeCode.updated_at) }}</td>
          <td class="px-6 py-3 whitespace-nowrap">
            <VBtn variant="text" color="error" size="small" @click.stop="handleDelete(officeCode)">
              <VIcon icon="bx-trash" class="me-1" />
            </VBtn>
          </td>
        </tr>
        <tr v-if="officeCodes.length === 0">
          <td colspan="6" class="text-center py-6 text-grey-darken-2">No office codes found.</td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr>
          <td colspan="6" class="text-center py-10">
            <VProgressCircular indeterminate color="primary" size="32" width="4" />
            <div class="mt-2 text-grey-darken-2">Loading office codes...</div>
          </td>
        </tr>
      </tbody>
    </VTable>
  </div>

  <!-- Pagination -->
  <div v-if="!loading" class="d-flex flex-wrap justify-between items-center mt-4 gap-4 pagination-wrapper">
    <!-- LEFT: total -->
    <div class="text-grey-darken-2 text-sm">
      Showing {{ officeCodes.length }} of {{ totalItems }} results
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

  <OfficeCodesModal v-model="showEditModal" :office-code="selectedOfficeCode" @save="handleSave" />
  <DeleteOfficeCodesModal v-model="showDeleteModal" :office-code="selectedOfficeCode" @confirm="confirmDelete" />
</template>

<style scoped>
.hover-row {
  transition: background-color 0.2s ease;
  cursor: pointer;
}
.hover-row:hover {
  background-color: #f3f4f6;
}

/* Responsive add button */
.responsive-add {
  flex-wrap: wrap;
}
.responsive-add .v-btn {
  margin-left: auto;
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
