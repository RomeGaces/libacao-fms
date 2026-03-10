<template>
  <VDialog
    v-model="isOpen"
    max-width="600"
    transition="dialog-bottom-transition"
  >
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <!-- Title with Fountain Pen Icon -->
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <img
          :src="fountainPen"
          alt="Fountain Pen"
          class="me-3"
          style="width: 28px; height: 28px;"
        />
        {{ isEditMode ? 'Edit Office Code' : 'Add Office Code' }}
      </VCardTitle>

      <!-- Subtext -->
      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        {{ isEditMode
          ? 'Update the office code and description. These changes will reflect across all related modules.'
          : 'Fill in the details to add a new office code. This will be available across all related modules.'
        }}
      </VCardSubtitle>

      <!-- Inputs -->
      <VCardText>
        <div class="d-flex flex-column gap-6">
          <VTextField
            v-model="localOfficeCode.office_code"
            label="Office Code"
            variant="outlined"
            density="comfortable"
            :disabled="isSaving"
          />
          <VTextField
            v-model="localOfficeCode.description"
            label="Description"
            variant="outlined"
            density="comfortable"
            :disabled="isSaving"
          />
        </div>
      </VCardText>

      <!-- Actions -->
      <VCardActions class="justify-end mt-6">
        <VBtn
          color="grey-darken-1"
          class="me-2 px-4"
          @click="close"
          :disabled="isSaving"
        >
          <VIcon icon="bx-x" class="me-1" />
          Cancel
        </VBtn>

        <VBtn
          color="primary"
          class="px-5"
          elevation="2"
          :loading="isSaving"
          :disabled="isSaving"
          @click="save"
        >
          <VIcon :icon="isEditMode ? 'bx-save' : 'bx-plus'" class="me-1" />
          {{ isEditMode ? 'Save' : 'Add' }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import fountainPen from '@/../icons/fountain_pen_3d.png'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  officeCode: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['update:modelValue', 'save'])

const isOpen = ref(props.modelValue)
const localOfficeCode = ref({ ...props.officeCode })
const isSaving = ref(false)

const isEditMode = computed(() => !!props.officeCode?.id)

// Sync parent -> local
watch(
  () => props.modelValue,
  (val) => {
    isOpen.value = val
    if (val) {
      localOfficeCode.value = { ...props.officeCode }
      isSaving.value = false
    }
  }
)

// Sync local -> parent
watch(isOpen, (val) => {
  emit('update:modelValue', val)
})

function close() {
  if (!isSaving.value) isOpen.value = false
}

function save() {
  isSaving.value = true
  emit('save', localOfficeCode.value, stopSaving)
}

function stopSaving(success = true) {
  isSaving.value = false
  if (success) isOpen.value = false
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
