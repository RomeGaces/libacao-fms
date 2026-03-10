<script setup>
import PaperTrailList from '@fms/views/pages/paper-trail-management/PaperTrailSet.vue' 
import bookmarkTabs from '@/../icons/bookmark_tabs_3d.png';
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <img
                :src="bookmarkTabs"
                alt="Paper Trail Icon"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">Paper Trail Management</span>
            </div>
          </div>
        </template>

        <VCardText class="text-body-2 text-grey-darken-2">
          This module allows for the creation and management of routing workflows for documents. Use this page to define multi-step approval processes for different office groups, ensuring that documents follow a predefined path for review and approval across various departments.
        </VCardText>

        <VCardText class="pt-0">
          <PaperTrailList />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
