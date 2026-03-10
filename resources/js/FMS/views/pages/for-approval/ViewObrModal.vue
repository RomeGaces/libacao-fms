<template>
  <VDialog
    v-model="isOpen"
    max-width="900"
    transition="dialog-bottom-transition"
  >
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <img
          :src="fountainPen"
          alt="Fountain Pen Icon"
          class="me-3"
          style="width: 28px; height: 28px;"
        />
        View OBR Details
      </VCardTitle>
      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        Review the details of this Obligation and Budget Request.
      </VCardSubtitle>

      <VCardText>
        <VForm>
          <!-- Rejection History Section (Show at top if exists) -->
          <div v-if="obr?.rejections && obr.rejections.length > 0" class="mb-6">
            <VAlert type="error" variant="tonal" prominent border="start" class="mb-4 rejection-alert">
              <div class="d-flex align-center cursor-pointer" @click="toggleRejectionHistory">
                <div class="flex-grow-1">
                  <VAlertTitle class="d-flex align-center mb-2">
                    <VIcon icon="bx-error-circle" size="24" class="me-2" />
                    <span class="text-h6">Rejection History</span>
                  </VAlertTitle>
                  <div class="text-body-2">
                    This OBR has been rejected {{ obr.rejections.length }} time{{ obr.rejections.length > 1 ? 's' : '' }}.
                    <span class="text-caption ms-2">{{ isRejectionHistoryExpanded ? 'Click to collapse' : 'Click to expand' }}</span>
                  </div>
                </div>
                <VIcon 
                  :icon="isRejectionHistoryExpanded ? 'bx-chevron-down' : 'bx-chevron-right'" 
                  size="28" 
                  class="toggle-icon"
                />
              </div>
            </VAlert>

            <!-- Collapsible Rejection Timeline -->
            <VExpandTransition>
              <div v-show="isRejectionHistoryExpanded" class="rejection-timeline">
                <div 
                  v-for="(rejection, index) in sortedRejections" 
                  :key="rejection.id"
                  class="rejection-card mb-4"
                >
                  <VCard variant="outlined" class="rejection-item-card">
                    <VCardText class="pa-4">
                      <!-- Header -->
                      <div class="d-flex align-center justify-space-between mb-3">
                        <VChip color="error" size="small" variant="flat" class="font-weight-bold">
                          Rejection #{{ obr.rejections.length - index }}
                        </VChip>
                        <div class="text-caption text-grey-darken-2 d-flex align-center">
                          <VIcon icon="bx-time" size="small" class="me-1" />
                          {{ formatDateTime(rejection.created_at) }}
                        </div>
                      </div>

                      <!-- Rejected By -->
                      <div class="mb-3 d-flex align-center">
                        <VAvatar size="32" color="error" class="me-2">
                          <VIcon icon="bx-user" size="18" />
                        </VAvatar>
                        <div>
                          <div class="text-caption text-grey-darken-1">Rejected by</div>
                          <div class="text-body-2 font-weight-medium">
                            {{ rejection.rejected_by_user?.name || 'Unknown User' }}
                          </div>
                        </div>
                      </div>

                      <!-- Rejection Reason -->
                      <VDivider class="my-3" />
                      <div class="d-flex align-center mb-2">
                        <VIcon icon="bx-message-square-detail" size="18" color="error" class="me-2" />
                        <span class="text-caption text-grey-darken-1 font-weight-medium">Reason for Rejection:</span>
                      </div>
                      <div class="text-body-2 text-grey-darken-3">
                        {{ rejection.rejection_details }}
                      </div>
                    </VCardText>
                  </VCard>
                </div>
              </div>
            </VExpandTransition>
          </div>

          <!-- Archive History Section -->
          <div v-if="obr?.archives && obr.archives.length > 0" class="mb-6">
            <VAlert type="warning" variant="tonal" prominent border="start" class="mb-4 archive-alert">
              <div class="d-flex align-center cursor-pointer" @click="toggleArchiveHistory">
                <div class="flex-grow-1">
                  <VAlertTitle class="d-flex align-center mb-2">
                    <VIcon icon="bx-archive" size="24" class="me-2" />
                    <span class="text-h6">Archive History</span>
                  </VAlertTitle>
                  <div class="text-body-2">
                    This OBR has been archived.
                    <span class="text-caption ms-2">{{ isArchiveHistoryExpanded ? 'Click to collapse' : 'Click to expand' }}</span>
                  </div>
                </div>
                <VIcon 
                  :icon="isArchiveHistoryExpanded ? 'bx-chevron-down' : 'bx-chevron-right'" 
                  size="28" 
                  class="toggle-icon"
                />
              </div>
            </VAlert>

            <!-- Collapsible Archive Timeline -->
            <VExpandTransition>
              <div v-show="isArchiveHistoryExpanded" class="archive-timeline">
                <div 
                  v-for="(archive, index) in sortedArchives" 
                  :key="archive.id"
                  class="archive-card mb-4"
                >
                  <VCard variant="outlined" class="archive-item-card">
                    <VCardText class="pa-4">
                      <!-- Header -->
                      <div class="d-flex align-center justify-space-between mb-3">
                        <VChip color="warning" size="small" variant="flat" class="font-weight-bold">
                          Archive #{{ obr.archives.length - index }}
                        </VChip>
                        <div class="text-caption text-grey-darken-2 d-flex align-center">
                          <VIcon icon="bx-time" size="small" class="me-1" />
                          {{ formatDateTime(archive.archived_at) }}
                        </div>
                      </div>

                      <!-- Archived By -->
                      <div class="mb-3 d-flex align-center">
                        <VAvatar size="32" color="warning" class="me-2">
                          <VIcon icon="bx-user" size="18" />
                        </VAvatar>
                        <div>
                          <div class="text-caption text-grey-darken-1">Archived by</div>
                          <div class="text-body-2 font-weight-medium">
                            {{ archive.archived_by_user?.name || 'Unknown User' }}
                          </div>
                        </div>
                      </div>

                      <!-- Archive Reason -->
                      <VDivider class="my-3" />
                      <div class="d-flex align-center mb-2">
                        <VIcon icon="bx-message-square-detail" size="18" color="warning" class="me-2" />
                        <span class="text-caption text-grey-darken-1 font-weight-medium">Reason for Archiving:</span>
                      </div>
                      <div class="text-body-2 text-grey-darken-3">
                        {{ archive.archive_reason }}
                      </div>
                    </VCardText>
                  </VCard>
                </div>
              </div>
            </VExpandTransition>
          </div>

          <!-- Divider if rejections or archives exist -->
          <VDivider v-if="(obr?.rejections && obr.rejections.length > 0) || (obr?.archives && obr.archives.length > 0)" class="my-6" />

          <!-- Original OBR Details -->
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
                :model-value="paperTrailSet ? `Set #${paperTrailSet.set_no}` : 'N/A'"
                label="Paper Trail Set"
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
          color="primary"
          class="me-2 px-4"
          @click="printOBR"
        >
          <VIcon icon="bx-printer" class="me-1" /> Print
        </VBtn>
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

  <!-- Hidden Print Template -->
  <div id="print-obr" style="display: none;">
    <div class="print-container">
      <!-- Header -->
      <div class="print-header">
        <div class="header-logos">
          <img src="/images/logo-clean.png" alt="Municipality Logo" class="header-logo" />
          <img src="/images/bagong-pilipinas-logo.png" alt="Bagong Pilipinas Logo" class="header-logo" />
        </div>
        <div class="header-text">
          <h3>Republic of the Philippines</h3>
          <h4>Province of Aklan</h4>
          <h4>Municipality of Libacao</h4>
          <div class="header-stars">★ ★ ★</div>
        </div>
        <div class="header-spacer"></div>
      </div>

      <!-- Title and Number -->
      <div class="print-title-row">
        <div class="print-title">OBLIGATION REQUEST</div>
        <div class="print-number">
          <span>No:</span>
          <span class="value">{{ obr?.obr_no || '' }}</span>
        </div>
      </div>

      <!-- Payee Row -->
      <div class="print-row">
        <div class="print-cell label-cell">Payee</div>
        <div class="print-cell value-cell"></div>
        <div class="print-cell small-label">Date</div>
        <div class="print-cell value-cell small">{{ currentDate }}</div>
      </div>

      <!-- Office Address Row -->
      <div class="print-row">
        <div class="print-cell label-cell">Office<br/>Address</div>
        <div class="print-cell value-cell office-address" colspan="5">
          {{ obr?.office_address || '' }}
        </div>
      </div>

      <!-- Table Header -->
      <div class="print-table-header">
        <div class="col-rc">Responsibility<br/>Center</div>
        <div class="col-particulars">Particulars</div>
        <div class="col-account">Account<br/>Code</div>
        <div class="col-amount">Amount</div>
      </div>

      <!-- Table Body -->
      <div class="print-table-body">
        <div v-for="(item, index) in obr?.obr_objects" :key="index" class="print-table-row">
          <div class="col-rc">
            <span v-if="index === 0">{{ obr?.pao_request?.office_code?.description || '' }}</span>
          </div>
          <div class="col-particulars">{{ getExpenditureName(item.object_expenditure_id) }}</div>
          <div class="col-account"></div>
          <div class="col-amount">{{ formatCurrencyForPrint(item.amount) }}</div>
        </div>
        <!-- Empty rows to fill the page -->
        <div v-for="n in Math.max(0, 15 - (obr?.obr_objects?.length || 0))" :key="`empty-${n}`" class="print-table-row empty-row">
          <div class="col-rc"></div>
          <div class="col-particulars"></div>
          <div class="col-account"></div>
          <div class="col-amount"></div>
        </div>
      </div>

      <!-- Total Row -->
      <div class="print-total-row">
        <div class="total-label">TOTAL</div>
        <div class="total-amount">{{ formatCurrencyForPrint(totalExpenditureAmount) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import fountainPen from '@/../icons/fountain_pen_3d.png'
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
const paperTrailSet = ref(null)
const isRejectionHistoryExpanded = ref(false)
const isArchiveHistoryExpanded = ref(false)

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const totalExpenditureAmount = computed(() => {
  if (!props.obr?.obr_objects || props.obr.obr_objects.length === 0) return 0;
  return props.obr.obr_objects.reduce((total, item) => total + (parseFloat(item.amount) || 0), 0);
});

// Sort rejections by created_at descending (newest first)
const sortedRejections = computed(() => {
  if (!props.obr?.rejections || props.obr.rejections.length === 0) return [];
  return [...props.obr.rejections].sort((a, b) => 
    new Date(b.created_at) - new Date(a.created_at)
  );
});

// Sort archives by archived_at descending (newest first)
const sortedArchives = computed(() => {
  if (!props.obr?.archives || props.obr.archives.length === 0) return [];
  return [...props.obr.archives].sort((a, b) => 
    new Date(b.archived_at) - new Date(a.archived_at)
  );
});

const toggleRejectionHistory = () => {
  isRejectionHistoryExpanded.value = !isRejectionHistoryExpanded.value;
};

const toggleArchiveHistory = () => {
  isArchiveHistoryExpanded.value = !isArchiveHistoryExpanded.value;
};

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '₱0.00';
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
}

const formatCurrencyForPrint = (value) => {
  if (value === null || value === undefined) return '';
  const formatted = new Intl.NumberFormat('en-PH', { 
    minimumFractionDigits: 2,
    maximumFractionDigits: 2 
  }).format(value);
  return `₱ ${formatted}`;
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
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

const fetchPaperTrailSet = async (setId) => {
  if (!setId) return;
  try {
    const response = await api.get(`/paper-trail-sets/${setId}`);
    paperTrailSet.value = response.data;
  } catch (error) {
    console.error('Failed to fetch paper trail set:', error);
  }
}

const printOBR = () => {
  const printContent = document.getElementById('print-obr');
  const windowPrint = window.open('', '', 'width=800,height=600');
  
  windowPrint.document.write('<html><head><title>Print OBR</title>');
  windowPrint.document.write(`
    <style>
      @page {
        size: A4;
        margin: 0.5in;
      }
      
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      
      body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        line-height: 1.2;
      }
      
      .print-container {
        width: 100%;
        border: 2px solid #000;
        padding: 10px;
      }
      
      .print-header {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 2px solid #000;
        gap: 20px;
      }
      
      .header-logos {
        display: flex;
        gap: 15px;
        width: 140px;
      }
      
      .header-logo {
        width: 60px;
        height: 60px;
        object-fit: contain;
      }
      
      .header-text {
        flex: 1;
        text-align: center;
      }
      
      .header-text h3 {
        font-size: 14pt;
        font-weight: bold;
        margin-bottom: 2px;
      }
      
      .header-text h4 {
        font-size: 12pt;
        font-weight: normal;
        margin-bottom: 2px;
      }
      
      .header-stars {
        font-size: 10pt;
        margin-top: 2px;
      }
      
      .header-spacer {
        width: 140px;
      }
      
      .print-title-row {
        display: flex;
        border-bottom: 2px solid #000;
        border-top: 2px solid #000;
        margin-top: -2px;
      }
      
      .print-title {
        flex: 1;
        padding: 10px;
        font-size: 14pt;
        font-weight: bold;
        text-align: center;
      }
      
      .print-number {
        width: 200px;
        padding: 10px;
        border-left: 2px solid #000;
        display: flex;
        align-items: center;
        gap: 10px;
      }
      
      .print-number span:first-child {
        font-weight: bold;
      }
      
      .print-row {
        display: flex;
        border-bottom: 2px solid #000;
      }
      
      .print-cell {
        padding: 8px;
        border-right: 2px solid #000;
      }
      
      .print-cell:last-child {
        border-right: none;
      }
      
      .label-cell {
        width: 120px;
        font-weight: bold;
        background-color: #f5f5f5;
      }
      
      .value-cell {
        flex: 1;
        min-height: 30px;
      }
      
      .value-cell.small {
        width: 120px;
        flex: none;
      }
      
      .small-label {
        width: 80px;
        font-size: 9pt;
      }
      
      .office-address {
        border-right: none !important;
      }
      
      .print-table-header {
        display: flex;
        border-bottom: 2px solid #000;
        background-color: #f5f5f5;
        font-weight: bold;
      }
      
      .print-table-header > div {
        padding: 8px;
        border-right: 2px solid #000;
        text-align: center;
        font-size: 10pt;
      }
      
      .print-table-header > div:last-child {
        border-right: none;
      }
      
      .col-rc {
        width: 120px;
      }
      
      .col-particulars {
        flex: 1;
      }
      
      .col-account {
        width: 120px;
      }
      
      .col-amount {
        width: 120px;
        text-align: right;
      }
      
      .print-table-body {
        min-height: 400px;
      }
      
      .print-table-row {
        display: flex;
        border-bottom: 1px solid #000;
        min-height: 25px;
      }
      
      .print-table-row.empty-row {
        border-bottom: 1px solid #ccc;
      }
      
      .print-table-row > div {
        padding: 5px 8px;
        border-right: 2px solid #000;
        font-size: 10pt;
      }
      
      .print-table-row > div:last-child {
        border-right: none;
      }
      
      .print-total-row {
        display: flex;
        border-bottom: 2px solid #000;
        border-top: 2px solid #000;
        font-weight: bold;
        font-size: 11pt;
      }
      
      .total-label {
        flex: 1;
        padding: 10px;
        text-align: right;
      }
      
      .total-amount {
        width: 120px;
        padding: 10px;
        border-left: 2px solid #000;
        text-align: right;
      }
      
      @media print {
        body {
          margin: 0;
        }
        .print-container {
          border: 2px solid #000;
        }
      }
    </style>
  `);
  windowPrint.document.write('</head><body>');
  windowPrint.document.write(printContent.innerHTML);
  windowPrint.document.write('</body></html>');
  windowPrint.document.close();
  
  setTimeout(() => {
    windowPrint.focus();
    windowPrint.print();
    windowPrint.close();
  }, 250);
}

watch(() => props.modelValue, async (val) => {
  isOpen.value = val
  if (val) {
    isRejectionHistoryExpanded.value = false;
    isArchiveHistoryExpanded.value = false;
    
    if (objectExpenditures.value.length === 0) {
      await fetchObjectExpenditures()
    }
    if (props.obr?.latest_status?.set_id) {
      await fetchPaperTrailSet(props.obr.latest_status.set_id)
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

/* Rejection Alert Styles */
.rejection-alert {
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.rejection-alert:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(244, 67, 54, 0.2) !important;
}

/* Archive Alert Styles */
.archive-alert {
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.archive-alert:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 152, 0, 0.2) !important;
}

.toggle-icon {
  transition: transform 0.3s ease;
  flex-shrink: 0;
}

/* Rejection Timeline Styles */
.rejection-timeline {
  max-height: 500px;
  overflow-y: auto;
  padding: 8px;
}

.rejection-item-card {
  border-left: 4px solid #f44336 !important;
  transition: all 0.2s ease;
}

.rejection-item-card:hover {
  box-shadow: 0 4px 12px rgba(244, 67, 54, 0.2) !important;
}

/* Archive Timeline Styles */
.archive-timeline {
  max-height: 500px;
  overflow-y: auto;
  padding: 8px;
}

.archive-item-card {
  border-left: 4px solid #ff9800 !important;
  transition: all 0.2s ease;
}

.archive-item-card:hover {
  box-shadow: 0 4px 12px rgba(255, 152, 0, 0.2) !important;
}
</style>