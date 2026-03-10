<script setup>
import api from "@fms/utils/api";
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  data: Object,
  type: String, // 'group' or 'object'
})
const emit = defineEmits(['update:modelValue', 'deleted'])

const isOpen = ref(props.modelValue)
const isDeleting = ref(false)

watch(
  () => props.modelValue,
  val => {
    isOpen.value = val
    if (val) isDeleting.value = false
  }
)

watch(isOpen, val => {
  emit('update:modelValue', val)
})

function close() {
  if (!isDeleting.value) {
    isOpen.value = false
  }
}

async function confirm() {
  try {
    isDeleting.value = true

    if (props.type === 'group') {
      // Delete group -> backend cascades objects
      await api.delete(
        `/group-object-expenditures/${props.data.group_id}`)
    } else {
      // Delete only object
      await api.delete(`/object-expenditures/${props.data.object_id}`)
    }

    emit('deleted')
    isOpen.value = false
  } catch (err) {
    console.error(err)
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <VDialog
    v-model="isOpen"
    max-width="500"
    transition="dialog-bottom-transition"
  >
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <!-- Title with Trash Icon -->
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-trash" color="error" size="28" class="me-3" />
        Confirm Delete
      </VCardTitle>

      <!-- Subtext -->
      <VCardSubtitle
        class="text-body-2 text-grey-darken-1 mb-5 description-text"
      >
        <template v-if="type === 'group'">
          Are you sure you want to delete this
          <strong>group</strong>?  
          <br />
          This will also remove all related
          <strong>object expenditures</strong>.
          <br />
          <span class="text-error">This action cannot be undone.</span>
        </template>

        <template v-else>
          Are you sure you want to delete this
          <strong>object</strong>?  
          <br />
          <span class="text-error">This action cannot be undone.</span>
        </template>
      </VCardSubtitle>

      <!-- Actions -->
      <VCardActions class="justify-end mt-4">
        <VBtn
          color="grey-darken-1"
          class="me-2 px-4"
          @click="close"
          :disabled="isDeleting"
        >
          <VIcon icon="bx-x" class="me-1" />
          No
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
