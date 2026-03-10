<template>
  <VDialog
    v-model="isOpen"
    max-width="500"
    transition="dialog-bottom-transition"
    persistent
  >
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-trash" color="error" size="28" class="me-3" />
        Confirm Deletion
      </VCardTitle>

      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        Are you sure you want to delete
        <strong>"{{ itemName }}"</strong>?
        This action <span class="text-error font-weight-medium">cannot be undone</span>.
      </VCardSubtitle>

      <VCardActions class="justify-end mt-4">
        <VBtn
          variant="text"
          color="grey-darken-1"
          class="me-2 px-4"
          @click="close"
          :disabled="loading"
        >
          <VIcon icon="bx-x" class="me-1" />
          Cancel
        </VBtn>

        <VBtn
          color="error"
          class="px-5"
          elevation="2"
          :loading="loading"
          @click="confirm"
        >
          <VIcon icon="bx-trash" class="me-1" />
          Yes, Delete
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  itemName: {
    type: String,
    default: 'this item',
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

function close() {
  if (!props.loading) {
    isOpen.value = false
  }
}

function confirm() {
  emit('confirm')
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
</style>