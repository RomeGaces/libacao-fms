<script setup>
import api from "@fms/utils/api";
import { computed, onMounted, ref } from 'vue'
import PaoModal from "./PaoModal.vue"
import DeletePaoModal from './DeletePaoModal.vue'

const requests = ref([])
const loading = ref(true)
const activeRequestIds = ref([])
const activeGroupIds = ref([])

const searchQuery = ref('')
const sortOrder = ref('desc') // default newest first
const isTableView = ref(true) // false for Accordion, true for Table
const sortItems = ref([
  { title: 'Newest First', value: 'desc' },
  { title: 'Oldest First', value: 'asc' },
])

// --- Edit/Add Modal state ---
const isModalOpen = ref(false)
const modalData = ref({})

// --- Delete Modal State ---
const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)
const requestToDelete = ref(null)


function openAddModal() {
  modalData.value = {}
  isModalOpen.value = true
}

function openEditModal(request) {
  modalData.value = request
  isModalOpen.value = true
}

function handleSaved() {
  fetchRequests()
}

// --- Delete Functions ---
function openDeleteModal(request) {
  requestToDelete.value = request // Store the whole request object
  isDeleteModalOpen.value = true
}

async function deleteRequest() {
  if (!requestToDelete.value) return

  isDeleting.value = true
  try {
    await api.delete(`/pao-requests/${requestToDelete.value.request_id}`)
    fetchRequests()
    isDeleteModalOpen.value = false // Close modal on success
  } catch (error) {
    console.error('Failed to delete request:', error)
  } finally {
    isDeleting.value = false
    requestToDelete.value = null // Clean up after
  }
}

async function fetchRequests() {
  loading.value = true
  try {
    const response = await api.get('/pao-requests')
    requests.value = response.data
  } catch (error) {
    console.error('Failed to fetch requests:', error)
  } finally {
    loading.value = false
  }
}
onMounted(fetchRequests)

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function calculateGrandTotal(request) {
  if (!request || !request.groups) return 0;
  return request.groups.reduce((total, group) => {
    const groupTotal = group.objects.reduce((groupSum, obj) => {
      return groupSum + (Number(obj.amount) || 0);
    }, 0);
    return total + groupTotal;
  }, 0);
}

function formatCurrency(value) {
  if (value === null || value === undefined) return '-';
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(value);
}

function togglePanel(list, id) {
  const idx = list.indexOf(id)
  if (idx === -1) list.push(id)
  else list.splice(idx, 1)
}

function isActive(list, id) {
  return list.includes(id)
}

function getStatusClass(req) {
  const budget = parseFloat(req.budget);
  const grandTotal = calculateGrandTotal(req);

  // No budget or invalid budget
  if (isNaN(budget) || budget <= 0) {
    return 'gray';
  }

  const remaining = budget - grandTotal;
  const percentageRemaining = (remaining / budget) * 100; // Convert to percentage

  // 0% remaining (over budget or exactly at budget)
  if (percentageRemaining <= 0) {
    return 'gray';
  }
  // 1% - 9% remaining (Critical)
  else if (percentageRemaining >= 1 && percentageRemaining <= 9) {
    return 'red';
  }
  // 10% - 49% remaining (Warning)
  else if (percentageRemaining >= 10 && percentageRemaining <= 49) {
    return 'orange';
  }
  // 50% - 100% remaining (Good)
  else if (percentageRemaining >= 50) {
    return 'green';
  }
  
  return 'gray';
}

/* --- smooth expand/collapse --- */
function beforeEnter(el) {
  el.style.height = '0'
  el.style.opacity = '0'
  el.style.overflow = 'hidden'
}
function enter(el, done) {
  const height = el.scrollHeight + 'px'
  el.style.transition = 'height 300ms ease, opacity 200ms ease'
  requestAnimationFrame(() => {
    el.style.height = height
    el.style.opacity = '1'
  })
  const onEnd = (e) => {
    if (e.target !== el) return
    el.style.height = ''
    el.style.transition = ''
    el.style.overflow = ''
    el.removeEventListener('transitionend', onEnd)
    done()
  }
  el.addEventListener('transitionend', onEnd)
}
function leave(el, done) {
  el.style.height = el.scrollHeight + 'px'
  el.style.opacity = '1'
  el.style.overflow = 'hidden'
  el.getBoundingClientRect()
  requestAnimationFrame(() => {
    el.style.transition = 'height 300ms ease, opacity 200ms ease'
    el.style.height = '0'
    el.style.opacity = '0'
  })
  const onEnd = (e) => {
    if (e.target !== el) return
    el.style.height = ''
    el.style.transition = ''
    el.style.overflow = ''
    el.removeEventListener('transitionend', onEnd)
    done()
  }
  el.addEventListener('transitionend', onEnd)
}

/* --- computed filtered & sorted requests --- */
const filteredRequests = computed(() => {
  let result = requests.value

  if (searchQuery.value.trim() !== '') {
    const q = searchQuery.value.toLowerCase()
    result = result.filter((req) =>
      req.name?.toLowerCase().includes(q) ||
      req.office_code_description?.toLowerCase().includes(q)
    )
  }

  result = result.sort((a, b) => {
    const da = new Date(a.created_at)
    const db = new Date(b.created_at)
    return sortOrder.value === 'asc' ? da - db : db - da
  })

  return result
})
</script>

<template>
  <div class="d-flex justify-end mb-4 responsive-add">
    <VBtn variant="outlined" color="primary" @click="openAddModal">
      + Add PAO Request
    </VBtn>
  </div>

  <div class="accordion-card">
    <div class="controls">
      <div class="controls-top-row">
        <VTextField
          v-model="searchQuery"
          placeholder="Search by requester or office..."
          prepend-inner-icon="bx-search"
          density="compact"
          variant="outlined"
          hide-details
          class="search-field"
        />
        <VSelect
          v-model="sortOrder"
          :items="sortItems"
          prepend-inner-icon="bx-sort-alt-2"
          density="compact"
          variant="outlined"
          hide-details
          class="sort-dropdown"
        />
      </div>

      <div class="controls-bottom-row">
        <VSwitch
          v-model="isTableView"
          :label="isTableView ? 'Switch to Accordion View' : 'Switch to Table View'"
          color="primary"
          density="compact"
          hide-details
          class="view-switch"
        />
      </div>
    </div>

    <div class="legend-container">
      <div class="legend-item">
        <span class="legend-dot green"></span>
        <span class="legend-text">50% - 100% budget remaining</span>
      </div>
      <div class="legend-item">
        <span class="legend-dot orange"></span>
        <span class="legend-text">10% - 49% budget remaining</span>
      </div>
      <div class="legend-item">
        <span class="legend-dot red"></span>
        <span class="legend-text">1% - 9% budget remaining (Critical)</span>
      </div>
      <div class="legend-item">
        <span class="legend-dot gray"></span>
        <span class="legend-text">0% budget remaining</span>
      </div>
    </div>
    <div v-if="loading" class="py-10 text-center">
      <VProgressCircular indeterminate color="primary" size="32" width="4" />
      <div class="mt-2 text-grey-darken-2 loader-text">
        Loading requests...
      </div>
    </div>

    <div v-else>
      <div v-if="!isTableView">
        <div
          v-for="req in filteredRequests"
          :key="req.request_id"
          class="accordion-item"
        >
          <button
            class="accordion-header"
            type="button"
            :aria-expanded="isActive(activeRequestIds, req.request_id)"
            @click="togglePanel(activeRequestIds, req.request_id)"
          >
            <div class="d-flex align-center gap-3">
              <span :class="['status-pill', getStatusClass(req)]"></span>
              <div class="header-left">
                <div class="group-title">{{ req.office_code_description }}</div>
                <div class="subheader">{{ formatDate(req.created_at) }}</div>
                <div class="subheader">Requested By: {{ req.name }}</div>
              </div>
            </div>

            <div class="header-right">
              <div class="action-buttons">
                <VBtn variant="text" size="small" color="primary" @click.stop="openEditModal(req)">
                  Edit
                </VBtn>
                <VBtn
                  icon="bx-trash"
                  variant="text"
                  size="small"
                  color="error"
                  @click.stop="openDeleteModal(req)"
                />
              </div>
              <VIcon
                :class="['caret', { rotated: isActive(activeRequestIds, req.request_id) }]"
                icon="bx-chevron-down"
              />
            </div>
          </button>

          <transition @before-enter="beforeEnter" @enter="enter" @leave="leave">
            <div
              v-if="isActive(activeRequestIds, req.request_id)"
              class="accordion-body"
            >
              <div
                v-for="grp in req.groups"
                :key="`${req.request_id}-${grp.group_id}`"
                class="accordion-item group-accordion"
              >
                <button
                  class="accordion-header group-header"
                  type="button"
                  :aria-expanded="isActive(activeGroupIds, `${req.request_id}-${grp.group_id}`)"
                  @click="togglePanel(activeGroupIds, `${req.request_id}-${grp.group_id}`)"
                >
                  <div class="group-title">{{ grp.group_name }}</div>
                  <VIcon
                    :class="['caret', { rotated: isActive(activeGroupIds, `${req.request_id}-${grp.group_id}`) }]"
                    icon="bx-chevron-down"
                  />
                </button>

                <transition @before-enter="beforeEnter" @enter="enter" @leave="leave">
                  <div
                    v-if="isActive(activeGroupIds, `${req.request_id}-${grp.group_id}`)"
                    class="accordion-body"
                  >
                    <VTable class="accordion-table">
                      <thead>
                        <tr class="bg-grey-lighten-4 text-grey-darken-3 text-sm">
                          <th>Account Code</th>
                          <th>Object Expenditure</th>
                          <th class="text-right">Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="obj in grp.objects" :key="obj.object_expenditure_id">
                          <td>{{ obj.account_code }}</td>
                          <td>{{ obj.object_expenditure_name }}</td>
                          <td class="text-right">{{ formatCurrency(obj.amount) }}</td>
                        </tr>
                      </tbody>
                    </VTable>
                  </div>
                </transition>
              </div>
            </div>
          </transition>
        </div>
      </div>

      <div v-else>
        <VTable class="main-table">
          <thead>
            <tr>
              <th class="status-column-header text-center">Status</th>
              <th>Year</th>
              <th>Office Code</th>
              <th>Requested By</th>
              <th class="text-right">Grand Total</th>
              <th>Budget</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="req in filteredRequests"
              :key="req.request_id"
              class="clickable-row"
              @click="openEditModal(req)"
            >
              <td class="text-center">
                <span :class="['status-pill', getStatusClass(req)]"></span>
              </td>
              <td>{{ req.budget_year }}</td>
              <td>{{ req.office_code_description }}</td>
              <td>{{ req.name }}</td>
              <td class="text-right">{{ formatCurrency(calculateGrandTotal(req)) }}</td>
              <td>{{ formatCurrency(req.budget) }}</td>
              <td class="text-center">
                <VBtn
                  icon="bx-trash"
                  variant="text"
                  size="small"
                  color="error"
                  @click.stop="openDeleteModal(req)"
                />
              </td>
            </tr>
          </tbody>
        </VTable>
      </div>

      <div v-if="!loading && filteredRequests.length === 0" class="text-center py-6 text-grey-darken-2">
        No requests found.
      </div>
    </div>
  </div>

  <PaoModal
    v-model="isModalOpen"
    :data="modalData"
    @saved="handleSaved"
  />

  <DeletePaoModal
    v-model="isDeleteModalOpen"
    :item-name="requestToDelete?.office_code_description"
    :loading="isDeleting"
    @confirm="deleteRequest"
  />
</template>

<style scoped>
.loader-text {
  font-size: 0.875rem;
}
.accordion-card {
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e6e9ee;
  box-shadow: 0 6px 18px rgba(12, 20, 40, 0.04);
  overflow: hidden;
  padding: 16px;
}

.controls {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.controls-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.controls-bottom-row {
  display: flex;
  justify-content: flex-end;
}

.search-field {
  max-width: 600px !important;
  width: 100%;
}
.sort-dropdown {
  max-width: 250px !important;
}

.view-switch {
  align-self: center;
}
.view-switch :deep(.v-selection-control) {
  flex-direction: row-reverse;
  padding-inline-start: 0;
}
.view-switch :deep(.v-label) {
  font-size: 0.825rem;
  color: #64748b;
  opacity: 1;
  margin-right: 8px;
}

/* --- ALWAYS-VISIBLE LEGEND STYLES --- */
.legend-container {
  display: flex;
  justify-content: flex-end;
  gap: 20px;
  margin-top: 4px;
  margin-bottom: 20px;
  padding: 0 4px;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
}
.legend-text {
  font-size: 0.8rem;
  color: #475569;
}
.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}
.legend-dot.green {
  background-color: #22c55e;
}
.legend-dot.orange {
  background-color: #f97316;
}
.legend-dot.red {
  background-color: #ef4444;
}
.legend-dot.gray {
  background-color: #94a3b8;
}


.accordion-item + .accordion-item {
  border-top: 1px solid #eef2f6;
}
.accordion-header {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f9f9f9;
  padding: 14px 18px;
  border: none;
  cursor: pointer;
  text-align: left;
  transition: background 0.25s ease;
}
.accordion-header:hover {
  background: #f3f4f6;
}
.header-left {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
.header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}
.action-buttons {
    display: flex;
    align-items: center;
    gap: 4px;
}
.group-title {
  font-weight: 600;
  font-size: 1rem;
  color: #12263b;
}
.subheader {
  font-size: 0.875rem;
  color: #64748b;
  margin-top: 2px;
}
.caret {
  transition: transform 260ms ease;
}
.caret.rotated {
  transform: rotate(180deg);
}
.accordion-body {
  padding: 12px 18px 20px 18px;
  background: #fff;
}
.group-accordion {
  border: 1px solid #eef2f6;
  margin-top: 8px;
  border-radius: 6px;
}
.group-header {
  background: #fdfdfd;
}
.accordion-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.accordion-table th,
.accordion-table td {
  font-size: 0.875rem;
  color: #334155;
  padding: 10px 14px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.accordion-table th {
  font-weight: 600;
  background: #f9fafb;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.accordion-table th:nth-child(1),
.accordion-table td:nth-child(1) {
  width: 25%;
}

.accordion-table th:nth-child(2),
.accordion-table td:nth-child(2) {
  width: 50%;
}

.accordion-table th:nth-child(3),
.accordion-table td:nth-child(3) {
  width: 25%;
  text-align: right;
}

.status-pill {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: rgb(var(--v-theme-primary));
  flex-shrink: 0;
  display: inline-block;
}

.status-pill.green {
  background-color: #22c55e;
}
.status-pill.orange {
  background-color: #f97316;
}
.status-pill.red {
  background-color: #ef4444;
}
.status-pill.gray {
  background-color: #94a3b8;
}

.main-table {
    width: 100%;
    border: 1px solid #eef2f6;
    border-radius: 6px;
    border-spacing: 0;
}

.main-table th {
    background-color: #f9fafb;
    color: #4b5563;
    font-weight: 600;
    text-align: left;
    padding: 12px 16px;
    border-bottom: 1px solid #eef2f6;
    font-size: 0.8rem;
}

.main-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #eef2f6;
    font-size: 0.875rem;
    color: #334155;
    vertical-align: middle;
}

.main-table tbody tr:last-child td {
    border-bottom: none;
}

.status-column-header {
    width: 60px;
}

.main-table tbody .clickable-row {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.main-table tbody .clickable-row:hover {
  background-color: #f9fafb;
}

@media (max-width: 768px) {
  .controls-top-row {
    flex-direction: column;
    align-items: stretch;
  }
  .search-field,
  .sort-dropdown {
    max-width: 100% !important;
  }
  .legend-container {
    justify-content: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 16px;
  }
}
</style>