<template>
  <VDialog
    v-model="isOpen"
    max-width="900"
    transition="dialog-bottom-transition"
  >
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-archive" size="28" color="primary" class="me-3" />
        Archived OBR Details
      </VCardTitle>
      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        Review the details of this archived Obligation and Budget Request.
      </VCardSubtitle>

      <VCardText>
        <VForm>
          <!-- Archive Information -->
          <VAlert type="warning" variant="tonal" prominent border="start" class="mb-6">
            <VAlertTitle class="d-flex align-center mb-2">
              <VIcon icon="bx-info-circle" size="24" class="me-2" />
              <span class="text-h6">Archive Information</span>
            </VAlertTitle>
            
            <div class="archive-info mt-3">
              <VRow>
                <VCol cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-grey-darken-1 mb-1">Archived Date</div>
                    <div class="text-body-1 font-weight-medium">
                      {{ formatDateTime(obr?.latest_archive?.archived_at) }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12" md="6">
                  <div class="info-item mb-3">
                    <div class="text-caption text-grey-darken-1 mb-1">Archived By</div>
                    <div class="text-body-1 font-weight-medium">
                      {{ obr?.latest_archive?.archived_by_user?.name || 'Unknown' }}
                    </div>
                  </div>
                </VCol>
                <VCol cols="12">
                  <div class="info-item">
                    <div class="text-caption text-grey-darken-1 mb-1">Archive Reason</div>
                    <div class="text-body-1 pa-3 bg-grey-lighten-4 rounded">
                      {{ obr?.latest_archive?.archive_reason || 'No reason provided' }}
                    </div>
                  </div>
                </VCol>
              </VRow>
            </div>
          </VAlert>

          <VDivider class="my-6" />

          <!-- OBR Details -->
          <VRow>
            <VCol cols="12" md="6">
              <VTextField
                :model-value="obr?.year"
                label="Budget Year"
                variant="outlined"
                density="comfortable"
                readonly
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                :model-value="obr?.pao_request?.office_code?.description"
                label="Office Code"
                variant="outlined"
                density="comfortable"
                readonly
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                :model-value="obr?.obr_no"
                label="OBR Number"
                variant="outlined"
                density="comfortable"
                readonly
              />
            </VCol>
            
            <VCol cols="12" md="6">
              <VTextField
                :model-value="obr?.office_address"
                label="Office Address"
                variant="outlined"
                density="comfortable"
                readonly
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                :model-value="obr?.latest_status?.internal_step?.approval_title"
                label="Status When Archived"
                variant="outlined"
                density="comfortable"
                readonly
              />
            </VCol>
          </VRow>

          <div class="mt-6">
            <h6 class="text-h6 text-grey-darken-2 mb-3">
              Expenditure Items
            </h6>
            <VDivider class="mb-4" />

            <VRow class="mb-4 text-center">
              <VCol>
                <div class="text-caption text-grey-darken-1">
                  Total Expenditure
                </div>
                <div class="font-weight-medium text-h6">
                  {{ formatCurrency(totalExpenditureAmount) }}
                </div>
              </VCol>
            </VRow>
            
            <VDivider class="my-4" />
            
            <div class="rounded-lg border border-grey-lighten-3 overflow-x-auto">
              <VTable fixed-header class="scrollable-table text-caption">
                <thead>
                  <tr>
                    <th class="text-left px-4 py-2" style="width: 60%;">
                      Expenditure Object
                    </th>
                    <th class="text-right px-4 py-2" style="width: 40%;">
                      Amount
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!obr?.obr_objects || obr.obr_objects.length === 0">
                    <td colspan="2" class="text-center py-6 text-grey-darken-2">
                      No expenditure items.
                    </td>
                  </tr>
                  <tr v-else v-for="item in obr.obr_objects" :key="item.id">
                    <td class="px-4 py-3">
                      {{ getExpenditureName(item.object_expenditure_id) }}
                    </td>
                    <td class="text-right px-4 py-3 font-weight-medium">
                      {{ formatCurrency(item.amount) }}
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </div>
          </div>
        </VForm>
      </VCardText>

      <VCardActions class="justify-end mt-6">
        <VBtn
          color="grey-darken-1"
          class="px-4"
          @click="close"
        >
          <VIcon icon="bx-x" class="me-1" /> Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import api from "@fms/utils/api";

const props = defineProps({
  modelValue: Boolean,
  obr: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(props.modelValue)
const objectExpenditures = ref([])

const totalExpenditureAmount = computed(() => {
  if (!props.obr?.obr_objects || props.obr.obr_objects.length === 0) return 0;
  return props.obr.obr_objects.reduce((total, item) => total + (parseFloat(item.amount) || 0), 0);
});

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '₱0.00';
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

const getExpenditureName = (id) => {
  const item = objectExpenditures.value.find(exp => exp.id === id);
  return item?.object_expenditure || 'Unknown';
}

const fetchObjectExpenditures = async () => {
  try {
    const response = await api.get('/object-expenditures');
    objectExpenditures.value = Array.isArray(response.data.data) ? response.data.data : response.data;
  } catch (error) {
    console.error('Failed to fetch object expenditures:', error);
  }
}

watch(() => props.modelValue, async (val) => {
  isOpen.value = val
  if (val) {
    if (objectExpenditures.value.length === 0) {
      await fetchObjectExpenditures()
    }
  }
})

watch(isOpen, (val) => {
  if (val !== props.modelValue) {
    emit('update:modelValue', val)
  }
})

function close() {
  isOpen.value = false
}
</script>

<style scoped>
.custom-modal-shadow {
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}

.description-text {
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: unset !important;
  line-height: 1.6;
}

.scrollable-table >>> .v-table__wrapper {
  max-height: 300px;
  overflow-y: auto;
}

.scrollable-table >>> thead th {
  position: sticky;
  top: 0;
  z-index: 1;
  background-color: #f5f5f5 !important;
  color: #424242 !important;
  font-weight: 600;
}

.archive-info .info-item {
  padding: 8px;
  border-radius: 4px;
}
</style>