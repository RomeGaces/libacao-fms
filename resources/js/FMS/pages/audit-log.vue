<script setup>
import AuditLogList from '@fms/views/pages/audit-log/AuditLogList.vue' 
import auditLog from '@/../icons/audit_logs_3d.png';
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <img
                :src="auditLog"
                alt="Audit Log Icon"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">Audit Log</span>
            </div>
          </div>
        </template>

        <VCardText class="text-body-2 text-grey-darken-2">
          This module provides a comprehensive record of all changes made within the system. View detailed logs of create, update, and delete operations across all modules, including who made the changes and when they occurred, ensuring full transparency and accountability.
        </VCardText>

        <VCardText class="pt-0">
          <AuditLogList />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>