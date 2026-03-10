<template>
  <VDialog v-model="isOpen" max-width="500" persistent>
    <VCard>
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium bg-error-lighten-5 pa-4">
        <VIcon icon="bx-trash" size="28" color="error" class="me-2" />
        <span class="text-error">Permanently Delete OBR</span>
      </VCardTitle>

      <VCardText class="pt-6 pb-2">
        <VAlert type="error" variant="tonal" class="mb-4">
          <VAlertTitle class="mb-2">⚠️ Warning: This action cannot be undone!</VAlertTitle>
          <div class="text-body-2">
            You are about to permanently delete this archived OBR request. All associated data will be removed from the system.
          </div>
        </VAlert>

        <div class="obr-details pa-4 bg-grey-lighten-4 rounded mb-4">
          <div class="d-flex justify-space-between mb-2">
            <span class="text-body-2 text-grey-darken-1">OBR Number:</span>
            <span class="text-body-2 font-weight-medium">{{ obr?.obr_no }}</span>
          </div>
          <div class="d-flex justify-space-between mb-2">
            <span class="text-body-2 text-grey-darken-1">Office:</span>
            <span class="text-body-2 font-weight-medium">{{ obr?.pao_request?.office_code?.description }}</span>
          </div>
          <div class="d-flex justify-space-between">
            <span class="text-body-2 text-grey-darken-1">Archived By:</span>
            <span class="text-body-2 font-weight-medium">{{ obr?.latest_archive?.archived_by_user?.name }}</span>
          </div>
        </div>

        <div class="text-body-2 text-center text-grey-darken-2">
          Are you sure you want to proceed with this permanent deletion?
        </div>
      </VCardText>

      <VCardActions class="justify-end pa-4">
        <VBtn
          color="grey"
          variant="text"
          @click="cancel"
          :disabled="deleting"
        >
          Cancel
        </VBtn>
        <VBtn
          color="error"
          variant="flat"
          @click="confirmDeletion"
          :loading="deleting"
        >
          <VIcon icon="bx-trash" class="me-1" />
          Delete Permanently
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  obr: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const isOpen = ref(props.modelValue)
const deleting = ref(false)

watch(() => props.modelValue, (val) => {
  isOpen.value = val
})

watch(isOpen, (val) => {
  if (val !== props.modelValue) {
    emit('update:modelValue', val)
  }
})

const confirmDeletion = async () => {
  deleting.value = true
  
  await emit('confirm', (success) => {
    deleting.value = false
    if (success) {
      isOpen.value = false
    }
  })
}

const cancel = () => {
  isOpen.value = false
}
</script>

<style scoped>
.obr-details {
  border-left: 4px solid #f44336;
}
</style>