<template>
  <VDialog
    v-model="isOpen"
    max-width="800"
    transition="dialog-bottom-transition"
    persistent
    scrollable
  >
    <VCard class="custom-modal-shadow rounded-lg pa-5">
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <VIcon icon="bx-sitemap" class="me-3" color="primary" size="28" />
        {{ isEditMode ? 'Edit Paper Trail Flow' : 'Add New Paper Trail Flow' }}
      </VCardTitle>

      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        {{ isEditMode
          ? 'Modify the flow steps and owners. Changes will affect new documents using this flow.'
          : 'Create a new sequence of steps for a document paper trail.'
        }}
      </VCardSubtitle>

      <VCardText>
        <VForm>
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="form.set_no"
                label="Set Number"
                variant="outlined"
                density="comfortable"
                :readonly="true"
                :loading="isFetchingCount" />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="form.office_code"
                label="Office Code (Group)"
                :items="officeCodes"
                item-title="description"
                item-value="office_code"
                variant="outlined"
                density="comfortable"
                :loading="isLoadingOfficeCodes"
              />
            </VCol>
          </VRow>

          <VDivider class="my-6" />

          <div class="d-flex justify-space-between align-center mb-5">
            <h6 class="text-h6">
              Flow Steps
            </h6>
            <VBtn
              color="primary"
              variant="tonal"
              @click="addStep"
            >
              <VIcon icon="bx-plus" class="me-1" /> Add Step
            </VBtn>
          </div>

          <div v-if="form.steps.length > 0" class="steps-list-container">
            <div
              v-for="(step, stepIndex) in form.steps"
              :key="step._key"
              class="step-item"
            >
              <div class="line-container">
                <div class="dot" />
              </div>

              <div class="content-container">
                <div class="step-header">
                  <div class="d-flex align-center">
                    <VIcon icon="bx-grid-vertical" class="drag-handle" />
                    <span class="font-weight-bold">Step {{ stepIndex + 1 }}</span>
                    <VChip
                      v-if="isStepUsed(step.id)"
                      color="warning"
                      variant="tonal"
                      size="x-small"
                      class="ml-2"
                    >
                      In Use
                    </VChip>
                  </div>
                  <div class="step-actions">
                    <VBtn 
                      icon 
                      variant="text" 
                      size="small" 
                      @click="moveStep(stepIndex, -1)" 
                      :disabled="stepIndex === 0 || isStepUsed(step.id)"
                    >
                      <VIcon icon="bx-up-arrow-alt" />
                    </VBtn>
                    <VBtn 
                      icon 
                      variant="text" 
                      size="small" 
                      @click="moveStep(stepIndex, 1)" 
                      :disabled="stepIndex === form.steps.length - 1 || isStepUsed(step.id)"
                    >
                      <VIcon icon="bx-down-arrow-alt" />
                    </VBtn>
                    <VBtn 
                      icon 
                      variant="text" 
                      size="small" 
                      color="error" 
                      @click="removeStep(stepIndex)"
                      :disabled="isStepUsed(step.id)"
                    >
                      <VIcon icon="bx-x" />
                    </VBtn>
                  </div>
                </div>

                <div class="step-body">
                  <VSelect
                    v-model="step.office_code_id"
                    label="Office Code (Step Owner)"
                    :items="officeCodes.filter(oc => oc.office_code !== 'All')"
                    item-title="description"
                    item-value="id"
                    variant="outlined"
                    density="comfortable"
                    class="mb-4"
                    :loading="isLoadingOfficeCodes"
                    :disabled="isStepUsed(step.id)"
                    hide-details
                  />
                  
                  <VDivider class="my-4" />
                  <div class="d-flex justify-space-between align-center mb-3">
                    <span class="text-subtitle-1 font-weight-medium">Internal Approvals</span>
                    <VBtn 
                      size="small" 
                      variant="tonal" 
                      @click="addInternalStep(step)"
                      :disabled="areAllInternalStepsUsed(step)"
                    >
                      <VIcon icon="bx-plus" size="small" class="me-1" /> Add
                    </VBtn>
                  </div>
                  <div v-if="step.internal_steps.length > 0" class="d-flex flex-column gap-3">
                    <div
                      v-for="(internalStep, internalIndex) in step.internal_steps"
                      :key="internalStep._key"
                      class="d-flex align-center gap-2"
                    >
                      <VTextField
                        v-model="internalStep.approval_title"
                        label="Approval Title"
                        variant="outlined"
                        density="compact"
                        hide-details
                        :disabled="isInternalStepUsed(internalStep.id)"
                        class="flex-grow-1"
                      />
                      <VChip
                        v-if="isInternalStepUsed(internalStep.id)"
                        color="warning"
                        variant="tonal"
                        size="small"
                      >
                        In Use
                      </VChip>
                      <VBtn
                        icon
                        variant="text"
                        size="small"
                        color="error"
                        @click="removeInternalStep(step, internalIndex)"
                        :disabled="isInternalStepUsed(internalStep.id)"
                      >
                        <VIcon icon="bx-trash" />
                      </VBtn>
                    </div>
                  </div>
                  <div v-else class="text-center text-caption text-grey py-2">
                    No internal approvals for this step.
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="steps-empty-state">
            <VIcon icon="bx-list-plus" size="48" />
            <p class="mt-4 text-h6">No steps in this flow yet.</p>
            <p class="text-grey-darken-1">Click "Add Step" to get started.</p>
          </div>
        </VForm>
      </VCardText>

      <VCardActions class="justify-end mt-6">
        <VBtn
          color="grey-darken-1"
          class="me-2 px-4"
          variant="text"
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
          @click="save"
        >
          <VIcon :icon="isEditMode ? 'bx-save' : 'bx-plus'" class="me-1" />
          {{ isEditMode ? 'Save Changes' : 'Create Flow' }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import api from '@fms/utils/api';

const props = defineProps({
  modelValue: Boolean,
  setData: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'save']);

const isOpen = ref(props.modelValue);
const isSaving = ref(false);
const officeCodes = ref([]);
const isLoadingOfficeCodes = ref(false);
const isFetchingCount = ref(false);
const usedStepIds = ref([]);
const usedInternalStepIds = ref([]);

const defaultForm = {
  id: null,
  set_no: '',
  office_code: 'All',
  steps: [],
};
const form = ref({ ...defaultForm });

const isEditMode = computed(() => !!form.value.id);

const isStepUsed = (stepId) => {
  return stepId && usedStepIds.value.includes(stepId);
};

const isInternalStepUsed = (internalStepId) => {
  return internalStepId && usedInternalStepIds.value.includes(internalStepId);
};

const areAllInternalStepsUsed = (step) => {
  // If step has no internal steps yet, allow adding
  if (!step.internal_steps || step.internal_steps.length === 0) {
    return false;
  }
  
  // Check if all internal steps are in use
  const allUsed = step.internal_steps.every(internalStep => 
    isInternalStepUsed(internalStep.id)
  );
  
  return allUsed;
};

const fetchNextSetNumber = async () => {
  isFetchingCount.value = true;
  try {
    const response = await api.get('/paper-trail-sets?per_page=1');
    const totalSets = response.data.total || 0;
    form.value.set_no = totalSets + 1;
  } catch (error) {
    console.error("Failed to fetch paper trail set count:", error);
    form.value.set_no = 'Error';
  } finally {
    isFetchingCount.value = false;
  }
};

const fetchUsedSteps = async (setId) => {
  if (!setId) return;
  
  try {
    const response = await api.get(`/paper-trail-sets/${setId}/used-steps`);
    usedStepIds.value = response.data.step_ids || [];
    usedInternalStepIds.value = response.data.internal_step_ids || [];
  } catch (error) {
    console.error("Failed to fetch used steps:", error);
    usedStepIds.value = [];
    usedInternalStepIds.value = [];
  }
};

const fetchOfficeCodes = async () => {
  isLoadingOfficeCodes.value = true;
  try {
    const response = await api.get('/office-codes');
    const data = Array.isArray(response.data.data) ? response.data.data : response.data;
    officeCodes.value = [{ id: null, office_code: 'All', description: 'All Offices' }, ...data];
  } catch (error) {
    console.error("Failed to fetch office codes:", error);
    officeCodes.value = [{ id: null, office_code: 'All', description: 'All Offices' }];
  } finally {
    isLoadingOfficeCodes.value = false;
  }
};

const save = () => {
  isSaving.value = true;
  
  const payload = JSON.parse(JSON.stringify(form.value));
  
  payload.steps = payload.steps.map(step => {
    delete step.office_code_step_owner;
    delete step.office_code;
    delete step._key;
    
    step.internal_steps = step.internal_steps.map(internal => {
      delete internal._key;
      return internal;
    });

    return step;
  });

  emit('save', payload, (success) => {
    isSaving.value = false;
    if (success) {
      close();
    }
  });
};

const close = () => {
  isOpen.value = false;
};

const addStep = () => {
  form.value.steps.push({
    _key: crypto.randomUUID(),
    id: null,
    office_code_id: null,
    internal_steps: [
      { 
        _key: crypto.randomUUID(),
        id: null, 
        approval_title: '' 
      }
    ],
  });
};

const removeStep = (index) => {
  form.value.steps.splice(index, 1);
};

const addInternalStep = (step) => {
  if (!step.internal_steps) {
    step.internal_steps = [];
  }
  step.internal_steps.push({ 
    _key: crypto.randomUUID(),
    id: null, 
    approval_title: '' 
  });
};

const removeInternalStep = (step, internalIndex) => {
  step.internal_steps.splice(internalIndex, 1);
};

const moveStep = (index, direction) => {
  const newIndex = index + direction;
  if (newIndex < 0 || newIndex >= form.value.steps.length) return;
  
  const element = form.value.steps.splice(index, 1)[0];
  form.value.steps.splice(newIndex, 0, element);
};

watch(() => props.modelValue, async (val) => {
  isOpen.value = val;
  if (val) {
    if (props.setData) {
      const data = JSON.parse(JSON.stringify(props.setData));
      data.steps = data.steps.map(step => ({
        ...step,
        _key: crypto.randomUUID(),
        internal_steps: step.internal_steps.map(internal => ({
          ...internal,
          _key: crypto.randomUUID()
        }))
      }));
      form.value = data;
      
      // Fetch which steps are in use
      await fetchUsedSteps(data.id);
    } else {
      form.value = { ...defaultForm, steps: [] };
      usedStepIds.value = [];
      usedInternalStepIds.value = [];
      fetchNextSetNumber();
    }
  }
});

watch(isOpen, (val) => {
  emit('update:modelValue', val);
});

onMounted(() => {
  fetchOfficeCodes();
});
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

.steps-list-container {
  display: flex;
  flex-direction: column;
}

.step-item {
  display: flex;
  position: relative;
  gap: 1rem;
}

.line-container {
  position: relative;
  width: 12px;
  flex-shrink: 0;
}

.line-container::before {
  content: '';
  position: absolute;
  width: 2px;
  background-color: #e0e0e0;
  left: 50%;
  transform: translateX(-50%);
  top: 0;
  bottom: 0;
}

.dot {
  width: 12px;
  height: 12px;
  border: 2px solid #e0e0e0; 
  background-color: white;
  border-radius: 50%;
  position: absolute;
  top: 18px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1;
}

.step-item:first-child .line-container::before {
  top: 18px;
}

.step-item:last-child .line-container::before {
  bottom: calc(100% - 18px);
}

.content-container {
  flex-grow: 1;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  background-color: #fff;
}

.step-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.5rem 0.5rem 1rem;
  background-color: #f9f9f9;
  border-bottom: 1px solid #e0e0e0;
  border-radius: 8px 8px 0 0;
}

.drag-handle {
  cursor: grab;
  color: #9e9e9e;
  margin-right: 0.75rem;
}

.step-actions .v-btn {
  color: #757575;
}

.step-body {
  padding: 1.25rem;
}

.steps-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  border: 2px dashed #e0e0e0;
  border-radius: 8px;
  color: #757575;
  margin-top: 1rem;
}
</style>