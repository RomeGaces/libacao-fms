<script setup>
import { ref, watch, computed, nextTick } from "vue";
import puzzlePiece from '@/../icons/puzzle_piece_3d.png';
import api from "@fms/utils/api";

const props = defineProps({
  modelValue: Boolean,
  data: { type: Object, default: () => ({}) },
});
const emit = defineEmits(["update:modelValue", "saved"]);

const getDefaultFormState = () => ({
  office_code_id: null,
  office_code_budget_id: null,
  created_by: JSON.parse(localStorage.getItem("user_info"))?.id || null,
  groups: [],
});

const isSaving = ref(false);
const loadingReferenceData = ref(false);
const form = ref(getDefaultFormState());

const errors = ref({
  office_code_id: false,
  office_code_budget_id: false,
  groups: [],
});

const officeCodes = ref([]);
const groups = ref([]);
const paoRequests = ref([]);
const officeCodeBudgets = ref([]);

function formatCurrency(value) {
  if (value === null || value === undefined) return '₱0.00';
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(value);
}

async function fetchOfficeCodes() {
  try {
    const res = await api.get("/office-codes");
    officeCodes.value = res.data.data;
  } catch (err) {
    console.error("Failed to fetch office codes:", err);
  }
}

async function fetchGroups() {
  try {
    const res = await api.get("/group-object-expenditures");
    groups.value = res.data;
  } catch (err) {
    console.error("Failed to fetch groups:", err);
  }
}

async function fetchPaoRequests() {
  try {
    const res = await api.get("/pao-requests");
    paoRequests.value = res.data;
  } catch (err) {
    console.error("Failed to fetch PAO requests:", err);
    paoRequests.value = [];
  }
}

async function fetchOfficeCodeBudgets(officeCodeId) {
  if (!officeCodeId) {
    officeCodeBudgets.value = [];
    return;
  }
  try {
    const res = await api.get(`/office-code-budgets/by-office-code/${officeCodeId}`);
    officeCodeBudgets.value = res.data;
  } catch (err) {
    console.error("Failed to fetch office code budgets:", err);
    officeCodeBudgets.value = [];
  }
}

const officeCodeItems = computed(() => {
  return officeCodes.value.map(office => ({
    title: office.description,
    value: office.id,
  }));
});

const budgetYearItems = computed(() => {
  const usedBudgetIds = new Set(
    paoRequests.value.map(req => Number(req.office_code_budget_id))
  );

  const items = officeCodeBudgets.value.map(budget => {
    const isEditingCurrentBudget = Number(budget.id) === Number(props.data?.office_code_budget_id);
    const isDisabled = usedBudgetIds.has(Number(budget.id)) && !isEditingCurrentBudget;

    return {
      title: budget.annual_budget.year,
      value: budget.id,
      props: { disabled: isDisabled },
    };
  });

  if (props.data?.request_id && props.data.office_code_budget_id) {
    const isCurrentBudgetInList = items.some(
      item => Number(item.value) === Number(props.data.office_code_budget_id)
    );

    if (!isCurrentBudgetInList) {
      items.unshift({
        title: props.data.budget_year,
        value: props.data.office_code_budget_id,
        props: { disabled: false },
      });
    }
  }

  return items;
});

watch(() => props.modelValue, async (isVisible) => {
  if (isVisible) {
    loadingReferenceData.value = true;
    try {
      await Promise.all([
        fetchOfficeCodes(),
        fetchGroups(),
        fetchPaoRequests(),
      ]);

      if (props.data && props.data.request_id) {
        const incomingGroups = props.data.groups ? JSON.parse(JSON.stringify(props.data.groups)) : [];
        incomingGroups.forEach(g => g._expanded = true);

        form.value = {
          office_code_id: props.data.office_code_id || null,
          office_code_budget_id: props.data.office_code_budget_id || null,
          created_by: form.value.created_by,
          groups: incomingGroups,
        };
      } else {
        form.value = getDefaultFormState();
        officeCodeBudgets.value = [];
      }
    } catch (error) {
      console.error("Failed to load reference data:", error);
    } finally {
      loadingReferenceData.value = false;
    }
  }
});

watch(() => form.value.office_code_id, (newOfficeId, oldOfficeId) => {
  if (oldOfficeId !== null) {
    form.value.office_code_budget_id = null;
  }
  if (newOfficeId) {
    fetchOfficeCodeBudgets(newOfficeId);
  } else {
    officeCodeBudgets.value = [];
  }
});

watch(() => form.value.groups.map(g => g.group_id), (newIds, oldIds) => {
  newIds.forEach((id, i) => {
    if (oldIds[i] && id !== oldIds[i]) {
      form.value.groups[i].objects = [];
    }
  });
});

function close() {
  emit("update:modelValue", false);
  nextTick(() => {
    form.value = getDefaultFormState();
    errors.value = { office_code_id: false, office_code_budget_id: false, groups: [] };
    paoRequests.value = [];
    officeCodeBudgets.value = [];
  });
}

const canAddGroup = computed(() => {
  const existingGroupIds = form.value.groups.map(g => g.group_id).filter(Boolean);
  return groups.value.some(g => !existingGroupIds.includes(g.group_id));
});

function addGroup() {
  const existingGroupIds = form.value.groups.map(g => g.group_id).filter(Boolean);
  const firstAvailableGroup = groups.value.find(g => !existingGroupIds.includes(g.group_id));
  if (firstAvailableGroup) {
    form.value.groups.push({ group_id: firstAvailableGroup.group_id, objects: [], _expanded: true });
  }
}

function addAllGroups() {
  const existingGroupIds = form.value.groups.map(g => g.group_id).filter(Boolean);
  groups.value
    .filter(g => !existingGroupIds.includes(g.group_id))
    .forEach(g => form.value.groups.push({ group_id: g.group_id, objects: [], _expanded: true }));
}

function availableGroups(currentGroup) {
  const existingGroupIds = form.value.groups.map(g => g.group_id).filter(Boolean);
  return groups.value
    .filter(g => !existingGroupIds.includes(g.group_id) || g.group_id === currentGroup.group_id)
    .map(g => ({ title: g.group_name, value: g.group_id }));
}

function canAddObject(group) {
  if (!group.group_id) return false;
  const existingObjectIds = group.objects.map(o => o.object_expenditure_id).filter(Boolean);
  const availableObjects = (groups.value.find(g => g.group_id === group.group_id)?.object_of_expenditures || [])
    .filter(o => !existingObjectIds.includes(o.object_id));
  return availableObjects.length > 0;
}

function availableObjects(group, currentObject) {
  const existingObjectIds = group.objects.map(o => o.object_expenditure_id).filter(Boolean);
  return (groups.value.find(g => g.group_id === group.group_id)?.object_of_expenditures || [])
    .filter(o => !existingObjectIds.includes(o.object_id) || o.object_id === currentObject.object_expenditure_id)
    .map(o => ({ title: o.object_name, value: o.object_id }));
}

function addObject(group) {
  const existingObjectIds = group.objects.map(o => o.object_expenditure_id).filter(Boolean);
  const available = (groups.value.find(g => g.group_id === group.group_id)?.object_of_expenditures || [])
    .filter(o => !existingObjectIds.includes(o.object_id));
  if (available.length > 0) {
    group.objects.push({ object_expenditure_id: available[0].object_id, amount: null });
  }
}

function addAllObjects(group) {
  const existingObjectIds = group.objects.map(o => o.object_expenditure_id).filter(Boolean);
  const allObjects = (groups.value.find(g => g.group_id === group.group_id)?.object_of_expenditures || [])
    .filter(o => !existingObjectIds.includes(o.object_id));

  allObjects.forEach(o => group.objects.push({ object_expenditure_id: o.object_id, amount: null }));
}

function removeGroup(index) {
  form.value.groups.splice(index, 1);
}

function removeObject(group, index) {
  group.objects.splice(index, 1);
}

function groupTotal(group) {
  return group.objects.reduce((sum, obj) => sum + (Number(obj.amount) || 0), 0);
}

const grandTotal = computed(() => {
  return form.value.groups.reduce((sum, g) => sum + groupTotal(g), 0);
});

const selectedBudget = computed(() => {
  if (!form.value.office_code_budget_id || !officeCodeBudgets.value.length) {
    return null;
  }
  return officeCodeBudgets.value.find(
    b => Number(b.id) === Number(form.value.office_code_budget_id)
  );
});

function validate() {
  let isValid = true;
  const newErrors = { office_code_id: false, office_code_budget_id: false, groups: [] };

  if (!form.value.office_code_id) {
    newErrors.office_code_id = true;
    isValid = false;
  }
  if (!form.value.office_code_budget_id) {
    newErrors.office_code_budget_id = 'Budget Year is required';
    isValid = false;
  }

  const usedBudgetIds = new Set(paoRequests.value.map(req => Number(req.office_code_budget_id)));
  const selectedBudgetId = Number(form.value.office_code_budget_id);
  const isCurrentlyEditingThisBudget = selectedBudgetId === Number(props.data?.office_code_budget_id);

  if (usedBudgetIds.has(selectedBudgetId) && !isCurrentlyEditingThisBudget) {
    newErrors.office_code_budget_id = 'This budget year is already in use.';
    isValid = false;
  }
  
  // *** FIX: Use 'budget' property instead of 'budget_amount' ***
  if (isValid && selectedBudget.value && grandTotal.value > parseFloat(selectedBudget.value.budget)) {
    const formattedBudget = formatCurrency(selectedBudget.value.budget);
    newErrors.office_code_budget_id = `Total exceeds the allotted budget of ${formattedBudget}.`;
    isValid = false;
  }

  form.value.groups.forEach((group, gi) => {
    const groupError = { group_id: false, objects: [] };
    if (!group.group_id) {
      groupError.group_id = true;
      isValid = false;
    }

    group.objects.forEach((obj, oi) => {
      const objectError = { object_expenditure_id: false, amount: false };
      if (!obj.object_expenditure_id) {
        objectError.object_expenditure_id = true;
        isValid = false;
      }
      if (obj.amount === null || obj.amount === '') {
        objectError.amount = true;
        isValid = false;
      }
      if (!groupError.objects) groupError.objects = [];
      groupError.objects[oi] = objectError;
    });
    newErrors.groups[gi] = groupError;
  });

  errors.value = newErrors;
  return isValid;
}

async function save() {
  if (!validate()) return;

  try {
    isSaving.value = true;
    if (props.data?.request_id) {
      await api.put(`/pao-requests/${props.data.request_id}`, form.value);
    } else {
      await api.post("/pao-requests", form.value);
    }
    emit("saved");
    close();
  } catch (err) {
    console.error("Failed to save PAO request:", err);
  } finally {
    isSaving.value = false;
  }
}
</script>

<template>
  <VDialog
    v-model="props.modelValue"
    max-width="850"
    transition="dialog-bottom-transition"
    persistent
  >
    <VCard class="custom-modal-shadow rounded-lg pa-4">
      <VOverlay
        v-model="loadingReferenceData"
        contained
        persistent
        class="align-center justify-center"
      >
        <VProgressCircular indeterminate color="white" />
      </VOverlay>

      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <img
          :src="puzzlePiece"
          alt="Puzzle Piece"
          class="me-3"
          style="width: 28px; height: 28px;"
        />
        {{ props.data?.request_id ? 'Edit PAO Request' : 'Add PAO Request' }}
      </VCardTitle>

      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        {{ props.data?.request_id
          ? 'Update this PAO Request. Changes will reflect on the selected office and related groups & object expenditures.'
          : 'Fill in the details to create a new PAO Request. This will be applied to the selected office and its related groups & object expenditures.'
        }}
      </VCardSubtitle>

      <VCardText class="pa-4">
        <VRow>
          <VCol cols="12" sm="6">
            <div class="mb-5">
              <label class="text-sm text-grey-darken-2 d-block mb-1">
                Office Code
              </label>
              <small class="text-grey-darken-1 d-block mb-2">
                Choose the office this request belongs to.
              </small>
              <VSelect
                v-model="form.office_code_id"
                :items="officeCodeItems"
                variant="outlined"
                density="comfortable"
                placeholder="Select Office Code"
                class="small-select"
                :disabled="!!props.data?.request_id"
                :error="!!errors.office_code_id"
                :error-messages="errors.office_code_id ? 'Office Code is required' : ''"
                @update:modelValue="errors.office_code_id = false"
              />
            </div>
          </VCol>
          <VCol cols="12" sm="6">
            <div class="mb-5">
              <label class="text-sm text-grey-darken-2 d-block mb-1">
                Budget Year
              </label>
              <small class="text-grey-darken-1 d-block mb-2">
                Select the budget year for this appropriation.
              </small>
              <VSelect
                v-model="form.office_code_budget_id"
                :items="budgetYearItems"
                variant="outlined"
                density="comfortable"
                placeholder="Select Year"
                class="small-select"
                :disabled="!form.office_code_id || !!props.data?.request_id"
                :error="!!errors.office_code_budget_id"
                :error-messages="errors.office_code_budget_id || ''"
                @update:modelValue="errors.office_code_budget_id = false"
              />
              <div v-if="selectedBudget" class="mt-2 text-sm text-grey-darken-2">
                Allotted Budget: 
                <span class="font-weight-bold">
                  {{ formatCurrency(selectedBudget.budget) }}
                </span>
              </div>
            </div>
          </VCol>
        </VRow>

        <div class="mb-5">
          <div class="d-flex flex-wrap justify-space-between align-center mb-2 gap-2">
            <div>
              <label class="text-sm text-grey-darken-2 d-block mb-1">
                Groups & Object Expenditures
              </label>
              <small class="text-grey-darken-1">
                Add one or more groups and their related expenditures.
              </small>
            </div>
            <div class="d-flex flex-wrap gap-2 add-group-buttons">
              <VBtn
                variant="outlined"
                size="small"
                prepend-icon="bx-plus"
                :disabled="!canAddGroup"
                @click="addGroup"
              >
                Add Group
              </VBtn>
              <VBtn
                variant="outlined"
                size="small"
                prepend-icon="bx-list-plus"
                :disabled="!canAddGroup"
                @click="addAllGroups"
              >
                Add All Groups
              </VBtn>
            </div>
          </div>

          <div
            v-for="(group, gi) in form.groups"
            :key="gi"
            class="mb-4 group-card"
          >
            <div
              class="d-flex justify-space-between align-center pa-3 group-header"
              @click="group._expanded = !group._expanded"
            >
              <div class="font-weight-medium">
                {{ groups.find(g => g.group_id === group.group_id)?.group_name || 'Select Group' }}
              </div>

              <div class="d-flex align-center">
                <span v-if="!group._expanded" class="me-3 font-weight-medium text-grey-darken-2">
                  {{ formatCurrency(groupTotal(group)) }}
                </span>
                <VBtn icon variant="text" size="small">
                  <VIcon :icon="group._expanded ? 'bx-chevron-up' : 'bx-chevron-down'" />
                </VBtn>
              </div>
            </div>

            <VExpandTransition>
              <div v-show="group._expanded" class="pa-4 pt-0">
                <VSelect
                  v-model="group.group_id"
                  :items="availableGroups(group)"
                  variant="outlined"
                  density="compact"
                  placeholder="Select Group"
                  class="mb-3 mt-3 small-select"
                  :error="errors.groups[gi]?.group_id"
                  :error-messages="errors.groups[gi]?.group_id ? 'Group is required' : ''"
                  @update:modelValue="errors.groups[gi] && (errors.groups[gi].group_id = false)"
                />

                <div class="ms-4 mb-2">
                  <div v-for="(obj, oi) in group.objects" :key="oi" class="mb-3">
                    <VRow dense>
                      <VCol cols="12" sm="7">
                        <VSelect
                          v-model="obj.object_expenditure_id"
                          :items="availableObjects(group, obj)"
                          variant="outlined"
                          density="compact"
                          placeholder="Select Object Expenditure"
                          class="small-select"
                          :error="errors.groups[gi]?.objects[oi]?.object_expenditure_id"
                          :error-messages="errors.groups[gi]?.objects[oi]?.object_expenditure_id ? 'Object is required' : ''"
                          @update:modelValue="errors.groups[gi] && errors.groups[gi].objects[oi] && (errors.groups[gi].objects[oi].object_expenditure_id = false)"
                        />
                      </VCol>
                      <VCol cols="12" sm="4">
                        <VTextField
                          v-model="obj.amount"
                          type="number"
                          variant="outlined"
                          density="compact"
                          placeholder="Amount"
                          class="small-select"
                          :error="errors.groups[gi]?.objects[oi]?.amount"
                          :error-messages="errors.groups[gi]?.objects[oi]?.amount ? 'Amount is required' : ''"
                          @update:modelValue="errors.groups[gi] && errors.groups[gi].objects[oi] && (errors.groups[gi].objects[oi].amount = false)"
                        />
                      </VCol>
                      <VCol cols="12" sm="1" class="d-flex align-center">
                        <VBtn
                          icon="bx-trash"
                          color="error"
                          variant="text"
                          size="x-small"
                          @click="removeObject(group, oi)"
                        />
                      </VCol>
                    </VRow>
                  </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-2 ms-4 add-object-buttons">
                  <VBtn
                    variant="text"
                    size="small"
                    prepend-icon="bx-plus"
                    :disabled="!canAddObject(group)"
                    @click="addObject(group)"
                  >
                    Add Object
                  </VBtn>

                  <VBtn
                    variant="text"
                    size="small"
                    prepend-icon="bx-layer-plus"
                    :disabled="!canAddObject(group)"
                    @click="addAllObjects(group)"
                  >
                    Add All Objects
                  </VBtn>
                </div>

                <div class="mt-3 ms-4 d-flex justify-end font-weight-medium mb-2">
                  Total: {{ formatCurrency(groupTotal(group)) }}
                </div>

                <div class="mt-2 text-right">
                  <VBtn
                    variant="outlined"
                    size="small"
                    color="error"
                    class="btn-error"
                    @click="removeGroup(gi)"
                  >
                    Delete {{ groups.find(g => g.group_id === group.group_id)?.group_name || 'Group' }}
                  </VBtn>
                </div>
              </div>
            </VExpandTransition>
          </div>
        </div>

        <div class="d-flex justify-end align-center font-weight-bold mt-4 border-top pt-3">
          <VIcon
            v-if="selectedBudget && grandTotal > parseFloat(selectedBudget.budget)"
            icon="bx-error-circle"
            color="error"
            class="me-2"
          />
          <span 
            :class="{ 'text-error': selectedBudget && grandTotal > parseFloat(selectedBudget.budget) }"
          >
            Grand Total: {{ formatCurrency(grandTotal) }}
          </span>
        </div>
      </VCardText>

      <VCardActions class="d-flex justify-end flex-wrap gap-2 pa-4">
        <VBtn variant="text" color="grey-darken-1" prepend-icon = "bx-x" @click="close">Cancel</VBtn>
        <VBtn
          color="primary"
          prepend-icon="bx-send"
          :loading="isSaving"
          @click="save"
        >
          {{ props.data?.request_id ? 'Update Program Appropriation' : 'Add Program Appropriation' }}
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

.group-card {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: box-shadow 0.2s ease;
}
.group-card:hover {
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.group-header {
  cursor: pointer;
  border-bottom: 1px solid #eee;
  user-select: none;
}
.group-header:hover {
  background-color: #f9f9f9;
}

.small-select .v-field__input {
  font-size: 0.8rem !important;
  min-height: 32px !important;
  padding-top: 2px !important;
  padding-bottom: 2px !important;
}
.small-select .v-label {
  font-size: 0.75rem !important;
}
.small-select .v-list-item-title {
  font-size: 0.8rem !important;
}

.add-group-buttons,
.add-object-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

@media (max-width: 600px) {
  .group-card {
    padding: 1rem !important;
  }
  .add-group-buttons,
  .add-object-buttons {
    flex-direction: column;
    width: 100%;
  }
  .add-group-buttons .v-btn,
  .add-object-buttons .v-btn {
    width: 100%;
  }
  .v-card-actions {
    flex-direction: column;
    align-items: stretch !important;
  }
}
.btn-error:hover {
  background-color: rgb(var(--v-theme-error)) !important;
  color: white !important;
}
</style>