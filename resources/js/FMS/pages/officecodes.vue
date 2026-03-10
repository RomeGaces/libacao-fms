<script setup>
import OfficeCodes from '@fms/views/pages/office-codes/OfficeCodes.vue'
import OfficeCodesModal from '@fms/views/pages/office-codes/OfficeCodesModal.vue'
import { ref } from 'vue'
import api from "@fms/utils/api";

// Icon alias
import openBook from '@/../icons/open_book_3d.png'

const showModal = ref(false)
const selectedOfficeCode = ref(null)


// For triggering table refresh
const refreshKey = ref(0)

// Open modal for adding new office code
function addOfficeCode() {
  selectedOfficeCode.value = { office_code: '', description: '' } // empty for add
  showModal.value = true
}

// Open modal for editing existing office code
function editOfficeCode(officeCode) {
  selectedOfficeCode.value = { ...officeCode }
  showModal.value = true
}

// Handle delete (can add API call later)
function deleteOfficeCode(officeCode) {
  console.log('Delete office code:', officeCode)
}

async function saveOfficeCode(officeCode, done) {
  try {
    if (officeCode.id) {
      // Update existing office code
      await api.put(`/office-codes/${officeCode.id}`, officeCode)
    } else {
      // Create new office code
      await api.post(`/office-codes`, officeCode)
    }

    // Trigger table refresh
    refreshKey.value++

    done(true) // stop spinner + close modal
  } catch (error) {
    console.error('Failed to save office code:', error)
    done(false) // stop spinner but keep modal open
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <!-- Card Title with Add Button -->
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <img
                :src="openBook"
                alt="Office Icon"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">List of Office Codes</span>
            </div>
          </div>
        </template>

        <!-- Description -->
        <VCardText class="text-body-2 text-grey-darken-2">
          This table displays all registered office codes in the organization. Use the action buttons to edit or delete individual records.
        </VCardText>

        <!-- Injected Table -->
        <VCardText class="pt-0">
          <OfficeCodes
            :key="refreshKey"
            @fmsedit="editOfficeCode"
            @fmsdelete="deleteOfficeCode"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- Shared Modal for Add/Edit -->
  <OfficeCodesModal
    v-model="showModal"
    :department="selectedOfficeCode"
    :title="selectedOfficeCode?.id ? 'Edit Office Code' : 'Add Office Code'"
    :subtitle="selectedOfficeCode?.id
      ? 'Update details for this office code'
      : 'Fill out the form to create a new office code'"
    @fmssave="saveOfficeCode"
  />
</template>
