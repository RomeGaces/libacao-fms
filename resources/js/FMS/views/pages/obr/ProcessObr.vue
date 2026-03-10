<template>
  <VDialog v-model="isOpen" max-width="500" transition="dialog-bottom-transition">
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-send" color="primary" size="28" class="me-3" />
        Confirm OBR Processing
      </VCardTitle>

      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        Are you sure you want to process OBR
        <strong>{{ obr?.obr_no }}</strong>? The paper trail for this request
        will begin after this action.
      </VCardSubtitle>

      <VCardActions class="justify-end mt-4">
        <VBtn
          color="grey-darken-1"
          class="me-2 px-4"
          @click="close"
          :disabled="isProcessing"
        >
          <VIcon icon="bx-x" class="me-1" />
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          class="px-5"
          elevation="2"
          :loading="isProcessing"
          :disabled="isProcessing"
          @click="confirm"
        >
          <VIcon icon="bx-check" class="me-1" />
          Yes, Process
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  obr: Object,
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const isOpen = ref(props.modelValue)
// Renamed from isDeleting to isProcessing
const isProcessing = ref(false)

watch(() => props.modelValue, (val) => {
  isOpen.value = val
  // Reset processing state when modal opens
  if (val) isProcessing.value = false
})

watch(isOpen, (val) => {
  emit('update:modelValue', val)
})

function close() {
  // Check isProcessing
  if (!isProcessing.value) isOpen.value = false
}

function confirm() {
  // Set isProcessing to true
  isProcessing.value = true
  emit('confirm', (success = true) => {
    // Callback to stop loading
    isProcessing.value = false
    if (success) isOpen.value = false
  })
}
</script>

<style scoped>
/* Re-using the same styles as your example */
.custom-modal-shadow {
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}
.description-text {
  /* Ensures long text wraps nicely */
  white-space: normal !important;
  line-height: 1.6;
}
</style>