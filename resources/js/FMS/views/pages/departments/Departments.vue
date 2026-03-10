<script setup>
import api from "@fms/utils/api";
import { onMounted, ref } from 'vue'
import DeleteDepartmentModal from './DeleteDepartmentModal.vue'
import DepartmentModal from './DepartmentModal.vue'

const departments = ref([])
const loading = ref(true)

const showEditModal = ref(false)
const showDeleteModal = ref(false)

const selectedDept = ref(null)

// Fetch departments
const fetchDepartments = async () => {
  loading.value = true
  try {
    const response = await api.get('/departments')
    departments.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch departments:', error)
  } finally {
    loading.value = false
  }
}

// Format date
const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Open Edit Modal
const handleEdit = (dept) => {
  selectedDept.value = dept ? JSON.parse(JSON.stringify(dept)) : { name: '', department_code: '' }
  showEditModal.value = true
}

// Save department
const handleSave = async (updatedDept, done) => {
  try {
    if (updatedDept.id) {
      await api.put(`/departments/${updatedDept.id}`, updatedDept)
    } else {
      await api.post(`/departments`, updatedDept)
    }
    await fetchDepartments()
    done(true)
  } catch (error) {
    console.error('Failed to save department:', error)
    done(false)
  }
}

// Open Delete Modal
const handleDelete = (dept) => {
  selectedDept.value = dept
  showDeleteModal.value = true
}

// Confirm delete
const confirmDelete = async (done) => {
  if (!selectedDept.value) return
  try {
    await axios.delete(`/departments/${selectedDept.value.id}`)
    await fetchDepartments()
    done(true)
  } catch (error) {
    console.error('Failed to delete department:', error)
    done(false)
  }
}

onMounted(() => {
  fetchDepartments()
})
</script>

<template>
  <div class="d-flex justify-end mb-4 responsive-add">
    <VBtn variant="outlined" color="primary" @click="handleEdit(null)">+ Add Department
    </VBtn>
  
  </div>

  <div class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-hidden">
    <VTable
      class="bg-grey-lighten-5 text-body-2"
      style="width: 100%; border-collapse: separate; border-spacing: 0"
    >
      <thead>
        <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
          <th class="text-left px-6 py-3">ID</th>
          <th class="text-left px-6 py-3">Department Name</th>
          <th class="text-left px-6 py-3">Code</th>
          <th class="text-left px-6 py-3">Created At</th>
          <th class="text-left px-6 py-3">Updated At</th>
          <th class="text-left px-6 py-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Spinner while loading -->
        <tr v-if="loading">
          <td colspan="6" class="text-center py-10">
            <VProgressCircular
              indeterminate
              color="primary"
              size="32"
              width="4"
            />
            <div class="mt-2 text-grey-darken-2">Loading departments...</div>
          </td>
        </tr>

        <!-- Data rows -->
        <tr
          v-for="(dept, index) in departments"
          :key="dept.id"
          :class="['hover-row', index % 2 === 0 ? 'bg-grey-lighten-5' : '']"
          @click="handleEdit(dept)"
        >
          <td class="px-6 py-3">{{ dept.id }}</td>
          <td class="px-6 py-3">{{ dept.name }}</td>
          <td class="px-6 py-3">{{ dept.department_code }}</td>
          <td class="px-6 py-3">{{ formatDate(dept.created_at) }}</td>
          <td class="px-6 py-3">{{ formatDate(dept.updated_at) }}</td>
          <td class="px-6 py-3 whitespace-nowrap">
            <VBtn
              variant="text"
              color="error"
              size="small"
              @click.stop="handleDelete(dept)"
            >
              <VIcon icon="bx-trash" class="me-1" />
            </VBtn>
          </td>
        </tr>

        <!-- Empty state -->
        <tr v-if="!loading && departments.length === 0">
          <td colspan="6" class="text-center py-6 text-grey-darken-2">
            No departments found.
          </td>
        </tr>
      </tbody>
    </VTable>

    <!-- Edit Modal -->
    <DepartmentModal
      v-model="showEditModal"
      :department="selectedDept"
      @save="handleSave"
    />

    <!-- Delete Confirmation Modal -->
    <DeleteDepartmentModal
      v-model="showDeleteModal"
      :department="selectedDept"
      @confirm="confirmDelete"
    />
  </div>
</template>

<style scoped>
/* Lighter hover effect for rows */
.hover-row {
  transition: background-color 0.2s ease;
  cursor: pointer;
}

.hover-row:hover {
  background-color: #f8f9fa; /* very light hover */
}
</style>
