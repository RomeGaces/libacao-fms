<script setup>
import DeleteExpendituresModal from '@fms/views/pages/expenditures/DeleteExpendituresModal.vue'
import Expenditures from '@fms/views/pages/expenditures/Expenditures.vue'
import ExpendituresModal from '@fms/views/pages/expenditures/ExpendituresModal.vue'
import { ref } from 'vue'

// Icon for Expenditures
import pencil from '@/../icons/pencil_3d.png'

const showModal = ref(false)
const showDeleteModal = ref(false)
const selectedData = ref(null)
const modalType = ref('group') // 'group' or 'object'

// For triggering refresh in Expenditures.vue
const refreshKey = ref(0)

// Open Add/Edit modal
function openModal(type, data = null) {
  modalType.value = type
  selectedData.value = data ? { ...data } : {}
  showModal.value = true
}

// Open Delete modal
function openDeleteModal(type, data) {
  modalType.value = type
  selectedData.value = data
  showDeleteModal.value = true
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <!-- Header with Icon -->
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <img
                :src="pencil"
                alt="Expenditure Icon"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">Expenditures</span>
            </div>
          </div>
        </template>

        <!-- Description -->
        <VCardText class="text-body-2 text-grey-darken-2">
          This section provides a structured overview of your organization's expenditures,
          categorized into logical groups such as Office Supplies, Travel Expenses, or
          Equipment Purchases. Each group serves as a container for related objects of
          expenditure, allowing for granular tracking and easier auditability.
        </VCardText>

        <!-- Accordion + Table -->
        <VCardText class="pt-0">
          <Expenditures
            :key="refreshKey"
            @fmsedit="openModal"
            @fmsdelete="openDeleteModal"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- Add/Edit Modal -->
  <ExpendituresModal
    v-model="showModal"
    :data="selectedData"
    :type="modalType"
    @fmssaved="refreshKey++"
  />

  <!-- Delete Modal -->
  <DeleteExpendituresModal
    v-model="showDeleteModal"
    :data="selectedData"
    :type="modalType"
    @fmsdeleted="refreshKey++"
  />
</template>

<style scoped>
/* Match style with Departments.vue (clean + theme aware) */

/* Accordion headers */
.v-expansion-panel-title {
  padding: 14px 18px;
  font-weight: 500;
}

/* Hover polish (light + dark theme support) */
.v-expansion-panel:hover {
  background-color: rgba(0, 0, 0, 0.04); /* light hover */
}

:deep(.v-theme--dark) .v-expansion-panel:hover {
  background-color: rgba(255, 255, 255, 0.08); /* dark hover */
}

/* Keep panels flush */
.v-expansion-panel {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
:deep(.v-theme--dark) .v-expansion-panel {
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
.v-expansion-panel:last-child {
  border-bottom: none;
}
</style>
