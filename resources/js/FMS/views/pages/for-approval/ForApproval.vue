<script setup>
import api from "@fms/utils/api";
import { onMounted, ref, computed, watch } from "vue";
import BulkProceedModal from './BulkProceedModal.vue';
import BulkReturnModal from './BulkReturnModal.vue';
import BulkRejectModal from './BulkRejectModal.vue';
import BulkArchiveModal from './BulkArchiveModal.vue';
import ViewObrModal from './ViewObrModal.vue';

const obrs = ref([]);
const loading = ref(true);

const sets = ref([]);
const selectedSetId = ref(null);
const officeId = ref(null);
const employeeId = ref(null); // Add employee ID
const tabs = ref([]);
const activeTabId = ref(null);
const tabsLoading = ref(false);
const tabCounts = ref({});

const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showProcessModal = ref(false);
const showBulkProceedModal = ref(false);
const showBulkReturnModal = ref(false);
const showBulkRejectModal = ref(false);
const showBulkArchiveModal = ref(false);
const showViewModal = ref(false);
const selectedObr = ref(null);
const selectedObrIds = ref([]);
const selectAll = ref(false);

// Toast notification state
const toast = ref({
  show: false,
  message: '',
  color: 'success',
  icon: ''
});

const currentPage = ref(1);
const lastPage = ref(1);
const totalItems = ref(0);

// --- Fetch Sets ---
const fetchSets = async () => {
  try {
    const response = await api.get("/sets");
    sets.value = response.data;
    if (sets.value.length > 0) selectedSetId.value = sets.value[0].id;
  } catch (error) {
    console.error("Failed to fetch sets:", error);
  }
};

// --- Fetch Employee ID and Office ID from User Info ---
const fetchUserInfo = async () => {
  try {
    const userInfoStr = localStorage.getItem("user_info");
    if (!userInfoStr) {
      console.error("User info not found in localStorage.");
      return;
    }

    const userInfo = JSON.parse(userInfoStr);
    
    // Get employee ID from user
    employeeId.value = userInfo?.employee_id;
    
    if (!employeeId.value) {
      console.error("Employee ID not found in user info.");
    }

    // Get office ID from department name
    const description = userInfo?.department_details?.name;

    if (!description) {
      console.error("Department name not found in user info.");
      return;
    }

    const response = await api.get(
      `/office-codes/find-by-description/${encodeURIComponent(description)}`
    );
    
    if (response.data && response.data.id) {
      officeId.value = response.data.id;
    } else {
      console.error("Office ID not found for description:", description);
    }
  } catch (error) {
    console.error("Failed to fetch user info:", error);
  }
};

// --- Fetch Tabs with Access Control ---
const fetchTabs = async (setId) => {
  if (!setId || !officeId.value || !employeeId.value) {
    tabs.value = [];
    activeTabId.value = null;
    tabCounts.value = {};
    return;
  }

  tabsLoading.value = true;
  try {
    // Fetch accessible steps for the current employee
    const response = await api.get('/department-employees/accessible-steps', {
      params: {
        employee_id: employeeId.value,
        office_id: officeId.value,
        set_id: setId
      }
    });
    
    tabs.value = response.data;
    activeTabId.value = tabs.value.length > 0 ? tabs.value[0].id : null;
    
    // Fetch counts for badges
    await fetchTabCounts(setId);
  } catch (error) {
    console.error("Failed to fetch tabs:", error);
    tabs.value = [];
    activeTabId.value = null;
    tabCounts.value = {};
  } finally {
    tabsLoading.value = false;
  }
};

// --- Fetch Tab Counts ---
const fetchTabCounts = async (setId) => {
  if (!officeId.value || !setId) return;
  
  try {
    const response = await api.get('/obr-requests/counts-by-internal-step', {
      params: {
        office_id: officeId.value,
        set_id: setId
      }
    });
    tabCounts.value = response.data;
  } catch (error) {
    console.error("Failed to fetch tab counts:", error);
    tabCounts.value = {};
  }
};

// --- Fetch OBRs ---
const fetchObrs = async (page = 1) => {
  if (!activeTabId.value) {
    obrs.value = [];
    totalItems.value = 0;
    lastPage.value = 1;
    currentPage.value = 1;
    loading.value = false;
    return;
  }

  loading.value = true;
  try {
    const response = await api.get(
      `/obr-requests?page=${page}&internal_step_id=${activeTabId.value}`
    );
    obrs.value = response.data.data;
    currentPage.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    totalItems.value = response.data.total;
  } catch (error) {
    console.error("Failed to fetch OBRs:", error);
    obrs.value = [];
    totalItems.value = 0;
  } finally {
    loading.value = false;
  }
};

// --- Watchers ---
watch(selectedSetId, (newSetId) => {
  if (employeeId.value && officeId.value) {
    fetchTabs(newSetId);
  }
});

watch([officeId, employeeId], ([newOfficeId, newEmployeeId]) => {
  if (newOfficeId && newEmployeeId && selectedSetId.value) {
    fetchTabs(selectedSetId.value);
  }
});

watch(activeTabId, (newActiveTabId) => {
  if (newActiveTabId) {
    fetchObrs(1);
    selectedObrIds.value = [];
    selectAll.value = false;
  } else {
    obrs.value = [];
    totalItems.value = 0;
    currentPage.value = 1;
    lastPage.value = 1;
  }
});

const activeTabTitle = computed(() => {
  const found = tabs.value.find((t) => t.id === activeTabId.value);
  return found ? found.approval_title : null;
});

const nextStageName = computed(() => {
  const currentIndex = tabs.value.findIndex(t => t.id === activeTabId.value);
  if (currentIndex >= 0 && currentIndex < tabs.value.length - 1) {
    return tabs.value[currentIndex + 1].approval_title;
  }
  return '';
});

const previousStageName = computed(() => {
  const currentIndex = tabs.value.findIndex(t => t.id === activeTabId.value);
  if (currentIndex > 0) {
    return tabs.value[currentIndex - 1].approval_title;
  }
  return '';
});

const emptyStateMessage = computed(() => {
  if (!selectedSetId.value) return "Please select a set to begin.";
  if (!officeId.value || !employeeId.value) return "Loading user configuration...";
  if (tabsLoading.value) return "Loading accessible steps...";
  if (!activeTabId.value) return "No accessible steps found for this set.";
  return `No OBRs found for "${activeTabTitle.value}".`;
});

// Check if current tab is first internal step (for Return button)
const isFirstStage = computed(() => {
  if (!tabs.value.length || !activeTabId.value) return false;
  return tabs.value[0].id === activeTabId.value;
});

// Check if current tab is last internal step
const isLastStage = computed(() => {
  if (!tabs.value.length || !activeTabId.value) return false;
  return tabs.value[tabs.value.length - 1].id === activeTabId.value;
});

// Check if there's a previous office using step_no (for Reject button)
const hasPreviousOffice = computed(() => {
  if (!tabs.value.length || !activeTabId.value) {
    return false;
  }
  
  const currentTab = tabs.value.find(t => t.id === activeTabId.value);
  
  if (!currentTab || !currentTab.step) {
    return false;
  }
  
  const stepNo = currentTab.step.step_no;
  return stepNo > 1;
});

const selectedObrs = computed(() => {
  return obrs.value.filter(obr => selectedObrIds.value.includes(obr.id));
});

const hasSelectedItems = computed(() => selectedObrIds.value.length > 0);

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedObrIds.value = obrs.value.map(obr => obr.id);
  } else {
    selectedObrIds.value = [];
  }
};

const toggleSelectObr = (obrId) => {
  const index = selectedObrIds.value.indexOf(obrId);
  if (index > -1) {
    selectedObrIds.value.splice(index, 1);
  } else {
    selectedObrIds.value.push(obrId);
  }
  selectAll.value = selectedObrIds.value.length === obrs.value.length;
};

const formatDate = (dateStr) =>
  new Date(dateStr).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });

const formatCurrency = (v) =>
  new Intl.NumberFormat("en-PH", { style: "currency", currency: "PHP" }).format(
    v
  );

const calculateTotal = (obrObjects) =>
  obrObjects?.reduce((s, i) => s + parseFloat(i.amount), 0) || 0;

const getStatusColor = (t) =>
  !t ? "grey" : t.toLowerCase() === "draft" ? "grey" : "success";

const handleEdit = (obr) => {
  selectedObr.value = obr ? JSON.parse(JSON.stringify(obr)) : null;
  showViewModal.value = true;
};

const handleBulkProceed = () => {
  showBulkProceedModal.value = true;
};

const handleBulkReturn = () => {
  showBulkReturnModal.value = true;
};

const handleBulkReject = () => {
  showBulkRejectModal.value = true;
};

const handleBulkArchive = () => {
  showBulkArchiveModal.value = true;
};

const confirmBulkProceed = async (done) => {
  try {
    const promises = selectedObrIds.value.map(obrId => 
      api.post(`/obr-requests/${obrId}/process`)
    );
    await Promise.all(promises);
    
    showToast(`Successfully proceeded ${selectedObrIds.value.length} OBR(s)`, 'success');
    
    selectedObrIds.value = [];
    selectAll.value = false;
    await fetchObrs(currentPage.value);
    await fetchTabCounts(selectedSetId.value);
    done(true);
  } catch (error) {
    console.error("Failed to process OBRs:", error);
    showToast('Failed to process some OBRs', 'error');
    done(false);
  }
};

const confirmBulkReturn = async (done) => {
  try {
    const promises = selectedObrIds.value.map(obrId => 
      api.post(`/obr-requests/${obrId}/return`)
    );
    await Promise.all(promises);
    
    showToast(`Successfully returned ${selectedObrIds.value.length} OBR(s)`, 'success');
    
    selectedObrIds.value = [];
    selectAll.value = false;
    await fetchObrs(currentPage.value);
    await fetchTabCounts(selectedSetId.value);
    done(true);
  } catch (error) {
    console.error("Failed to return OBRs:", error);
    showToast('Failed to return some OBRs', 'error');
    done(false);
  }
};

const confirmBulkReject = async (rejectionDetails, done) => {
  try {
    const promises = selectedObrIds.value.map(obrId => 
      api.post(`/obr-requests/${obrId}/reject`, {
        rejection_details: rejectionDetails
      })
    );
    await Promise.all(promises);
    
    showToast(`Successfully rejected ${selectedObrIds.value.length} OBR(s)`, 'success');
    
    selectedObrIds.value = [];
    selectAll.value = false;
    await fetchObrs(currentPage.value);
    await fetchTabCounts(selectedSetId.value);
    done(true);
  } catch (error) {
    console.error("Failed to reject OBRs:", error);
    showToast('Failed to reject some OBRs', 'error');
    done(false);
  }
};

const confirmBulkArchive = async (archiveReason, done) => {
  try {
    const promises = selectedObrIds.value.map(obrId => 
      api.post(`/obr-requests/${obrId}/archive`, {
        archive_reason: archiveReason
      })
    );
    await Promise.all(promises);
    
    showToast(`Successfully archived ${selectedObrIds.value.length} OBR(s)`, 'success');
    
    selectedObrIds.value = [];
    selectAll.value = false;
    await fetchObrs(currentPage.value);
    await fetchTabCounts(selectedSetId.value);
    done(true);
  } catch (error) {
    console.error("Failed to archive OBRs:", error);
    showToast('Failed to archive some OBRs', 'error');
    done(false);
  }
};

const showToast = (message, type = 'success') => {
  const config = {
    success: { color: 'success', icon: 'bx-check-circle' },
    error: { color: 'error', icon: 'bx-error-circle' },
    warning: { color: 'warning', icon: 'bx-error' },
    info: { color: 'info', icon: 'bx-info-circle' }
  };
  
  const { color, icon } = config[type] || config.success;
  
  toast.value = {
    show: true,
    message,
    color,
    icon
  };
};

const goToPage = (page) => {
  if (page < 1 || page > lastPage.value) return;
  fetchObrs(page);
};

onMounted(() => {
  fetchSets();
  fetchUserInfo();
});
</script>

<template>
  <VRow>
    <VCol cols="12" md="6" lg="4">
      <VSelect
        v-model="selectedSetId"
        :items="sets"
        item-title="set_no"
        item-value="id"
        label="Select Workflow Set"
        density="compact"
        variant="outlined"
        hide-details
        class="select-workflow-set"
      />
    </VCol>
  </VRow>

  <!-- Tabs -->
  <div v-if="!tabsLoading && tabs.length > 0" class="tabs-wrapper mt-6 mb-2">
    <div class="tabs-bar" style="padding-top: 12px;">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        :class="['hub-tab', { 'hub-tab--active': activeTabId === tab.id, 'hub-tab--disabled': !tabCounts[tab.id] }]"
        :disabled="!tabCounts[tab.id]"
        @click="activeTabId = tab.id"
      >
        {{ tab.approval_title }}
        <span v-if="tabCounts[tab.id]" class="tab-badge">{{ tabCounts[tab.id] }}</span>
      </button>
    </div>
  </div>

  <div v-if="tabsLoading" class="mt-4">
    <VProgressLinear indeterminate color="primary" height="6" rounded />
  </div>

  <!-- No Access Message -->
  <div v-if="!tabsLoading && tabs.length === 0 && selectedSetId && officeId && employeeId" class="mt-6">
    <VCard class="text-center py-10">
      <VCardText>
        <VIcon icon="bx-lock" size="48" color="warning" class="mb-3" />
        <p class="text-h6 text-grey-darken-2 mb-2">No Access to Steps</p>
        <p class="text-body-2 text-grey-darken-1">You don't have access to any internal steps in this workflow set.</p>
        <p class="text-body-2 text-grey-darken-1">Please contact your administrator to grant access.</p>
      </VCardText>
    </VCard>
  </div>

  <div class="my-4"></div>

  <!-- Bulk Action Buttons -->
  <div v-if="obrs.length > 0" class="d-flex justify-space-between align-center mb-4 flex-wrap gap-3">
    <!-- Left side buttons -->
    <div class="d-flex gap-3 flex-wrap">
      <button
        class="action-btn action-btn-proceed"
        :disabled="!hasSelectedItems"
        @click="handleBulkProceed"
      >
        <VIcon icon="bx-chevron-right-circle" size="20" />
        <span>Proceed</span>
        <span v-if="selectedObrIds.length > 0" class="btn-badge">{{ selectedObrIds.length }}</span>
      </button>
      
      <button
        class="action-btn action-btn-return"
        :disabled="!hasSelectedItems || isFirstStage"
        @click="handleBulkReturn"
      >
        <VIcon icon="bx-chevron-left-circle" size="20" />
        <span>Return</span>
        <span v-if="selectedObrIds.length > 0" class="btn-badge">{{ selectedObrIds.length }}</span>
      </button>
    </div>

    <!-- Right side buttons -->
    <div class="d-flex gap-3 flex-wrap">
      <button
        class="action-btn action-btn-reject"
        :disabled="!hasSelectedItems || !hasPreviousOffice"
        @click="handleBulkReject"
      >
        <VIcon icon="bx-x-circle" size="20" />
        <span>Reject</span>
        <span v-if="selectedObrIds.length > 0" class="btn-badge">{{ selectedObrIds.length }}</span>
      </button>

      <button
        class="action-btn action-btn-archive"
        :disabled="!hasSelectedItems"
        @click="handleBulkArchive"
      >
        <VIcon icon="bx-archive" size="20" />
        <span>Archive</span>
        <span v-if="selectedObrIds.length > 0" class="btn-badge">{{ selectedObrIds.length }}</span>
      </button>
    </div>
  </div>

  <div v-if="tabs.length > 0" class="rounded-lg shadow-sm border border-grey-lighten-3 overflow-x-auto">
    <VTable
      class="bg-grey-lighten-5 text-body-2 min-w-full"
      style="border-collapse: separate; border-spacing: 0"
    >
      <thead>
        <tr class="bg-grey-lighten-3 text-grey-darken-4 font-weight-medium">
          <th class="text-center px-4 py-3" style="width: 50px;">
            <input
              type="checkbox"
              v-model="selectAll"
              @change="toggleSelectAll"
              class="custom-checkbox"
            />
          </th>
          <th class="text-left px-6 py-3">Current Status</th>
          <th class="text-left px-6 py-3">OBR Number</th>
          <th class="text-left px-6 py-3">Office</th>
          <th class="text-left px-6 py-3">Office Address</th>
          <th class="text-left px-6 py-3">Total Amount</th>
          <th class="text-left px-6 py-3">Created At</th>
        </tr>
      </thead>

      <tbody v-if="!loading">
        <tr
          v-for="(obr, i) in obrs"
          :key="obr.id"
          :class="[
            'hover-row',
            i % 2 === 0 ? 'bg-grey-lighten-5' : '',
            obr.rejections && obr.rejections.length > 0 ? 'has-rejection' : ''
          ]"
          @click="handleEdit(obr)"
        >
          <td class="text-center px-4 py-3" @click.stop>
            <input
              type="checkbox"
              :checked="selectedObrIds.includes(obr.id)"
              @change="toggleSelectObr(obr.id)"
              class="custom-checkbox"
            />
          </td>
          <td class="px-6 py-3">
            <div class="d-flex align-center gap-2">
              <VChip
                v-if="obr.latest_status?.internal_step"
                :color="getStatusColor(obr.latest_status.internal_step.approval_title)"
                variant="tonal"
                size="small"
                label
              >
                {{ obr.latest_status.internal_step.approval_title }}
              </VChip>
              <span v-else class="text-grey-darken-1">N/A</span>
              
              <!-- Rejection indicator icon -->
              <VTooltip v-if="obr.rejections && obr.rejections.length > 0" location="top">
                <template v-slot:activator="{ props }">
                  <VIcon
                    v-bind="props"
                    icon="bx-error-circle"
                    color="error"
                    size="20"
                    class="rejection-icon"
                  />
                </template>
                <span>{{ obr.rejections.length }} rejection(s) - Click to view details</span>
              </VTooltip>
            </div>
          </td>
          <td class="px-6 py-3">{{ obr.obr_no }}</td>
          <td class="px-6 py-3">{{ obr.pao_request?.office_code?.description || 'N/A' }}</td>
          <td class="px-6 py-3">{{ obr.office_address }}</td>
          <td class="px-6 py-3 font-weight-medium">
            {{ formatCurrency(calculateTotal(obr.obr_objects)) }}
          </td>
          <td class="px-6 py-3">{{ formatDate(obr.created_at) }}</td>
        </tr>

        <tr v-if="obrs.length === 0">
          <td colspan="7" class="text-center py-6 text-grey-darken-2">
            {{ emptyStateMessage }}
          </td>
        </tr>
      </tbody>

      <tbody v-else>
        <tr>
          <td colspan="7" class="text-center py-10">
            <VProgressCircular indeterminate color="primary" size="32" width="4" />
            <div class="mt-2 text-grey-darken-2">Loading OBRs...</div>
          </td>
        </tr>
      </tbody>
    </VTable>
  </div>

  <!-- Pagination -->
  <div v-if="!loading && obrs.length > 0" class="d-flex flex-wrap justify-between items-center mt-4 gap-4 pagination-wrapper">
    <div class="text-grey-darken-2 text-sm">
      Showing {{ obrs.length }} of {{ totalItems }} results
    </div>

    <div class="d-flex gap-2 items-center ms-auto flex-wrap justify-center">
      <VBtn
        class="pagination-btn"
        size="small"
        elevation="0"
        :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)"
      >
        ‹ Previous
      </VBtn>

      <VBtn
        v-for="page in lastPage"
        :key="page"
        size="small"
        elevation="0"
        :class="page === currentPage ? 'pagination-active' : 'pagination-btn'"
        @click="goToPage(page)"
      >
        {{ page }}
      </VBtn>

      <VBtn
        class="pagination-btn"
        size="small"
        elevation="0"
        :disabled="currentPage === lastPage"
        @click="goToPage(currentPage + 1)"
      >
        Next ›
      </VBtn>
    </div>
  </div>

  <!-- Modals -->
  <ViewObrModal v-model="showViewModal" :obr="selectedObr" />
  <BulkProceedModal v-model="showBulkProceedModal" :selected-obrs="selectedObrs" :current-stage="activeTabTitle" :next-stage="nextStageName" @confirm="confirmBulkProceed" />
  <BulkReturnModal v-model="showBulkReturnModal" :selected-obrs="selectedObrs" :current-stage="activeTabTitle" :previous-stage="previousStageName" @confirm="confirmBulkReturn" />
  <BulkRejectModal v-model="showBulkRejectModal" :selected-obrs="selectedObrs" :current-stage="activeTabTitle" @confirm="confirmBulkReject" />
  <BulkArchiveModal v-model="showBulkArchiveModal" :selected-obrs="selectedObrs" :current-stage="activeTabTitle" @confirm="confirmBulkArchive" />

  <!-- Toast Notification -->
  <VSnackbar v-model="toast.show" :color="toast.color" location="top right" :timeout="3000">
    <div class="d-flex align-center">
      <VIcon :icon="toast.icon" class="me-2" />
      {{ toast.message }}
    </div>
    <template v-slot:actions>
      <VBtn variant="text" icon size="small" color="white" @click="toast.show = false">
        <VIcon icon="bx-x" />
      </VBtn>
    </template>
  </VSnackbar>
</template>

<style scoped>
.select-workflow-set {
  max-width: 260px;
}

.hover-row {
  transition: background-color 0.2s ease;
  cursor: pointer;
}

.hover-row:hover {
  background-color: #f3f4ff;
}

/* Rejected row indicator */
.has-rejection {
  border-left: 4px solid #f44336;
}

.rejection-icon {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Modern Action Buttons */
.action-btn {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  min-width: 140px;
  justify-content: center;
}

.action-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-btn:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* Proceed Button - Green */
.action-btn-proceed {
  background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
}

.action-btn-proceed:hover:not(:disabled) {
  background: linear-gradient(135deg, #45a049 0%, #3d8b40 100%);
}

/* Return Button - Orange */
.action-btn-return {
  background: linear-gradient(135deg, #ff9800 0%, #fb8c00 100%);
}

.action-btn-return:hover:not(:disabled) {
  background: linear-gradient(135deg, #fb8c00 0%, #f57c00 100%);
}

/* Reject Button - Red */
.action-btn-reject {
  background: linear-gradient(135deg, #f44336 0%, #e53935 100%);
}

.action-btn-reject:hover:not(:disabled) {
  background: linear-gradient(135deg, #e53935 0%, #d32f2f 100%);
}

/* Archive Button - Primary Blue */
.action-btn-archive {
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgba(var(--v-theme-primary), 0.85) 100%);
}

.action-btn-archive:hover:not(:disabled) {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.85) 0%, rgba(var(--v-theme-primary), 0.75) 100%);
}

/* Badge on Button */
.btn-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #fff;
  color: #1a1a1a;
  font-size: 0.75rem;
  font-weight: 700;
  min-width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  padding: 0 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  border: 2px solid currentColor;
}

.action-btn-proceed .btn-badge {
  border-color: #4caf50;
}

.action-btn-return .btn-badge {
  border-color: #ff9800;
}

.action-btn-reject .btn-badge {
  border-color: #f44336;
}

.action-btn-archive .btn-badge {
  border-color: rgb(var(--v-theme-primary));
}

/* Responsive */
@media (max-width: 640px) {
  .action-btn {
    min-width: 120px;
    padding: 8px 16px;
    font-size: 0.813rem;
  }
}

/* Pagination */
.pagination-wrapper {
  flex-direction: row;
}

@media (max-width: 640px) {
  .pagination-wrapper {
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }
}

.pagination-btn {
  background-color: #f3f4f6 !important;
  color: #374151 !important;
  border-radius: 9999px !important;
  min-width: 36px;
  height: 36px;
  padding: 0 12px;
  font-weight: 500;
  text-transform: none !important;
  transition: all 0.2s ease;
  border: none !important;
  box-shadow: none !important;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #2563eb !important;
  color: #ffffff !important;
}

.pagination-active {
  background-color: #2563eb !important;
  color: #ffffff !important;
  border-radius: 9999px !important;
  min-width: 36px;
  height: 36px;
  padding: 0 14px;
  font-weight: 600;
  border: none !important;
  box-shadow: none !important;
}

.pagination-btn:disabled {
  opacity: 0.5;
  pointer-events: none;
}

/* Tabs */
.tabs-wrapper {
  border-bottom: 2px solid #e2e8f0;
}

.tabs-bar {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  padding: 0 24px;
  background: transparent;
  overflow-x: auto;
  scrollbar-width: none;
}

.tabs-bar::-webkit-scrollbar {
  display: none;
}

.hub-tab {
  position: relative;
  appearance: none;
  border: 1px solid #e2e8f0;
  border-bottom: none;
  background: #ffffff;
  color: #64748b;
  font-weight: 500;
  font-size: 0.85rem;
  letter-spacing: 0.2px;
  padding: 10px 20px;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.04);
}

.hub-tab:hover:not(.hub-tab--active) {
  color: #334155;
  background: #f8fafc;
  transform: translateY(-2px);
  box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.08);
}

.hub-tab--active {
  color: #1f2937;
  font-weight: 700;
  background: #ffffff;
  border-color: #e2e8f0;
  box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
  z-index: 1;
}

.hub-tab--active::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 10px;
  right: 10px;
  height: 3px;
  background-color: rgb(var(--v-theme-primary));
  border-radius: 2px;
}

.hub-tab--disabled {
  opacity: 0.4;
  cursor: not-allowed;
  pointer-events: none;
}

.tab-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background-color: rgb(var(--v-theme-error)) !important;
  color: white;
  font-size: 0.65rem;
  font-weight: 500;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
}

.custom-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: rgb(var(--v-theme-primary));
}
</style>