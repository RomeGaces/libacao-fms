<template>
  <VDialog
    v-model="isOpen"
    max-width="500"
    transition="dialog-bottom-transition"
    persistent
  >
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <!-- Title with Trash Icon -->
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-trash" color="error" size="28" class="me-3" />
        Confirm Delete
      </VCardTitle>

      <!-- Subtext with budget details -->
      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        Are you sure you want to delete the annual budget for
        <strong>{{ budget?.year }}</strong>?
        This action <span class="text-error">cannot be undone</span>.
      </VCardSubtitle>

      <!-- Action Buttons -->
      <VCardActions class="justify-end mt-4">
        <VBtn
          color="grey-darken-1"
          class="me-2 px-4"
          @click="close"
          :disabled="isDeleting"
        >
          <VIcon icon="bx-x" class="me-1" />
          Cancel
        </VBtn>

        <VBtn
          color="error"
          class="px-5"
          elevation="2"
          :loading="isDeleting"
          :disabled="isDeleting"
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
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  budget: Object,
})
const emit = defineEmits(['update:modelValue', 'confirm'])

const isOpen = ref(props.modelValue)
const isDeleting = ref(false)

// Watch for the modelValue prop to change from the parent
watch(
  () => props.modelValue,
  (val) => {
    isOpen.value = val
    if (val) {
      isDeleting.value = false // Reset spinner every time modal opens
    }
  }
)

// Watch for changes inside the modal (e.g., closing by clicking outside)
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
  emit('confirm', stopDeleting)
}

// This function must be called by the parent after the API call is complete
function stopDeleting(success = true) {
  isDeleting.value = false
  if (success) {
    isOpen.value = false
  }
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
}
</style>
