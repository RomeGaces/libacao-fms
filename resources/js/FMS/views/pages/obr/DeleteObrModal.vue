<template>
  <VDialog v-model="isOpen" max-width="500" transition="dialog-bottom-transition">
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-trash" color="error" size="28" class="me-3" />
        Confirm Delete
      </VCardTitle>

      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        Are you sure you want to delete OBR
        <strong>{{ obr?.obr_no }}</strong>? This action <span class="text-error">cannot be undone</span>.
      </VCardSubtitle>

      <VCardActions class="justify-end mt-4">
        <VBtn color="grey-darken-1" class="me-2 px-4" @click="close" :disabled="isDeleting">
          <VIcon icon="bx-x" class="me-1" /> No
        </VBtn>
        <VBtn color="error" class="px-5" elevation="2" :loading="isDeleting" :disabled="isDeleting" @click="confirm">
          <VIcon icon="bx-trash" class="me-1" /> Yes, Delete
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
const isDeleting = ref(false)

watch(() => props.modelValue, (val) => {
    isOpen.value = val
    if (val) isDeleting.value = false
})

watch(isOpen, (val) => {
  emit('update:modelValue', val)
})

function close() {
  if (!isDeleting.value) isOpen.value = false
}

function confirm() {
  isDeleting.value = true
  emit('confirm', (success = true) => {
      isDeleting.value = false
      if (success) isOpen.value = false
  })
}
</script>

<style scoped>
/* Reuse styles from your original Delete modal */
.custom-modal-shadow {
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}
.description-text {
  white-space: normal !important;
}
</style>