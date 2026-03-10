<script setup>
import ArchivedObrList from '@fms/views/pages/obr/ArchivedObrList.vue'
// Icon alias
import archiveBox from '@/../icons/package_3d.png'
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <img
                :src="archiveBox"
                alt="Archive Icon"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">Archived OBR's</span>
            </div>
          </div>
        </template>

        <VCardText class="text-body-2 text-grey-darken-2">
          This module displays all archived Obligation Budget Requests (OBRs) for your office. Archived OBRs are permanently removed from active workflow but retained for historical reference. You can view details and permanently delete archived requests when necessary.
        </VCardText>

        <VCardText class="pt-0">
          <ArchivedObrList />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>