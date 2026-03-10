<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import api from "@fms/utils/api";
import OfficeCodeBudgetModal from './OfficeCodeBudgetModal.vue'
import DeleteOfficeCodeBudget from './DeleteOfficeCodeBudget.vue'

// Reactive State
const loading = ref(false)
const annualBudgets = ref([]) // List of all available annual budgets (for the dropdown)
const officeBudgets = ref([]) // List of office budgets for the *selected* year

const selectedAnnualBudgetId = ref(null) // The ID of the selected annual budget
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedOfficeBudget = ref(null)

// Computed property to find the full selected annual budget object
const selectedAnnualBudget = computed(() => {
  return annualBudgets.value.find(ab => ab.id === selectedAnnualBudgetId.value) || null;
});

// Computed property for the total allocated budget for the selected year
const totalAllocated = computed(() => {
    if (!officeBudgets.value || officeBudgets.value.length === 0) return 0;
    return officeBudgets.value.reduce((sum, item) => sum + (Number(item.budget) || 0), 0);
});


// --- API Functions ---

async function fetchAnnualBudgets() {
  try {
    const response = await api.get('/annual-budgets');
    annualBudgets.value = response.data;
  } catch (error) {
    console.error('Failed to fetch annual budgets:', error);
  }
}

async function fetchOfficeBudgets(annualBudgetId) {
  if (!annualBudgetId) {
    officeBudgets.value = []; // Clear table if no year is selected
    return;
  }
  loading.value = true;
  try {
    const response = await api.get(`/office-code-budgets?annual_budget_id=${annualBudgetId}`);
    officeBudgets.value = response.data;
  } catch (error) {
    console.error(`Failed to fetch office budgets for year ID ${annualBudgetId}:`, error);
    officeBudgets.value = []; // Clear on error
  } finally {
    loading.value = false;
  }
}

// Watch for changes in the dropdown selection and fetch relevant data
watch(selectedAnnualBudgetId, (newId) => {
  fetchOfficeBudgets(newId);
});

// --- Event Handlers ---

function handleEdit(budget) {
  selectedOfficeBudget.value = budget ? { ...budget } : { office_code_id: null, budget: '' };
  showEditModal.value = true;
}

function handleDelete(budget) {
  selectedOfficeBudget.value = budget;
  showDeleteModal.value = true;
}

async function handleSave(budgetData, done) {
  try {
    // Add the current annual_budget_id to the payload before sending
    const payload = { ...budgetData, annual_budget_id: selectedAnnualBudgetId.value };

    if (payload.id) {
      // Update existing budget
      await api.put(`/office-code-budgets/${payload.id}`, payload);
    } else {
      // Create new budget
      await api.post('/office-code-budgets', payload);
    }
    await fetchOfficeBudgets(selectedAnnualBudgetId.value); // Refresh the table
    done(true);
  } catch (error) {
    console.error('Failed to save office budget:', error);
    done(false);
  }
}

async function confirmDelete(done) {
  if (!selectedOfficeBudget.value) return;
  try {
    await api.delete(`/office-code-budgets/${selectedOfficeBudget.value.id}`);
    await fetchOfficeBudgets(selectedAnnualBudgetId.value); // Refresh the table
    done(true);
  } catch (error) {
    console.error('Failed to delete office budget:', error);
    done(false);
  }
}

// --- Utility Functions ---
function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
}

function formatCurrency(value) {
    const number = Number(value) || 0;
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(number);
}


// Fetch initial data on component mount
onMounted(() => {
  fetchAnnualBudgets();
});
</script>

<template>
  <!-- Year Selector and Add Button -->
  <div class="d-flex flex-wrap gap-4 justify-space-between align-center mb-4">
    <VSelect
        v-model="selectedAnnualBudgetId"
        :items="annualBudgets"
        item-title="year"
        item-value="id"
        label="Select Budget Year"
        variant="outlined"
        density="comfortable"
        clearable
        hide-details
        style="max-width: 250px;"
    />
    <VBtn
        variant="outlined"
        color="primary"
        :disabled="!selectedAnnualBudgetId"
        @click="handleEdit(null)"
    >
        + Add Office Budget
    </VBtn>
  </div>

  <!-- Summary Cards -->
    <VRow v-if="selectedAnnualBudget" class="mb-4">
        <VCol cols="12" md="6">
            <VCard class="pa-4" variant="tonal">
                <div class="text-subtitle-2 text-grey-darken-1">Total Annual Budget ({{ selectedAnnualBudget.year }})</div>
                <div class="text-h5 font-weight-bold">{{ formatCurrency(selectedAnnualBudget.annual_budget) }}</div>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard class="pa-4" variant="tonal" :color="totalAllocated > selectedAnnualBudget.annual_budget ? 'error' : 'success'">
                <div class="text-subtitle-2 text-grey-darken-1">Total Allocated to Offices</div>
                <div class="text-h5 font-weight-bold">{{ formatCurrency(totalAllocated) }}</div>
            </VCard>
        </VCol>
    </VRow>

  <!-- Table -->
  <div class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-x-auto">
    <VTable class="bg-grey-lighten-5 text-body-2 min-w-full" style="border-collapse: separate; border-spacing: 0">
      <thead>
        <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
          <th class="text-left px-6 py-3">Office Code</th>
          <th class="text-left px-6 py-3">Description</th>
          <th class="text-left px-6 py-3">Budget</th>
          <th class="text-left px-6 py-3">Created At</th>
          <th class="text-left px-6 py-3">Updated At</th>
          <th class="text-center px-6 py-3">Actions</th>
        </tr>
      </thead>

      <tbody v-if="!loading">
        <tr v-if="!selectedAnnualBudgetId" >
             <td colspan="6" class="text-center py-10 text-grey-darken-1">
                Please select a budget year to view office allocations.
            </td>
        </tr>
        <tr
          v-else-if="officeBudgets.length > 0"
          v-for="(budget, index) in officeBudgets"
          :key="budget.id"
          :class="['hover-row', index % 2 === 0 ? 'bg-grey-lighten-5' : '']"
          @click="handleEdit(budget)"
        >
          <td class="px-6 py-3">{{ budget.office_code?.office_code }}</td>
          <td class="px-6 py-3">{{ budget.office_code?.description }}</td>
          <td class="px-6 py-3">{{ formatCurrency(budget.budget) }}</td>
          <td class="px-6 py-3">{{ formatDate(budget.created_at) }}</td>
          <td class="px-6 py-3">{{ formatDate(budget.updated_at) }}</td>
          <td class="px-6 py-3 text-center whitespace-nowrap">
            <VBtn variant="text" color="error" size="small" @click.stop="handleDelete(budget)">
              <VIcon icon="bx-trash" />
            </VBtn>
          </td>
        </tr>
        <tr v-if="selectedAnnualBudgetId && officeBudgets.length === 0">
          <td colspan="6" class="text-center py-6 text-grey-darken-2">
            No office budgets have been allocated for this year.
          </td>
        </tr>
      </tbody>

      <tbody v-else>
        <tr>
          <td colspan="6" class="text-center py-10">
            <VProgressCircular indeterminate color="primary" size="32" width="4" />
            <div class="mt-2 text-grey-darken-2">Loading budgets...</div>
          </td>
        </tr>
      </tbody>
    </VTable>
  </div>

  <!-- Modals -->
  <OfficeCodeBudgetModal
    v-model="showEditModal"
    :budget="selectedOfficeBudget"
    :annual-budget="selectedAnnualBudget"
    :existing-office-budgets="officeBudgets"
    @save="handleSave"
  />
  <DeleteOfficeCodeBudget
    v-model="showDeleteModal"
    :budget="selectedOfficeBudget"
    @confirm="confirmDelete"
  />
</template>

<style scoped>
.hover-row {
  transition: background-color 0.2s ease;
  cursor: pointer;
}
.hover-row:hover {
  background-color: #f0f4f8;
}
.gap-4 {
    gap: 1rem;
}
</style>

