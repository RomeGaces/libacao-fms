<script setup>
import fountainPen from '@/../icons/fountain_pen_3d.png'
import api from "@fms/utils/api";
import { computed, defineEmits, defineProps, ref, watch } from 'vue'

// Props
const props = defineProps({
  modelValue: Boolean,
  data: {
    type: Object,
    default: () => ({}),
  },
  type: String, // 'group' or 'object'
})
const emit = defineEmits(['update:modelValue', 'saved'])
// Local state
const isOpen = ref(props.modelValue)
const localData = ref({})
const isSaving = ref(false)
const errors = ref({})
const globalError = ref('')

// Normalize incoming props for form binding
function normalizeData(data) {
  console.log(data);
  if (!data) return {}
  if (props.type === 'group') {
    return {
      ...data,
      id: data.group_id,
      group_name: data.group_name ?? '',
    }
  } else {
    return {
      ...data,
      id: data.object_id,
      object_expenditure: data.object_expenditure ?? data.object_name ?? '',
      account_code: data.account_code ?? '',
      group_id: data.group_id ?? null,
    }
  }
}

// Edit mode check
const isEditMode = computed(() => !!localData.value?.id)

// Sync parent → local
watch(
  () => props.modelValue,
  val => {
    isOpen.value = val
    if (val) {
      localData.value = normalizeData(props.data)
      errors.value = {}
      globalError.value = ''
      isSaving.value = false
    }
  },
  { immediate: true }
)

// Sync local → parent
watch(isOpen, val => {
  emit('update:modelValue', val)
})

// Validation
function validate() {
  const e = {}

  if (props.type === 'group') {
    if (!localData.value.group_name?.trim()) {
      e.group_name = ['Group name is required.']
    }
  } else {
    if (!localData.value.group_id) {
      e.group_id = ['Group is required.']
    }
    if (!localData.value.object_expenditure?.trim()) {
      e.object_expenditure = ['Object Expenditure is required.']
    }
    if (!localData.value.account_code?.trim()) {
      e.account_code = ['Account Code is required.']
    }
  }

  errors.value = e
  return Object.keys(e).length === 0
}

// Save handler
async function save() {
  try {
    isSaving.value = true
    errors.value = {}
    globalError.value = ''

    if (!validate()) {
      isSaving.value = false
      return
    }

    if (props.type === 'group') {
      const payload = { group_name: localData.value.group_name.trim() }

      if (isEditMode.value) {
        await api.put(`/group-object-expenditures/${localData.value.id}`,
          payload)
      } else {
        await api.post(`/group-object-expenditures`,
          payload)
      }
    } else {
      const payload = {
        group_id: Number(localData.value.group_id),
        object_expenditure: localData.value.object_expenditure.trim(),
        account_code: localData.value.account_code.trim(),
      }

      if (isEditMode.value) {
        await api.put(`/object-expenditures/${localData.value.id}`,
          payload)
      } else {
        await api.post(`/object-expenditures`,
          payload)
      }
    }

    emit('saved')
    isOpen.value = false
  } catch (err) {
    if (err?.response?.status === 422) {
      errors.value = err.response.data?.errors || {}
    } else {
      globalError.value =
        err?.response?.data?.message ||
        err?.message ||
        'Something went wrong while saving.'
      console.error('Save failed:', err?.response ?? err)
    }
  } finally {
    isSaving.value = false
  }
}

function close() {
  if (!isSaving.value) {
    isOpen.value = false
  }
}
</script>

<template>
  <VDialog v-model="isOpen" max-width="600" transition="dialog-bottom-transition">
    <VCard class="custom-modal rounded-lg pa-5">
      <!-- Title -->
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <img
          :src="fountainPen"
          alt="Fountain Pen"
          class="me-3"
          style="width: 28px; height: 28px;"
        />
        {{ isEditMode ? `Edit ${type === 'group' ? 'Group' : 'Object'}` : `Add ${type === 'group' ? 'Group' : 'Object'}` }}
      </VCardTitle>

      <!-- Subtext -->
      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        {{ isEditMode
          ? `Update the ${type === 'group' ? 'group name' : 'object details'}.`
          : `Fill in the details to add a new ${type === 'group' ? 'group' : 'object expenditure'}.`
        }}
      </VCardSubtitle>

      <!-- Server error -->
      <VCardText v-if="globalError" class="pt-0">
        <VAlert type="error" variant="tonal" class="mb-4">
          {{ globalError }}
        </VAlert>
      </VCardText>

      <!-- Form -->
      <VCardText>
        <div class="d-flex flex-column gap-6">
          <!-- Group -->
          <VTextField
            v-if="type === 'group'"
            v-model="localData.group_name"
            label="Group Name"
            variant="outlined"
            density="comfortable"
            :disabled="isSaving"
            :error-messages="errors.group_name"
          />

          <!-- Object -->
          <template v-else>
            <VTextField
              v-model="localData.object_expenditure"
              label="Object Expenditure"
              variant="outlined"
              density="comfortable"
              :disabled="isSaving"
              :error-messages="errors.object_expenditure"
            />
            <VTextField
              v-model="localData.account_code"
              label="Account Code"
              variant="outlined"
              density="comfortable"
              :disabled="isSaving"
              :error-messages="errors.account_code"
            />
            <input type="hidden" v-model="localData.group_id" />
            <div v-if="errors.group_id" class="text-error text-caption">
              {{ errors.group_id?.[0] }}
            </div>
          </template>
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

<style scoped>
.custom-modal {
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18) !important;
  border-radius: 12px;
}
.description-text {
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: unset !important;
}
.text-error {
  color: #d32f2f;
}
</style>
