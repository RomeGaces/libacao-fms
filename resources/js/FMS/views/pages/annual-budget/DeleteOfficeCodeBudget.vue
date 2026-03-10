<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  budget: Object,
})
const emit = defineEmits(['update:modelValue', 'confirm'])

const isOpen = ref(props.modelValue)
const isDeleting = ref(false)

watch(() => props.modelValue, (val) => {
  isOpen.value = val
  if (val) {
    isDeleting.value = false
  }
})

watch(isOpen, (val) => {
  emit('update:modelValue', val)
})

function close() {
  if (!isDeleting.value) {
    isOpen.value = false
  }
}

function confirm() {
  isDeleting.value = true
  emit('confirm', (success = true) => {
    isDeleting.value = false
    if (success) {
      isOpen.value = false
    }
  })
}
</script>

<template>
  <VDialog v-model="isOpen" max-width="500" transition="dialog-bottom-transition">
    <VCard class="pa-5 custom-modal-shadow">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-trash" color="error" size="28" class="me-3" />
        Confirm Deletion
      </VCardTitle>

      <VCardSubtitle class="wrap-text">
        Are you sure you want to delete the budget for <strong>{{ budget?.office_code?.office_code }}</strong>?
        This action cannot be undone.
      </VCardSubtitle>

      <VCardActions class="justify-end mt-4">
        <VBtn color="grey-darken-1" @click="close" :disabled="isDeleting">Cancel</VBtn>
        <VBtn color="error" elevation="2" @click="confirm" :loading="isDeleting" :disabled="isDeleting">
          Yes, Delete
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

.wrap-text {
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: unset !important;
}
</style>

