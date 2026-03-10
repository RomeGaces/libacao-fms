<script setup>
import api from "@fms/utils/api";
import { onMounted, ref } from 'vue'
import DeleteExpendituresModal from './DeleteExpendituresModal.vue'
import ExpendituresModal from './ExpendituresModal.vue'

const groups = ref([])
const loading = ref(true)
const activePanelIds = ref([])

// modals & selected data
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedData = ref(null)
const modalType = ref('group')

const fetchGroups = async () => {
  loading.value = true
  try {
    const response = await api.get('/group-object-expenditures')
    groups.value = response.data
  } catch (error) {
    console.error('Failed to fetch groups:', error)
  } finally {
    loading.value = false
  }
}
onMounted(() => { fetchGroups() })

function togglePanel(id, singleExpand = false) {
  const idx = activePanelIds.value.indexOf(id)
  if (idx === -1) {
    if (singleExpand) activePanelIds.value = [id]
    else activePanelIds.value.push(id)
  } else {
    activePanelIds.value.splice(idx, 1)
  }
}
function isActive(id) { return activePanelIds.value.includes(id) }

// ✅ Updated: Pass groupId always into modal
const handleEdit = (type, data = null, groupId = null) => {
  modalType.value = type
  let payload = data ? JSON.parse(JSON.stringify(data)) : {}
  if (groupId) payload.group_id = groupId
  selectedData.value = payload
  showEditModal.value = true
}

const handleDelete = (type, data, groupId = null) => {
  modalType.value = type
  let payload = JSON.parse(JSON.stringify(data))
  if (groupId) payload.group_id = groupId
  selectedData.value = payload
  showDeleteModal.value = true
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
</script>

<template>
  <!-- Add Group Button -->
  <div class="d-flex justify-end mb-6">
    <VBtn
      variant="outlined"
      color="primary"
      prepend-icon="bx-plus"
      @click="handleEdit('group', null)"
    >
      Add Group
    </VBtn>
  </div>

  <!-- Wrapper -->
  <div class="accordion-card">
    <div v-if="loading" class="py-10 text-center">
      <VProgressCircular indeterminate color="primary" size="32" width="4" />
      <div class="mt-2 text-grey-darken-2 loader-text">Loading groups...</div>
    </div>

    <div v-else>
      <div v-for="group in groups" :key="group.group_id" class="accordion-item">
        <!-- Header -->
        <button
          class="accordion-header"
          type="button"
          :aria-expanded="isActive(group.group_id)"
          @click="togglePanel(group.group_id)"
        >
          <div class="header-left">
            <span class="group-title">{{ group.group_name }}</span>
          </div>

          <div class="header-right">
            <!-- Add Object -->
            <VTooltip text="Add Object">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon
                  size="small"
                  variant="text"
                  class="action-btn add-btn"
                  @click.stop="handleEdit('object', null, group.group_id)"
                >
                  <VIcon icon="bx-plus" />
                </VBtn>
              </template>
            </VTooltip>

            <!-- Edit Group -->
            <VTooltip text="Edit Group">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon
                  size="small"
                  variant="text"
                  class="action-btn edit-btn"
                  @click.stop="handleEdit('group', group, group.group_id)"
                >
                  <VIcon icon="bx-edit" />
                </VBtn>
              </template>
            </VTooltip>

            <!-- Delete Group -->
            <VTooltip text="Delete Group">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon
                  size="small"
                  variant="text"
                  class="action-btn delete-btn"
                  @click.stop="handleDelete('group', group, group.group_id)"
                >
                  <VIcon icon="bx-trash" />
                </VBtn>
              </template>
            </VTooltip>

            <VIcon
              :class="['caret', { 'rotated': isActive(group.group_id) }]"
              icon="bx-chevron-down"
            />
          </div>
        </button>

        <!-- Collapsible body -->
        <transition @before-enter="beforeEnter" @enter="enter" @leave="leave">
          <div
            v-if="isActive(group.group_id)"
            class="accordion-body"
            aria-hidden="false"
          >
            <VTable class="accordion-table">
              <thead>
                <tr class="bg-grey-lighten-4 text-grey-darken-3 text-sm">
                  <th class="col-name">Object Name</th>
                  <th class="col-code">Account Code</th>
                  <th class="col-actions">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="obj in group.object_of_expenditures"
                  :key="obj.object_id"
                  class="hover-row"
                >
                  <td class="col-name">{{ obj.object_name }}</td>
                  <td class="col-code">{{ obj.account_code }}</td>
                  <td class="col-actions">
                    <VBtn
                      variant="text"
                      color="primary"
                      size="small"
                      prepend-icon="bx-edit"
                      @click.stop="handleEdit('object', obj, group.group_id)"
                    >
                      Edit
                    </VBtn>
                    <VBtn
                      variant="text"
                      color="error"
                      size="small"
                      prepend-icon="bx-trash"
                      @click.stop="handleDelete('object', obj, group.group_id)"
                    >
                      Delete
                    </VBtn>
                  </td>
                </tr>

                <tr v-if="!group.object_of_expenditures.length">
                  <td colspan="3" class="text-center py-3 text-grey-darken-2 italic">
                    No objects found.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </div>
        </transition>
      </div>
    </div>
  </div>

  <!-- Modals -->
  <ExpendituresModal
    v-model="showEditModal"
    :data="selectedData"
    :type="modalType"
    @saved="fetchGroups"
  />
  <DeleteExpendituresModal
    v-model="showDeleteModal"
    :data="selectedData"
    :type="modalType"
    @deleted="fetchGroups"
  />
</template>

<style scoped>
.loader-text {
  font-size: 0.800rem; /* match table font size */
  color: #64748b;      /* consistent neutral tone */
}
/* wrapper */
.accordion-card {
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e6e9ee;
  box-shadow: 0 6px 18px rgba(12, 20, 40, 0.04);
  overflow: hidden;
}
.accordion-item + .accordion-item { border-top: 1px solid #eef2f6; }

/* header */
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
.accordion-header:hover { background: #f3f4f6; }
.header-left { display:flex; align-items:center; gap:12px; }
.group-title { font-weight: 600; font-size: 0.95rem; color: #12263b; }

/* right side */
.header-right { display: flex; align-items: center; gap: 10px; }

/* neutral action buttons */
.action-btn {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  color: #64748b;
  transition: background 0.2s ease, color 0.2s ease;
}

/* add */
.add-btn:hover {
  background: rgba(59, 130, 246, 0.08);
  color: #2563eb;
}
/* edit */
.edit-btn:hover {
  background: rgba(139, 92, 246, 0.08);
  color: #7c3aed;
}
/* delete */
.delete-btn:hover {
  background: rgba(239, 68, 68, 0.08);
  color: #dc2626;
}

/* caret */
.caret {
  transition: transform 260ms ease;
  display: inline-flex;
  align-items: center;
}
.caret.rotated { transform: rotate(180deg); }

/* body */
.accordion-body {
  padding: 12px 18px 20px 18px;
  background: #fff;
}

/* table */
.accordion-table { border-collapse: collapse; width: 100%; table-layout: fixed; }
.accordion-table th, .accordion-table td {
  font-size: 0.875rem;
  color: #334155;
  padding: 10px 14px;
}
.accordion-table th { font-weight: 600; }
.col-name { width: 40%; text-align: left; }
.col-code { width: 30%; text-align: left; }
.col-actions { width: 30%; text-align: left; }
.hover-row { transition: background 0.18s ease; }
.hover-row:hover { background-color: #f9fafb; }
</style>
