<script setup>
import DepartmentModal from '@fms/views/pages/departments/DepartmentModal.vue'
import Departments from '@fms/views/pages/departments/Departments.vue'
import api from "@fms/utils/api";
import { ref } from 'vue'

// Icon alias (your custom imported icon)
import officeBuilding from '@/../icons/office_building_3d.png'

const showModal = ref(false)
const selectedDept = ref(null)

// Token for simplicity (could move to .env or store)

// For triggering refresh in Departments.vue
const refreshKey = ref(0)

function editDepartment(dept) {
  selectedDept.value = { ...dept }
  showModal.value = true
}

function deleteDepartment(dept) {
  console.log('Delete department:', dept)
  // 🔹 Add API call if you want to support deletion
}

async function saveDepartment(dept, done) {
  try {
    if (dept.id) {
      // 🔹 Update existing department
      await api.put(`/departments/${dept.id}`, dept)
    } else {
      // 🔹 Create new department
      await axios.post(`/departments`, dept)
    }

    // ✅ Trigger table refresh
    refreshKey.value++

    done(true) // stop spinner + close modal
  } catch (error) {
    console.error('Failed to save department:', error)
    done(false) // stop spinner but keep modal open
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <!-- Card Title with Action Button -->
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <!-- 🔹 Office Building Icon + Title -->
            <div class="d-flex align-center">
              <img
                :src="officeBuilding"
                alt="Office Icon"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">List of Departments</span>
            </div>
          </div>
        </template>

        <!-- Description -->
        <VCardText class="text-body-2 text-grey-darken-2">
          This table displays all registered departments within the organization, including their names, assigned codes, and associated units. Use the action buttons to view, edit, or deactivate individual departments.
        </VCardText>

        <!-- Injected Table -->
        <VCardText class="pt-0">
          <Departments
            :key="refreshKey"
            @fmsedit="editDepartment"
            @fmsdelete="deleteDepartment"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- 🔹 Shared Modal for Add & Edit -->
  <DepartmentModal
    v-model="showModal"
    :department="selectedDept"
    :title="selectedDept?.id ? 'Edit Department' : 'Add Department'"
    :subtitle="selectedDept?.id 
      ? 'Update details for this department' 
      : 'Fill out the form to create a new department'"
    @fmssave="saveDepartment"
  />
</template>
