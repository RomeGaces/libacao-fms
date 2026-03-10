<template>
  <VDialog v-model="isOpen" max-width="500" transition="dialog-bottom-transition">
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-chevron-left-circle" color="warning" size="28" class="me-3" />
        Return to Previous Process
      </VCardTitle>

      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        Are you sure you want to return OBR
        <strong>{{ obr?.obr_no }}</strong> to the previous process step?
        This will move it back from <strong>{{ currentStage }}</strong> to <strong>{{ previousStage }}</strong>.
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
          color="warning"
          class="px-5"
          elevation="2"
          :loading="isProcessing"
          :disabled="isProcessing"
          @click="confirm"
        >
          <VIcon icon="bx-check" class="me-1" />
          Yes, Return
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
  currentStage: String,
  previousStage: String,
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const isOpen = ref(props.modelValue)
const isProcessing = ref(false)

watch(() => props.modelValue, (val) => {
  isOpen.value = val
  if (val) isProcessing.value = false
})

watch(isOpen, (val) => {
  emit('update:modelValue', val)
})

function close() {
  if (!isProcessing.value) isOpen.value = false
}

function confirm() {
  isProcessing.value = true
  emit('confirm', (success = true) => {
    isProcessing.value = false
    if (success) isOpen.value = false
  })
}
</script>

<style scoped>
.custom-modal-shadow {
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}
.description-text {
  white-space: normal !important;
  line-height: 1.6;
}
</style>