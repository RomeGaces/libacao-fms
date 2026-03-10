<template>
  <VDialog v-model="isOpen" max-width="700" transition="dialog-bottom-transition">
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-x-circle" color="error" size="28" class="me-3" />
        Reject Multiple OBRs
      </VCardTitle>

      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        You are about to reject <strong>{{ selectedObrs.length }} OBR(s)</strong>. 
        This will return them to the <strong>initial step of the previous office</strong> in the workflow.
      </VCardSubtitle>

      <VCardText class="pa-0">
        <!-- Selected OBRs List -->
        <div class="rounded-lg border border-grey-lighten-3 mb-4" style="max-height: 200px; overflow-y: auto;">
          <VList density="compact">
            <VListItem
              v-for="obr in selectedObrs"
              :key="obr.id"
              :title="obr.obr_no"
              :subtitle="obr.office_address"
            >
              <template v-slot:prepend>
                <VIcon icon="bx-file" size="small" class="me-2" />
              </template>
            </VListItem>
          </VList>
        </div>

        <!-- Rejection Details Textarea -->
        <VTextarea
          v-model="rejectionDetails"
          label="Rejection Reason *"
          placeholder="Please provide a detailed reason for rejecting these OBRs..."
          variant="outlined"
          rows="4"
          :error-messages="errors.rejectionDetails"
          class="mt-2"
          counter="1000"
          maxlength="1000"
        />
      </VCardText>

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
          color="error"
          class="px-5"
          elevation="2"
          :loading="isProcessing"
          :disabled="isProcessing || !rejectionDetails.trim()"
          @click="confirm"
        >
          <VIcon icon="bx-check" class="me-1" />
          Yes, Reject All
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  selectedObrs: {
    type: Array,
    default: () => []
  },
  currentStage: String,
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const isOpen = ref(props.modelValue)
const isProcessing = ref(false)
const rejectionDetails = ref('')
const errors = ref({
  rejectionDetails: []
})

watch(() => props.modelValue, (val) => {
  isOpen.value = val
  if (val) {
    isProcessing.value = false
    rejectionDetails.value = ''
    errors.value = { rejectionDetails: [] }
  }
})

watch(isOpen, (val) => {
  emit('update:modelValue', val)
})

function close() {
  if (!isProcessing.value) {
    isOpen.value = false
    rejectionDetails.value = ''
    errors.value = { rejectionDetails: [] }
  }
}

function confirm() {
  // Validate
  errors.value = { rejectionDetails: [] }
  
  if (!rejectionDetails.value.trim()) {
    errors.value.rejectionDetails = ['Rejection reason is required']
    return
  }

  if (rejectionDetails.value.length > 1000) {
    errors.value.rejectionDetails = ['Rejection reason must not exceed 1000 characters']
    return
  }

  isProcessing.value = true
  emit('confirm', rejectionDetails.value, (success = true) => {
    isProcessing.value = false
    if (success) {
      rejectionDetails.value = ''
      isOpen.value = false
    }
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