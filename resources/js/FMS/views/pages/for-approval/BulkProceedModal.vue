<template>
  <VDialog v-model="isOpen" max-width="600" transition="dialog-bottom-transition">
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-chevron-right-circle" color="success" size="28" class="me-3" />
        Proceed Multiple OBRs
      </VCardTitle>

      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        Are you sure you want to proceed <strong>{{ selectedObrs.length }} OBR(s)</strong> to the next process step?
        <span v-if="nextStage">
          This will move them from <strong>{{ currentStage }}</strong> to <strong>{{ nextStage }}</strong>.
        </span>
        <span v-else>
          This will complete the process for <strong>{{ currentStage }}</strong> and move to the next department.
        </span>
      </VCardSubtitle>

      <VCardText v-if="selectedObrs.length > 0" class="pa-0">
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
          color="success"
          class="px-5"
          elevation="2"
          :loading="isProcessing"
          :disabled="isProcessing"
          @click="confirm"
        >
          <VIcon icon="bx-check" class="me-1" />
          Yes, Proceed All
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
  nextStage: String,
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