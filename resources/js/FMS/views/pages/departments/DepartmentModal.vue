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
        {{ isEditMode ? 'Edit Department' : 'Add Department' }}
      </VCardTitle>

      <!-- Subtext -->
      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        {{ isEditMode
          ? 'Update the department name and code. These changes will reflect across all related modules.'
          : 'Fill in the details to add a new department. This will be available across all related modules.'
        }}
      </VCardSubtitle>

      <!-- Inputs -->
      <VCardText>
        <div class="d-flex flex-column gap-6">
          <VTextField
            v-model="localDept.name"
            label="Department Name"
            variant="outlined"
            density="comfortable"
            :disabled="isSaving"
          />
          <VTextField
            v-model="localDept.department_code"
            label="Department Code"
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
  department: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['update:modelValue', 'save'])

const isOpen = ref(props.modelValue)
const localDept = ref({ ...props.department })
const isSaving = ref(false)

// Computed: check if we are in edit mode
const isEditMode = computed(() => !!props.department?.id)

// Sync parent -> local
watch(
  () => props.modelValue,
  (val) => {
    isOpen.value = val
    if (val) {
      // reset form + spinner every time modal opens
      localDept.value = { ...props.department }
      isSaving.value = false
    }
  }
)

// Sync local -> parent (for outside clicks)
watch(isOpen, (val) => {
  emit('update:modelValue', val)
})

function close() {
  if (!isSaving.value) {
    isOpen.value = false
  }
}

function save() {
  isSaving.value = true
  emit('save', localDept.value, stopSaving)
}

// 👇 parent must call this when saving is done
function stopSaving(success = true) {
  isSaving.value = false
  if (success) {
    isOpen.value = false
  }
}
</script>

<style scoped>
.custom-modal-shadow {
  /* Embossed modal shadow */
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}

/* Allow subtext to wrap nicely */
.description-text {
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: unset !important;
}
</style>
