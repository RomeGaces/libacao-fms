<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import api from "@fms/utils/api";
import fountainPen from '@/../icons/fountain_pen_3d.png';

const props = defineProps({
  modelValue: Boolean,
  budget: {
    type: Object,
    default: () => ({ office_code_id: null, budget: '' }),
  },
  annualBudget: {
    type: Object,
    default: null,
  },
  existingOfficeBudgets: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['update:modelValue', 'save']);

// --- Reactive State ---
const isOpen = ref(props.modelValue);
const isSaving = ref(false);
const localBudget = ref({ ...props.budget });
const officeCodes = ref([]);
const errorMessage = ref('');

// --- Computed Properties ---
const isEditMode = computed(() => !!props.budget?.id);

// Create a Set of office code IDs that are already used for the selected year.
// This is used to disable them in the dropdown.
const usedOfficeCodeIds = computed(() => {
    return new Set(props.existingOfficeBudgets.map(b => b.office_code_id));
});

// Calculates the remaining budget for the selected annual budget.
const remainingBudget = computed(() => {
  if (!props.annualBudget) return 0;

  // Ensure values are numbers before calculating
  const totalAnnual = Number(props.annualBudget.annual_budget) || 0;
  const currentEntryAmount = isEditMode.value ? (Number(props.budget.budget) || 0) : 0;

  const totalAllocated = props.existingOfficeBudgets.reduce((sum, item) => {
    return sum + (Number(item.budget) || 0);
  }, 0);

  // When editing, the "total allocated" includes the item being edited.
  // We subtract it so the user can modify the value up to the original limit.
  const allocatedExcludingCurrent = totalAllocated - currentEntryAmount;

  return totalAnnual - allocatedExcludingCurrent;
});

const availableBudgetText = computed(() => {
  const remaining = remainingBudget.value;
  return `Available: ${formatCurrency(remaining)}`;
});


// --- Functions ---

/**
 * Fetches office codes for the dropdown selector.
 */
async function fetchOfficeCodes() {
  try {
    const response = await api.get('/office-codes');
    if (response.data && Array.isArray(response.data.data)) {
        officeCodes.value = response.data.data;
    } else if (Array.isArray(response.data)) {
        officeCodes.value = response.data;
    } else {
        officeCodes.value = [];
    }
  } catch (error) {
    console.error('Failed to fetch office codes:', error);
    officeCodes.value = [];
  }
}

/**
 * Returns props for a VSelect item, disabling it if the office code is already used.
 * @param {object} item - The office code item from the list.
 */
function disableUsedOfficeCodes(item) {
  // Disable the item if its ID is in the set of used IDs.
  // This check is ignored in edit mode because the whole dropdown is disabled.
  return {
    disabled: !isEditMode.value && usedOfficeCodeIds.value.has(item.id)
  };
}


/**
 * Validates and saves the budget data.
 */
function save() {
  errorMessage.value = '';
  const newAmount = Number(localBudget.value.budget);

  // Validation Checks
  if (!localBudget.value.office_code_id) {
    errorMessage.value = 'Please select an office code.';
    return;
  }
  if (newAmount <= 0) {
      errorMessage.value = 'Budget amount must be greater than zero.';
      return;
  }
  if (newAmount > remainingBudget.value) {
    errorMessage.value = `Amount exceeds the available budget of ${formatCurrency(remainingBudget.value)}.`;
    return;
  }

  isSaving.value = true;
  emit('save', localBudget.value, (success = true) => {
    isSaving.value = false;
    if (success) {
      isOpen.value = false;
    }
  });
}

/**
 * Closes the modal if not in a saving state.
 */
function close() {
  if (!isSaving.value) {
    isOpen.value = false;
  }
}

/**
 * Formats a number into a currency string.
 */
function formatCurrency(value) {
  const number = Number(value) || 0;
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(number);
}

// --- Watchers & Lifecycle Hooks ---
onMounted(fetchOfficeCodes);

watch(() => props.modelValue, (val) => {
  isOpen.value = val;
  if (val) {
    localBudget.value = { ...props.budget };
    isSaving.value = false;
    errorMessage.value = '';
  }
});

watch(isOpen, (val) => {
  emit('update:modelValue', val);
});

// Clear validation error when user types
watch(() => localBudget.value.budget, () => {
    errorMessage.value = '';
});
</script>

<template>
  <VDialog v-model="isOpen" max-width="600" transition="dialog-bottom-transition">
    <VCard class="pa-5 custom-modal-shadow">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <img :src="fountainPen" alt="Icon" class="me-3" style="width: 28px;"/>
        {{ isEditMode ? 'Edit Office Budget' : 'Add Office Budget' }}
      </VCardTitle>

      <VCardSubtitle>
        {{ isEditMode ? 'Update the budget allocation for this office.' : 'Assign a budget to an office for the selected year.' }}
      </VCardSubtitle>

      <VCardText class="mt-4">
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="localBudget.office_code_id"
              :items="officeCodes"
              :item-props="disableUsedOfficeCodes"
              item-title="office_code"
              item-value="id"
              label="Office Code"
              variant="outlined"
              density="comfortable"
              :disabled="isSaving || isEditMode"
            >
              <!-- Custom template to show office code and description in dropdown -->
              <template #item="{ props, item }">
                <VListItem v-bind="props" :subtitle="item.raw.description"></VListItem>
              </template>
            </VSelect>
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="localBudget.budget"
              label="Budget Amount"
              type="number"
              prefix="₱"
              variant="outlined"
              density="comfortable"
              :disabled="isSaving"
              :hint="availableBudgetText"
              persistent-hint
              :error-messages="errorMessage ? [errorMessage] : []"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions class="justify-end mt-4">
        <VBtn color="grey-darken-1" @click="close" :disabled="isSaving">Cancel</VBtn>
        <VBtn color="primary" elevation="2" @click="save" :loading="isSaving" :disabled="isSaving">
          {{ isEditMode ? 'Save Changes' : 'Add Budget' }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style scoped>
.custom-modal-shadow {
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}
</style>

