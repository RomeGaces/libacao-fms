<script setup>
import { ref } from 'vue'
import AnnualBudget from '@fms/views/pages/annual-budget/AnnualBudget.vue'
import OfficeCodeBudget from '@fms/views/pages/annual-budget/OfficeCodeBudget.vue'
import moneyBag from '@/../icons/money_bag_3d.png'
import officeBuilding from '@/../icons/office_building_3d.png'

// A reactive key to force re-rendering of the OfficeCodeBudget component
const officeBudgetRefreshKey = ref(0);

/**
 * Increments the key, which forces the child component with this key to re-mount.
 */
function refreshOfficeBudgets() {
  officeBudgetRefreshKey.value++;
}
</script>

<template>
  <VRow>
    <!-- Annual Budget Card -->
    <VCol cols="12">
      <VCard>
        <template #title>
          <div class="d-flex align-center">
            <img
              :src="moneyBag"
              alt="Annual Budget Icon"
              class="me-3"
              style="width: 28px; height: 28px;"
            />
            <span class="text-h6">Annual Budget</span>
          </div>
        </template>

        <VCardText class="text-body-2 text-grey-darken-2">
          Manage the overall annual budget for the Province. This master budget serves as the primary source of funds for all office allocations.
        </VCardText>

        <VCardText class="pt-0">
          <!-- The AnnualBudget component emits 'budgetSaved' on success -->
          <AnnualBudget @budgetSaved="refreshOfficeBudgets" />
        </VCardText>
      </VCard>
    </VCol>

    <!-- Office Code Budget Card -->
    <VCol cols="12">
      <VCard>
        <template #title>
          <div class="d-flex align-center">
             <img
              :src="officeBuilding"
              alt="Office Budget Icon"
              class="me-3"
              style="width: 28px; height: 28px;"
            />
            <span class="text-h6">Office Code Budget Allocation</span>
          </div>
        </template>

        <VCardText class="text-body-2 text-grey-darken-2">
          Allocate funds from the master annual budget to specific offices. Select a budget year to view, add, or manage allocations.
        </VCardText>

        <VCardText class="pt-0">
          <!-- Binding the key to our reactive variable -->
          <OfficeCodeBudget :key="officeBudgetRefreshKey" />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

