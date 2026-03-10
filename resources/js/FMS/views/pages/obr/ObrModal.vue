<template>
  <VDialog
    v-model="isOpen"
    max-width="800"
    transition="dialog-bottom-transition"
    :persistent="!isViewOnlyMode"
  >
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <img
          :src="fountainPen"
          alt="Fountain Pen Icon"
          class="me-3"
          style="width: 28px; height: 28px;"
        />
        {{ modalTitle }}
      </VCardTitle>
      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        {{ modalSubtitle }}
      </VCardSubtitle>

      <VCardText>
        <VForm>
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="selectedYear"
                label="Budget Year"
                :items="annualBudgets"
                item-title="year"
                item-value="year"
                variant="outlined"
                density="comfortable"
                :loading="isFetchingYears"
                :disabled="isSaving || isFetchingYears || isEditMode || isViewOnlyMode"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="localObr.request_id"
                label="Office Code"
                :items="paoRequests"
                item-title="description"
                item-value="id"
                variant="outlined"
                density="comfortable"
                :loading="isFetchingPaoRequests"
                :disabled="isSaving || !selectedYear || isFetchingPaoRequests || isEditMode || isViewOnlyMode"
                no-data-text="Please select a year first"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="localObr.paper_trail_set_id"
                label="Paper Trail Set"
                :items="paperTrailSets"
                :item-title="setItemTitle"
                item-value="id"
                variant="outlined"
                density="comfortable"
                :loading="isFetchingSets"
                :disabled="isSaving || !localObr.request_id || isFetchingSets || isEditMode || isViewOnlyMode"
                no-data-text="Select an Office Code first"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="localObr.obr_no"
                label="OBR Number"
                variant="outlined"
                density="comfortable"
                :readonly="true"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="localObr.office_address"
                label="Office Address"
                variant="outlined"
                density="comfortable"
                :disabled="isSaving || isViewOnlyMode"
                :rules="requiredRule"
              />
            </VCol>
          </VRow>

          <div class="mt-6">
            <h6 class="text-h6 text-grey-darken-2 mb-3">
              Expenditure Items
            </h6>
            <VDivider class="mb-4" />

            <div v-if="localObr.request_id">
              <VRow class="mb-2">
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="searchQuery"
                    label="Search Expenditure Items"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="bx-search"
                    clearable
                    class="text-caption"
                    hide-details
                  />
                </VCol>
              </VRow>

              <VRow class="mb-4 text-center">
                <VCol>
                  <div class="text-caption text-grey-darken-1">
                    Total Available Budget
                  </div>
                  <div class="font-weight-medium text-h6">
                    {{ formatCurrency(totalAllowedBudget) }}
                  </div>
                </VCol>
                <VCol>
                  <div class="text-caption text-grey-darken-1">
                    This OBR's Expenditure
                  </div>
                  <div
                    class="font-weight-medium text-h6"
                    :class="{'text-error': totalRemainingBudget < 0}"
                  >
                    {{ formatCurrency(totalExpenditureAmount) }}
                  </div>
                </VCol>
                <VCol>
                  <div class="text-caption text-grey-darken-1">
                    Remaining Budget
                  </div>
                  <div
                    class="font-weight-medium text-h6"
                    :class="{'text-error': totalRemainingBudget < 0, 'text-success': totalRemainingBudget >= 0}"
                  >
                    {{ formatCurrency(totalRemainingBudget) }}
                  </div>
                </VCol>
              </VRow>
              
              <VDivider class="my-4" />
              
              <div
                ref="tableContainerRef"
                class="rounded-lg border border-grey-lighten-3 overflow-x-auto"
                elevation="2"
              >
                <VTable
                  fixed-header
                  class="scrollable-table text-caption"
                >
                  <thead>
                    <tr>
                      <th
                        class="text-left px-4 py-2"
                        style="width: 50%;"
                      >
                        Expenditure Object
                      </th>
                      <th
                        class="text-left px-4 py-2"
                        style="width: 35%;"
                      >
                        Amount
                      </th>
                      <th
                        v-if="!isViewOnlyMode"
                        class="text-right px-4 py-2"
                        style="width: 15%;"
                      >
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="filteredExpenditureItems.length === 0">
                      <td
                        :colspan="isViewOnlyMode ? 2 : 3"
                        class="text-center py-6 text-grey-darken-2"
                      >
                        {{ localObr.obr_objects.length > 0 ? 'No items match your search.' : 'No expenditure items added.' }}
                      </td>
                    </tr>
                    <tr
                      v-else
                      v-for="item in filteredExpenditureItems"
                      :key="item.id"
                    >
                      <td class="px-4 py-2">
                        <VSelect
                          v-model="item.object_expenditure_id"
                          :items="getFilteredPaoObjects(item)"
                          item-title="object_expenditure"
                          item-value="id"
                          variant="outlined"
                          density="compact"
                          hide-details
                          :disabled="isSaving || isViewOnlyMode"
                        />
                      </td>
                      <td class="px-4 py-2">
                        <VTextField
                          v-model="item.amount"
                          type="number"
                          variant="outlined"
                          density="compact"
                          prefix="₱"
                          :disabled="isSaving || !item.object_expenditure_id || isViewOnlyMode"
                          :hint="item.object_expenditure_id && !isViewOnlyMode ? `Available: ${formatCurrency(getRemainingBudgetForItem(item.object_expenditure_id))}` : ''"
                          persistent-hint
                          :error-messages="!isViewOnlyMode ? getAmountError(item) : ''"
                        />
                      </td>
                      <td v-if="!isViewOnlyMode" class="text-right px-4 py-2 whitespace-nowrap">
                        <VBtn
                          icon
                          variant="text"
                          color="error"
                          size="small"
                          :disabled="isSaving"
                          @click="removeExpenditureItem(item.id)"
                        >
                          <VIcon icon="bx-trash" />
                        </VBtn>
                      </td>
                    </tr>
                  </tbody>
                </VTable>
              </div>
              
              <div v-if="!isViewOnlyMode" class="mt-4">
                <VBtn
                  color="primary"
                  variant="tonal"
                  size="small"
                  @click="addExpenditureItem"
                  :disabled="isSaving || !localObr.request_id || !canAddItem"
                >
                  <VIcon
                    icon="bx-plus"
                    class="me-1"
                  /> Add Item
                </VBtn>
              </div>

            </div>
            <div
              v-else
              class="text-center py-4 text-grey-darken-1"
            >
              Please select an Office Code to add items.
            </div>
          </div>
        </VForm>
      </VCardText>

      <VCardActions class="justify-end mt-6">
        <VBtn
          v-if="isViewOnlyMode"
          color="primary"
          class="px-5"
          elevation="2"
          @click="close"
        >
          <VIcon icon="bx-x" class="me-1" /> Close
        </VBtn>
        <template v-else>
          <VBtn
            color="grey-darken-1"
            class="me-2 px-4"
            @click="close"
            :disabled="isSaving"
          >
            <VIcon
              icon="bx-x"
              class="me-1"
            /> Cancel
          </VBtn>
          <VBtn
            color="primary"
            class="px-5"
            elevation="2"
            :loading="isSaving"
            :disabled="isSaving || isFormInvalid"
            @click="save"
          >
            <VIcon
              :icon="isEditMode ? 'bx-save' : 'bx-plus'"
              class="me-1"
            />
            {{ isEditMode ? 'Save Changes' : 'Create OBR' }}
          </VBtn>
        </template>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import fountainPen from '@/../icons/fountain_pen_3d.png'
import { computed, ref, watch, onMounted, nextTick } from 'vue'
import api from "@fms/utils/api";

const props = defineProps({
  modelValue: Boolean,
  obr: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'save'])

const isOpen = ref(props.modelValue)
const isSaving = ref(false)
const isFetchingYears = ref(false)
const isFetchingPaoRequests = ref(false)
const isInitialSetup = ref(false);
const annualBudgets = ref([])
const paoRequests = ref([])
const paoObjects = ref([])
const existingObrs = ref([]);
const selectedYear = ref(null)
const tableContainerRef = ref(null);
const searchQuery = ref('');

// NEW (added): fetch state + data store for Sets
const isFetchingSets = ref(false);
const paperTrailSets = ref([]);

// ADDED: include paper_trail_set_id in localObr (others unchanged)
const localObr = ref({
    request_id: null,
    obr_no: '',
    office_address: '',
    obr_objects: [],
    paper_trail_set_id: null,
})

// NEW VALIDATION RULE (unchanged from yours)
const requiredRule = [
  value => (!!value && value.trim().length > 0) || 'This field is required.',
];

const isEditMode = computed(() => !!props.obr?.id)

// Check if OBR is in draft status
const isDraft = computed(() => {
  if (!props.obr?.latest_status?.internal_step?.approval_title) return true;
  return props.obr.latest_status.internal_step.approval_title.toLowerCase() === 'draft';
});

// View-only mode: when editing and not in draft
const isViewOnlyMode = computed(() => isEditMode.value && !isDraft.value);

// Modal title based on mode
const modalTitle = computed(() => {
  if (isViewOnlyMode.value) return 'View OBR';
  if (isEditMode.value) return 'Edit OBR';
  return 'Add New OBR';
});

// Modal subtitle based on mode
const modalSubtitle = computed(() => {
  if (isViewOnlyMode.value) return 'This OBR is currently in process and cannot be edited. You can view all the details below.';
  if (isEditMode.value) return 'Update details for this Obligation and Budget Request. Please review all details carefully before saving your changes.';
  return 'Fill out the form to create a new OBR. Select a budget year and office to begin adding expenditure items.';
});

const canAddItem = computed(() => {
    const addedItemIds = new Set(localObr.value.obr_objects.map(item => item.object_expenditure_id));
    return paoObjects.value.some(paoObj => 
        !addedItemIds.has(paoObj.id) && getRemainingBudgetForItem(paoObj.id) > 0
    );
});

const filteredExpenditureItems = computed(() => {
  if (!searchQuery.value) {
    return localObr.value.obr_objects;
  }
  const lowerCaseQuery = searchQuery.value.toLowerCase();
  return localObr.value.obr_objects.filter(item => {
    const paoObject = paoObjects.value.find(p => p.id === item.object_expenditure_id);
    return paoObject && paoObject.object_expenditure.toLowerCase().includes(lowerCaseQuery);
  });
});

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '₱0.00';
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
}

function getRemainingBudgetForItem(objectExpenditureId) {
    if (!objectExpenditureId || !localObr.value.request_id) return 0;
    
    const currentOfficeId = Number(localObr.value.request_id);
    const currentItemId = Number(objectExpenditureId);

    const paoObject = paoObjects.value.find(p => Number(p.id) === currentItemId);
    const totalAllocated = paoObject ? parseFloat(paoObject.amount) : 0;

    const totalObligated = existingObrs.value
        .filter(obr => Number(obr.request_id) === currentOfficeId)
        .filter(obr => !(isEditMode.value && Number(obr.id) === Number(props.obr.id)))
        .flatMap(obr => obr.obr_objects)
        .filter(obj => Number(obj.object_expenditure_id) === currentItemId)
        .reduce((sum, obj) => sum + parseFloat(obj.amount), 0);

    return totalAllocated - totalObligated;
}

function getAmountError(item) {
  const remainingBudget = getRemainingBudgetForItem(item.object_expenditure_id);
  const enteredAmount = parseFloat(item.amount);

  if (remainingBudget !== null && !isNaN(enteredAmount) && enteredAmount > remainingBudget) {
    return `Exceeds available budget of ${formatCurrency(remainingBudget)}`;
  }
  
  return '';
}

const totalAllowedBudget = computed(() => {
    if (!localObr.value.request_id || paoObjects.value.length === 0) {
        return 0;
    }
    return paoObjects.value.reduce((sum, paoItem) => {
        const remaining = getRemainingBudgetForItem(paoItem.id);
        return sum + remaining;
    }, 0);
});

const totalExpenditureAmount = computed(() => {
  if (!localObr.value.obr_objects || localObr.value.obr_objects.length === 0) return 0;
  return localObr.value.obr_objects.reduce((total, item) => total + (parseFloat(item.amount) || 0), 0);
});

const totalRemainingBudget = computed(() => {
  return totalAllowedBudget.value - totalExpenditureAmount.value;
});

// MODIFIED isFormInvalid: added only one new check for set selection
const isFormInvalid = computed(() => {
  // Check for required Office Address
  if (!localObr.value.office_address || !localObr.value.office_address.trim()) {
    return true;
  }
  
  // NEW: require paper trail set selection (only for 'Add' mode, 'Edit' mode has it disabled)
  if (!isEditMode.value && !localObr.value.paper_trail_set_id) {
    return true;
  }

  // Check if there are no items
  if (localObr.value.obr_objects.length === 0) {
    return true;
  }

  // Check if total would be negative
  if (totalRemainingBudget.value < 0) {
      return true;
  }
  
  // Check each item for validity
  return localObr.value.obr_objects.some(item => {
    const remainingBudget = getRemainingBudgetForItem(item.object_expenditure_id);
    const enteredAmount = parseFloat(item.amount);

    return !item.object_expenditure_id || 
           !item.amount || 
           enteredAmount <= 0 || 
           (remainingBudget !== null && enteredAmount > remainingBudget);
  });
});

function generateProvisionalObrNo() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const randomPart = Math.random().toString(36).substring(2, 6).toUpperCase();
  return `${year}-${month}${day}-${randomPart}`;
}

function getFilteredPaoObjects(currentItem) {
  const selectedIds = localObr.value.obr_objects
    .filter(item => item.id !== currentItem.id)
    .map(item => item.object_expenditure_id)
    .filter(id => id !== null);

  return paoObjects.value.filter(option => {
    const isCurrentlySelected = option.id === currentItem.object_expenditure_id;
    const isSelectedElsewhere = selectedIds.includes(option.id);

    if (isCurrentlySelected) {
        return true;
    }
    if (isSelectedElsewhere) {
        return false;
    }
    const remainingBudget = getRemainingBudgetForItem(option.id);
    return remainingBudget > 0;
  });
}

// NEW: fetch Sets (kept simple; no office filter dependency to avoid breaking)
async function fetchPaperTrailSets() {
  isFetchingSets.value = true;
  try {
    const { data } = await api.get('/paper-trail-sets?paginate=false');
    paperTrailSets.value = Array.isArray(data?.data) ? data.data : data;
  } catch (e) {
    console.error('Failed to fetch paper trail sets', e);
    paperTrailSets.value = [];
  } finally {
    isFetchingSets.value = false;
  }
}

// Display label for set options
const setItemTitle = (item) => `Set #${item.set_no} — ${item.office_code ?? 'N/A'}`;

const fetchAnnualBudgets = async () => {
    isFetchingYears.value = true
    try {
        const response = await api.get('/annual-budgets');
        annualBudgets.value = response.data;
    } catch (error) {
        console.error("Failed to fetch annual budgets:", error);
    } finally {
        isFetchingYears.value = false
    }
}

const fetchPaoRequestsByYear = async (year) => {
    if (!year) {
        paoRequests.value = []
        return
    }
    isFetchingPaoRequests.value = true
    try {
        const response = await api.get(`/pao-requests/year/${year}`);
        paoRequests.value = response.data;
    } catch (error) {
        console.error(`Failed to fetch PAO requests for year ${year}:`, error);
        paoRequests.value = []
    } finally {
        isFetchingPaoRequests.value = false
    }
}

const fetchExistingObrsByYear = async (year) => {
    if (!year) {
        existingObrs.value = [];
        return;
    }
    try {
        const response = await api.get(`/obr-requests?year=${year}&paginate=false`);
        const responseData = response.data;
        if (responseData && Array.isArray(responseData.data)) {
          existingObrs.value = responseData.data;
        } else if (Array.isArray(responseData)) {
          existingObrs.value = responseData;
        } else {
          existingObrs.value = [];
        }
    } catch (error) {
        console.error(`Failed to fetch existing OBRs for year ${year}:`, error);
        existingObrs.value = [];
    }
};

watch(selectedYear, async (newYear) => {
    if (isInitialSetup.value) return;
    localObr.value.request_id = null
    localObr.value.paper_trail_set_id = null // NEW: reset selected set on year change
    paoRequests.value = []
    paoObjects.value = []
    localObr.value.obr_objects = []
    existingObrs.value = [];
    paperTrailSets.value = []; // NEW: reset sets list
    if (newYear) {
        await Promise.all([
          fetchPaoRequestsByYear(newYear),
          fetchExistingObrsByYear(newYear),
          fetchPaperTrailSets(), // NEW: load sets list for selection
        ]);
    }
});

watch(() => localObr.value.request_id, async (newRequestId) => {
    if (isInitialSetup.value) return;
    localObr.value.obr_objects = [];
    localObr.value.paper_trail_set_id = null; // NEW: reset set when office changes
    paoObjects.value = [];
    if (newRequestId) {
        const selectedRequest = paoRequests.value.find(req => req.id === newRequestId);
        if (selectedRequest && selectedRequest.objects) {
            paoObjects.value = selectedRequest.objects;
        }
        // NEW: (safe) refresh sets after office selection (no filter to avoid breaking)
        await fetchPaperTrailSets();
    }
});

watch(() => props.modelValue, async (val) => {
    isOpen.value = val
    if (val) {
        searchQuery.value = '';
        isSaving.value = false
        selectedYear.value = null;
        localObr.value = { 
          request_id: null, 
          obr_no: generateProvisionalObrNo(), 
          office_address: '', 
          obr_objects: [],
          paper_trail_set_id: null,
        };
        paoRequests.value = [];
        paoObjects.value = [];
        existingObrs.value = [];
        paperTrailSets.value = [];

        if (isEditMode.value && props.obr) {
            isInitialSetup.value = true;
            selectedYear.value = props.obr.year || null;
            if (selectedYear.value) {
              await Promise.all([
                fetchPaoRequestsByYear(selectedYear.value),
                fetchExistingObrsByYear(selectedYear.value),
                fetchPaperTrailSets(), // NEW: ensure sets are loaded in edit mode
              ]);
            }
            
            const selectedRequest = paoRequests.value.find(req => req.id === props.obr.request_id);
            if (selectedRequest) {
                paoObjects.value = selectedRequest.objects.map(obj => ({ ...obj, id: Number(obj.id) }));
            }
            await nextTick();
            const obrCopy = JSON.parse(JSON.stringify(props.obr));
            if (obrCopy.obr_objects) {
                obrCopy.obr_objects = obrCopy.obr_objects.map((item, index) => ({
                    ...item,
                    id: item.id || `temp-${index}`,
                    object_expenditure_id: Number(item.object_expenditure_id)
                }));
            }
            
            // Pull the set_id from the latest_status relationship
            if (props.obr.latest_status) {
              obrCopy.paper_trail_set_id = props.obr.latest_status.set_id ?? null;
            } else {
              obrCopy.paper_trail_set_id = null;
            }
            // ✅ ======================================================== ✅

            localObr.value = obrCopy;
            setTimeout(() => { isInitialSetup.value = false; }, 0);
        } else {
          // Ensure budgets are fetched for 'Add' mode
          await fetchAnnualBudgets();
        }
    }
})

watch(isOpen, (val) => {
  emit('update:modelValue', val)
})

let tempItemId = 0;
async function addExpenditureItem() {
    localObr.value.obr_objects.push({ id: `new-${tempItemId++}`, object_expenditure_id: null, amount: null });

    await nextTick();

    if (tableContainerRef.value) {
        const scrollableWrapper = tableContainerRef.value.querySelector('.v-table__wrapper');
        if (scrollableWrapper) {
            scrollableWrapper.scrollTop = scrollableWrapper.scrollHeight;
        }
    }
}

function removeExpenditureItem(itemId) {
    const index = localObr.value.obr_objects.findIndex(item => item.id === itemId);
    if (index > -1) {
        localObr.value.obr_objects.splice(index, 1);
    }
}

function close() {
  if (!isSaving.value) isOpen.value = false
}

function save() {
  isSaving.value = true
  
  const payload = JSON.parse(JSON.stringify(localObr.value));
  payload.year = selectedYear.value;

  if (payload.obr_objects) {
      payload.obr_objects.forEach(item => {
        if(String(item.id).startsWith('new-')) {
          delete item.id;
        }
      });
  }

  // Do not send paper_trail_set_id on 'edit'
  if (isEditMode.value) {
    delete payload.paper_trail_set_id;
  }

  emit('save', payload, (success = true) => {
      isSaving.value = false
      if (success) isOpen.value = false
  })
}

onMounted(() => {
    // Moved to watch(isOpen) to fetch only when modal opens
    // fetchAnnualBudgets(); 
})
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
}

.v-table > .v-table__wrapper > table > tbody > tr > td {
  vertical-align: middle;
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
</style>