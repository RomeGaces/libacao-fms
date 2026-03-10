<script setup>
import { onMounted, ref } from 'vue'
import api from "@fms/utils/api"; // This is your pre-configured API utility
import AnnualBudgetModal from './AnnualBudgetModal.vue'
import DeleteAnnualBudget from './DeleteAnnualBudget.vue'

const emit = defineEmits(['budgetSaved'])

// Reactive state
const budgets = ref([])
const loading = ref(true)
const selectedBudget = ref(null)
const showEditModal = ref(false)
const showDeleteModal = ref(false)

// Holds the sum of allocated budgets for the item being edited
const totalAllocatedForEdit = ref(0);

/**
 * Fetches annual budget data from the API.
 */
async function fetchAnnualBudgets() {
  try {
    loading.value = true
    const response = await api.get(`/annual-budgets`)
    if (Array.isArray(response.data)) {
      budgets.value = response.data
    } else {
      console.error("API response is not an array:", response.data);
      budgets.value = [];
    }
  } catch (error) {
    console.error('Failed to fetch annual budgets:', error)
    budgets.value = [];
  } finally {
    loading.value = false
  }
}

/**
 * Formats an ISO date string into "Month Day, Year".
 */
function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Handles opening the modal for editing, first fetching the total allocated amount.
 * For adding, it opens the modal directly.
 */
async function handleEdit(budget) {
  if (budget && budget.id) {
    // For editing, first get the allocated total
    try {
        const response = await api.get(`/office-code-budgets?annual_budget_id=${budget.id}`);
        const allocatedBudgets = response.data || [];
        totalAllocatedForEdit.value = allocatedBudgets.reduce((sum, item) => sum + (Number(item.budget) || 0), 0);
    } catch (error) {
        console.error("Failed to fetch allocated budgets:", error);
        totalAllocatedForEdit.value = 0; // Default to 0 on error
    }
    selectedBudget.value = { ...budget };
  } else {
    // For adding, no allocation exists yet
    totalAllocatedForEdit.value = 0;
    selectedBudget.value = { year: '', annual_budget: '' };
  }
  showEditModal.value = true;
}


/**
 * Handles opening the delete confirmation modal.
 */
function handleDelete(budget) {
  selectedBudget.value = budget
  showDeleteModal.value = true
}

/**
 * Saves a new or updated budget and emits an event.
 */
async function handleSave(budgetData, done) {
  try {
    if (budgetData.id) {
      await api.put(`/annual-budgets/${budgetData.id}`, budgetData)
    } else {
      await api.post('/annual-budgets', budgetData)
    }
    await fetchAnnualBudgets()
    emit('budgetSaved')
    done(true)
  } catch (error) {
    console.error('Failed to save annual budget:', error)
    done(false)
  }
}

/**
 * Confirms and executes the deletion of a budget.
 */
async function confirmDelete(done) {
  if (!selectedBudget.value) return
  try {
    await api.delete(`/annual-budgets/${selectedBudget.value.id}`)
    await fetchAnnualBudgets()
    emit('budgetSaved'); // Also refresh on delete
    done(true)
  } catch (error) {
    console.error('Failed to delete annual budget:', error)
    done(false)
  }
}

// Fetch initial data when the component is mounted
onMounted(() => {
  fetchAnnualBudgets()
})
</script>

<template>
  <!-- Add button -->
  <div class="d-flex justify-end mb-4">
    <VBtn variant="outlined" color="primary" @click="handleEdit(null)">+ Add Annual Budget</VBtn>
  </div>

  <!-- Table -->
  <div class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-x-auto">
    <VTable class="bg-grey-lighten-5 text-body-2 min-w-full" style="border-collapse: separate; border-spacing: 0">
      <thead>
        <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
          <th class="text-left px-6 py-3">Budget Year</th>
          <th class="text-left px-6 py-3">Amount</th>
          <th class="text-left px-6 py-3">Created At</th>
          <th class="text-left px-6 py-3">Updated At</th>
          <th class="text-center px-6 py-3">Actions</th>
        </tr>
      </thead>

      <tbody v-if="!loading">
        <tr
          v-for="(budget, index) in budgets"
          :key="budget.id"
          :class="['hover-row', index % 2 === 0 ? 'bg-grey-lighten-5' : '']"
          @click="handleEdit(budget)"
        >
          <td class="px-6 py-3">{{ budget.year }}</td>
          <td class="px-6 py-3">₱ {{ new Intl.NumberFormat().format(budget.annual_budget) }}</td>
          <td class="px-6 py-3">{{ formatDate(budget.created_at) }}</td>
          <td class="px-6 py-3">{{ formatDate(budget.updated_at) }}</td>
          <td class="px-6 py-3 text-center whitespace-nowrap">
            <VBtn variant="text" color="error" size="small" @click.stop="handleDelete(budget)">
              <VIcon icon="bx-trash" />
            </VBtn>
          </td>
        </tr>
        <tr v-if="budgets.length === 0">
          <td colspan="5" class="text-center py-6 text-grey-darken-2">
            No annual budgets found.
          </td>
        </tr>
      </tbody>

      <tbody v-else>
        <tr>
          <td colspan="5" class="text-center py-10">
            <VProgressCircular indeterminate color="primary" size="32" width="4" />
            <div class="mt-2 text-grey-darken-2">Loading annual budgets...</div>
          </td>
        </tr>
      </tbody>
    </VTable>
  </div>

  <!-- Modals -->
  <AnnualBudgetModal
    v-model="showEditModal"
    :budget="selectedBudget"
    :total-allocated="totalAllocatedForEdit"
    @save="handleSave"
  />
  <DeleteAnnualBudget v-model="showDeleteModal" :budget="selectedBudget" @confirm="confirmDelete" />
</template>

<style scoped>
.hover-row {
  transition: background-color 0.2s ease;
  cursor: pointer;
}
.hover-row:hover {
  background-color: #f0f4f8;
}
</style>

