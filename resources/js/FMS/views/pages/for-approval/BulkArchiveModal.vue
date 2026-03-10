<template>
  <VDialog 
    v-model="isOpen" 
    max-width="600" 
    persistent
    transition="dialog-bottom-transition"
  >
    <VCard class="archive-modal-shadow">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium bg-grey-lighten-4 pa-4">
        <VIcon icon="bx-archive" color="warning" size="28" class="me-2" />
        Archive OBR Requests
      </VCardTitle>

      <VCardText class="pa-6">
        <VAlert type="warning" variant="tonal" class="mb-4">
          <VAlertTitle class="mb-2">Archive {{ selectedObrs.length }} OBR(s)?</VAlertTitle>
          <div class="text-body-2">
            These OBR requests will be removed from the workflow and stored in the archive.
            This action can be useful for removing outdated or cancelled requests.
          </div>
        </VAlert>

        <!-- Selected OBRs List -->
        <div class="mb-4">
          <div class="text-subtitle-2 font-weight-medium mb-2">Selected OBRs:</div>
          <div class="obr-list">
            <VChip
              v-for="obr in selectedObrs"
              :key="obr.id"
              size="small"
              class="ma-1"
              color="primary"
              variant="tonal"
            >
              {{ obr.obr_no }}
            </VChip>
          </div>
        </div>

        <VDivider class="my-4" />

        <!-- Archive Reason -->
        <VTextarea
          v-model="archiveReason"
          label="Archive Reason *"
          placeholder="Please provide a reason for archiving these OBR requests..."
          variant="outlined"
          rows="4"
          :error-messages="errors.archiveReason"
          counter="1000"
          maxlength="1000"
          required
        />

        <div class="text-caption text-grey-darken-1 mt-2">
          * This reason will be recorded in the archive history
        </div>
      </VCardText>

      <VCardActions class="pa-4 bg-grey-lighten-5">
        <VSpacer />
        <VBtn color="grey" variant="text" @click="close" :disabled="loading">
          Cancel
        </VBtn>
        <VBtn
          color="warning"
          variant="flat"
          @click="confirm"
          :loading="loading"
          :disabled="!archiveReason.trim()"
        >
          <VIcon icon="bx-archive" class="me-1" />
          Archive {{ selectedObrs.length }} OBR(s)
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  selectedObrs: {
    type: Array,
    default: () => [],
  },
  currentStage: String,
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const isOpen = ref(props.modelValue);
const archiveReason = ref('');
const loading = ref(false);
const errors = ref({
  archiveReason: [],
});

watch(() => props.modelValue, (val) => {
  isOpen.value = val;
  if (val) {
    // Reset form when modal opens
    archiveReason.value = '';
    errors.value = { archiveReason: [] };
  }
});

watch(isOpen, (val) => {
  if (val !== props.modelValue) {
    emit('update:modelValue', val);
  }
});

const validate = () => {
  errors.value = { archiveReason: [] };
  let isValid = true;

  if (!archiveReason.value.trim()) {
    errors.value.archiveReason = ['Archive reason is required'];
    isValid = false;
  } else if (archiveReason.value.trim().length < 10) {
    errors.value.archiveReason = ['Archive reason must be at least 10 characters'];
    isValid = false;
  }

  return isValid;
};

const confirm = async () => {
  if (!validate()) return;

  loading.value = true;

  emit('confirm', archiveReason.value, (success) => {
    loading.value = false;
    if (success) {
      close();
    }
  });
};

const close = () => {
  if (!loading.value) {
    isOpen.value = false;
  }
};
</script>

<style scoped>
.archive-modal-shadow {
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}

.obr-list {
  max-height: 120px;
  overflow-y: auto;
  padding: 8px;
  background: #f5f5f5;
  border-radius: 4px;
}
</style>