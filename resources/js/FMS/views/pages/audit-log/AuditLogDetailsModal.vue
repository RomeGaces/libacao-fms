<template>
  <VDialog
    v-model="isOpen"
    max-width="900"
    transition="dialog-bottom-transition"
  >
    <VCard v-if="auditLog" class="custom-modal-shadow rounded-lg pa-5">
      <!-- Title with History Icon -->
      <VCardTitle class="d-flex align-center text-h6 font-weight-medium mb-2">
        <img
          :src="auditLog3d"
          alt="Audit Logs"
          class="me-3"
          style="width: 28px; height: 28px;"
        />
        Audit Log Details
      </VCardTitle>

      <!-- Subtext -->
      <VCardSubtitle class="text-body-2 text-grey-darken-1 mb-5 description-text">
        View detailed information about this audit log entry, including what changed, who made the change, and when it occurred.
      </VCardSubtitle>

      <!-- Content -->
      <VCardText>
        <div class="d-flex flex-column gap-6">
          <!-- Basic Info Grid -->
          <VRow>
            <VCol cols="12" md="6">
              <div class="info-group">
                <div class="text-caption text-grey-darken-1 mb-1 font-weight-medium">Model Type</div>
                <VChip color="primary" variant="tonal" size="small" class="mt-1">
                  {{ auditLog.model_name }}
                </VChip>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="info-group">
                <div class="text-caption text-grey-darken-1 mb-1 font-weight-medium">Record ID</div>
                <div class="text-body-1 font-weight-medium mt-1">#{{ auditLog.auditable_id }}</div>
              </div>
            </VCol>
          </VRow>

          <VRow>
            <VCol cols="12" md="6">
              <div class="info-group">
                <div class="text-caption text-grey-darken-1 mb-1 font-weight-medium">User</div>
                <div class="text-body-1 font-weight-medium mt-2">
                  {{ auditLog.user?.name || 'Unknown' }}
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="info-group">
                <div class="text-caption text-grey-darken-1 mb-1 font-weight-medium">Date & Time</div>
                <div class="text-body-2 mt-2">{{ formatDateTime(auditLog.updated_at) }}</div>
              </div>
            </VCol>
          </VRow>

          <VDivider class="my-2" />

          <!-- Remarks Section -->
          <div class="info-group">
            <div class="text-caption text-grey-darken-1 mb-2 font-weight-medium">Remarks</div>
            <VCard variant="outlined" class="pa-3 bg-surface">
              <div class="text-body-2">{{ auditLog.remarks }}</div>
            </VCard>
          </div>

          <VDivider class="my-2" />

          <!-- Changes Section -->
          <div class="info-group">
            <div class="text-caption text-grey-darken-1 mb-3 font-weight-medium">Changes</div>
            <VRow>
              <!-- From Column -->
              <VCol cols="12" md="6">
                <VCard variant="outlined" class="pa-4 changes-card changes-from">
                  <div class="d-flex align-center mb-3">
                    <VIcon icon="bx-minus-circle" size="20" class="text-error me-2" />
                    <span class="text-caption font-weight-bold text-error">FROM (Before)</span>
                  </div>
                  <pre class="changes-content">{{ formatChanges(auditLog.changes?.from) }}</pre>
                </VCard>
              </VCol>

              <!-- To Column -->
              <VCol cols="12" md="6">
                <VCard variant="outlined" class="pa-4 changes-card changes-to">
                  <div class="d-flex align-center mb-3">
                    <VIcon icon="bx-plus-circle" size="20" class="text-success me-2" />
                    <span class="text-caption font-weight-bold text-success">TO (After)</span>
                  </div>
                  <pre class="changes-content">{{ formatChanges(auditLog.changes?.to) }}</pre>
                </VCard>
              </VCol>
            </VRow>
          </div>
        </div>
      </VCardText>

      <!-- Actions -->
      <VCardActions class="justify-end mt-6">
        <VBtn
          color="grey-darken-1"
          class="px-5"
          @click="close"
        >
          <VIcon icon="bx-x" class="me-1" />
          Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import auditLog3d from '@/../icons/audit_logs_3d.png';
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  auditLog: Object,
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(props.modelValue);

// Sync parent -> local
watch(
  () => props.modelValue,
  (val) => {
    isOpen.value = val;
  }
);

// Sync local -> parent
watch(isOpen, (val) => {
  emit('update:modelValue', val);
});

function close() {
  isOpen.value = false;
}

const formatDateTime = (datetime) => {
  if (!datetime) return '-';
  const date = new Date(datetime);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};

const formatChanges = (data) => {
  if (!data) return 'N/A';
  if (typeof data === 'string') return data;
  return JSON.stringify(data, null, 2);
};
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
}

.info-group {
  padding: 8px 0;
}

.changes-card {
  border-radius: 8px;
  transition: all 0.2s ease-in-out;
}

.changes-from {
  border-color: rgba(var(--v-theme-error), 0.3);
  background-color: rgba(var(--v-theme-error), 0.02);
}

.changes-to {
  border-color: rgba(var(--v-theme-success), 0.3);
  background-color: rgba(var(--v-theme-success), 0.02);
}

.changes-content {
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.8125rem;
  line-height: 1.5;
  white-space: pre-wrap;
  word-wrap: break-word;
  max-height: 280px;
  overflow-y: auto;
  margin: 0;
  padding: 12px;
  background-color: rgba(var(--v-theme-on-surface), 0.03);
  border-radius: 6px;
  color: rgba(var(--v-theme-on-surface), 0.87);
}

.changes-content::-webkit-scrollbar {
  width: 6px;
}

.changes-content::-webkit-scrollbar-track {
  background: rgba(var(--v-theme-on-surface), 0.05);
  border-radius: 3px;
}

.changes-content::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 3px;
}

.changes-content::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--v-theme-on-surface), 0.3);
}

.bg-surface {
  background-color: rgba(var(--v-theme-on-surface), 0.02);
}
</style>