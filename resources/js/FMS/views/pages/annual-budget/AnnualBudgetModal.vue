<script setup>
import { ref, watch, computed } from 'vue';
import fountainPen from '@/../icons/fountain_pen_3d.png';

const props = defineProps({
  modelValue: Boolean,
  budget: {
    type: Object,
    default: () => ({ year: '', annual_budget: '' }),
  },
  totalAllocated: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['update:modelValue', 'save']);

const isOpen = ref(props.modelValue);
const isSaving = ref(false);
const localBudget = ref({ ...props.budget });
const errorMessage = ref('');

const isEditMode = computed(() => !!props.budget?.id);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US').format(value);
}

// Watchers to sync state
watch(() => props.modelValue, (val) => {
  isOpen.value = val;
  if (val) {
    localBudget.value = { ...props.budget };
    isSaving.value = false;
    errorMessage.value = ''; // Clear errors when opening
  }
});

watch(isOpen, (val) => {
  emit('update:modelValue', val);
});

watch(() => localBudget.value.annual_budget, () => {
    errorMessage.value = ''; // Clear error when user types
});


function close() {
  if (!isSaving.value) {
    isOpen.value = false;
  }
}

function save() {
  errorMessage.value = '';
  const newAmount = Number(localBudget.value.annual_budget);

  // Validation Check
  if (isEditMode.value && newAmount < props.totalAllocated) {
    errorMessage.value = `Budget cannot be less than the already allocated amount of ₱${formatCurrency(props.totalAllocated)}.`;
    return;
  }
  
  if (!localBudget.value.year || !localBudget.value.annual_budget) {
      errorMessage.value = 'Both year and budget amount are required.';
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
</script>

<template>
  <VDialog v-model="isOpen" max-width="600" transition="dialog-bottom-transition">
    <VCard class="pa-5 custom-modal-shadow">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <img :src="fountainPen" alt="Icon" class="me-3" style="width: 28px;" />
        {{ isEditMode ? 'Edit Annual Budget' : 'Add Annual Budget' }}
      </VCardTitle>

      <VCardText class="mt-4">
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="localBudget.year"
              label="Budget Year"
              type="number"
              variant="outlined"
              density="comfortable"
              :disabled="isSaving"
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="localBudget.annual_budget"
              label="Budget Amount"
              type="number"
              prefix="₱"
              variant="outlined"
              density="comfortable"
              :disabled="isSaving"
              :hint="isEditMode ? `Allocated: ₱${formatCurrency(totalAllocated)}. New budget cannot be lower.` : ''"
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

