<script setup>
import ObrList from '@fms/views/pages/for-approval/ForApproval.vue'
// Icon alias
import okHand from '@/../icons/ok_hand_3d_default.png';
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <img
                :src="okHand"
                alt="For Approval"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">For Approval</span>
            </div>
          </div>
        </template>

        <VCardText class="text-body-2 text-grey-darken-2">
          This module provides a complete overview of all Obligation Requests & Status (ORS) forms. Use this page to formally reserve funds from the budget for specific expenditures. You can search for existing requests, create new obligations, and manage all entries to ensure effective budgetary control.
        </VCardText>

        <VCardText class="pt-0">
          <ObrList />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>