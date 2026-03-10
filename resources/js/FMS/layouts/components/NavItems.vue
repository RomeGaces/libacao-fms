<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import api from "@fms/utils/api"
import openBook from '@/../icons/open_book_3d.png'
import pencil from '@/../icons/pencil_3d.png'
import puzzlePiece from '@/../icons/puzzle_piece_3d.png'
import bookmarkTabs from '@/../icons/bookmark_tabs_3d.png'
import okHand from '@/../icons/ok_hand_3d_default.png'
import greenBook from '@/../icons/green_book_3d.png'
import moneyBag from '@/../icons/money_bag_3d.png'
import house from '@/../icons/house_3d.png'
import manOfficer from '@/../icons/man_office_worker_3d_light.png'
import package3d from '@/../icons/package_3d.png'
import auditLogs from '@/../icons/audit_logs_3d.png'

import VerticalNavSectionTitle from '@fms/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'

const user = ref(null)
const totalApprovalCount = ref(0)
const officeId = ref(null)
const selectedSetId = ref(null)

// Check if user is from Municipal Budget Officer
const isMunicipalBudgetOfficer = computed(() => {
  return user.value?.department_details?.name === "Office of the Municipal Budget Officer"
})

// Check if user is from Municipal Accountant
const isMunicipalAccountant = computed(() => {
  return user.value?.department_details?.name === "Office of the Municipal Accountant"
})

// Check if user is from Municipal Treasurer
const isMunicipalTreasurer = computed(() => {
  return user.value?.department_details?.name === "Office of the Municipal Treasurer"
})

// Fetch user data
const fetchUser = async () => {
  try {
    const { data } = await api.get("/user")
    user.value = data

    await fetchOfficeId()
    await fetchDefaultSet()
    if (officeId.value && selectedSetId.value) {
      await fetchTotalApprovalCount()
    }
  } catch (err) {
    console.error("Failed to fetch user:", err.response?.data || err.message)
  }
}

// Fetch Office ID from user's department
const fetchOfficeId = async () => {
  try {
    if (user.value?.department_details?.name) {
      const description = user.value.department_details.name
      const response = await api.get(`/office-codes/find-by-description/${encodeURIComponent(description)}`)
      if (response.data?.id) {
        officeId.value = response.data.id
        return
      }
    }

    const userInfoStr = localStorage.getItem("user_info")
    if (userInfoStr) {
      const userInfo = JSON.parse(userInfoStr)
      const description = userInfo?.department_details?.name
      if (description) {
        const response = await api.get(`/office-codes/find-by-description/${encodeURIComponent(description)}`)
        if (response.data?.id) officeId.value = response.data.id
      }
    }
  } catch (error) {
    console.error("Failed to fetch office ID:", error)
  }
}

// Fetch default set (first set)
const fetchDefaultSet = async () => {
  try {
    const response = await api.get("/sets")
    if (response.data?.length > 0) selectedSetId.value = response.data[0].id
  } catch (error) {
    console.error("Failed to fetch sets:", error)
  }
}

// Fetch total approval count
const fetchTotalApprovalCount = async () => {
  if (!officeId.value || !selectedSetId.value) return
  try {
    const response = await api.get('/obr-requests/counts-by-internal-step', {
      params: { office_id: officeId.value, set_id: selectedSetId.value }
    })
    const counts = response.data || {}
    totalApprovalCount.value = Object.values(counts).reduce((sum, count) => sum + count, 0)
  } catch (error) {
    console.error("Failed to fetch approval counts:", error)
    totalApprovalCount.value = 0
  }
}

// --- CLICK ANIMATION FIX ---
// We toggle a class on pointerdown so animations run even if navigation occurs.
let detachPointerHandler = null
const attachClickAnimationHandler = () => {
  const handler = (e) => {
    const item = e.target?.closest?.('.v-list-item')
    if (!item) return
    item.classList.add('animate-click')

    // remove after animations complete
    const remove = () => item.classList.remove('animate-click')
    // fallback timeout in case animationend doesn't fire
    const t = setTimeout(remove, 700)

    const onEnd = () => {
      clearTimeout(t)
      remove()
      item.removeEventListener('animationend', onEnd, true)
    }
    item.addEventListener('animationend', onEnd, true)
  }
  document.addEventListener('pointerdown', handler, true)
  detachPointerHandler = () => document.removeEventListener('pointerdown', handler, true)
}

let refreshInterval = null
onMounted(async () => {
  await fetchUser()

  // periodic refresh
  refreshInterval = setInterval(() => {
    if (officeId.value && selectedSetId.value) fetchTotalApprovalCount()
  }, 30000)

  // hook animations
  attachClickAnimationHandler()
})

onBeforeUnmount(() => {
  if (refreshInterval) clearInterval(refreshInterval)
  if (detachPointerHandler) detachPointerHandler()
})
</script>

<template>
  <!-- 👉 If Municipal Accountant OR Municipal Treasurer Department -->
  <template v-if="user && (isMunicipalAccountant || isMunicipalTreasurer)">
    <VerticalNavSectionTitle :item="{ heading: 'Home' }" />
    <VerticalNavLink :item="{ title: 'Dashboard', icon: house, href: '/dashboard' }" />

    <VerticalNavSectionTitle :item="{ heading: 'Management' }" />
    <VerticalNavLink :item="{ title: 'Paper Trail', icon: bookmarkTabs, href: '/paper-trail-management' }" />

    <VerticalNavSectionTitle :item="{ heading: 'Review Center' }" />
    <VerticalNavLink
      :item="{
        title: 'For Approval',
        icon: okHand,
        href: '/for-approval',
        badgeContent: totalApprovalCount > 0 ? totalApprovalCount : undefined,
        badgeClass: 'bg-error'
      }"
    />
    <VerticalNavLink :item="{ title: 'Archives', icon: package3d, href: '/obr-archived' }" />

    <!-- Admin-only for Accountant and Treasurer -->
    <template v-if="user.is_admin">
      <VerticalNavSectionTitle :item="{ heading: 'System' }" />
      <VerticalNavLink :item="{ title: 'Account Access', icon: manOfficer, href: '/access-management' }" />
    </template>
  </template>

  <!-- 👉 If Municipal Budget Officer Department -->
  <template v-else-if="user && isMunicipalBudgetOfficer">
    <VerticalNavSectionTitle :item="{ heading: 'Home' }" />
    <VerticalNavLink :item="{ title: 'Dashboard', icon: house, href: '/dashboard' }" />

    <VerticalNavSectionTitle :item="{ heading: 'Review Center' }" />
    <VerticalNavLink
      :item="{
        title: 'For Approval',
        icon: okHand,
        href: '/for-approval',
        badgeContent: totalApprovalCount > 0 ? totalApprovalCount : undefined,
        badgeClass: 'bg-error'
      }"
    />
    <VerticalNavLink :item="{ title: 'Archives', icon: package3d, href: '/obr-archived' }" />

    <!-- Admin-only items for Budget Officer -->
    <template v-if="user.is_admin">
      <VerticalNavSectionTitle :item="{ heading: 'Management' }" />
      <VerticalNavLink :item="{ title: 'Paper Trail', icon: bookmarkTabs, href: '/paper-trail-management' }" />
      <VerticalNavLink :item="{ title: 'Budget Allotment', icon: moneyBag, href: '/annual-budget' }" />
    </template>

    <VerticalNavSectionTitle :item="{ heading: 'Data Management' }" />
    <VerticalNavLink :item="{ title: 'Expenditures', icon: pencil, href: '/expenditures' }" />

    <!-- Admin-only items for Budget Officer -->
    <template v-if="user.is_admin">
      <VerticalNavLink :item="{ title: 'Account Access', icon: manOfficer, href: '/access-management' }" />
    </template>

    <VerticalNavSectionTitle :item="{ heading: 'Department Requests' }" />
    <VerticalNavLink :item="{ title: 'Program Appropriation', icon: puzzlePiece, href: '/pao' }" />
    <VerticalNavLink :item="{ title: 'Obligation Requests', icon: greenBook, href: '/obr' }" />

    <VerticalNavSectionTitle :item="{ heading: 'System' }" />
    <VerticalNavLink :item="{ title: 'Office Codes', icon: openBook, href: '/office-codes' }" />
    <VerticalNavLink :item="{ title: 'Audit Logs', icon: auditLogs, href: '/audit-logs' }" />
  </template>

  <!-- 👉 If Admin (but not Municipal Budget Officer, Accountant, or Treasurer) -->
  <template v-else-if="user && user.is_admin && !isMunicipalBudgetOfficer && !isMunicipalAccountant && !isMunicipalTreasurer">
    <VerticalNavSectionTitle :item="{ heading: 'Home' }" />
    <VerticalNavLink :item="{ title: 'Dashboard', icon: house, href: '/dashboard' }" />

    <VerticalNavSectionTitle :item="{ heading: 'Review Center' }" />
    <VerticalNavLink :item="{ title: 'Audit Logs', icon: auditLogs, href: '/audit-logs' }" />
    <VerticalNavLink
      :item="{
        title: 'For Approval',
        icon: okHand,
        href: '/for-approval',
        badgeContent: totalApprovalCount > 0 ? totalApprovalCount : undefined,
        badgeClass: 'bg-error'
      }"
    />
    <VerticalNavLink :item="{ title: 'Archives', icon: package3d, href: '/obr-archived' }" />

    <VerticalNavSectionTitle :item="{ heading: 'Department Requests' }" />
    <VerticalNavLink :item="{ title: 'Program Appropriation', icon: puzzlePiece, href: '/pao' }" />
    <VerticalNavLink :item="{ title: 'Obligation Requests', icon: greenBook, href: '/obr' }" />

    <VerticalNavSectionTitle :item="{ heading: 'Management' }" />
    <VerticalNavLink :item="{ title: 'Budget Allotment', icon: moneyBag, href: '/annual-budget' }" />
    <VerticalNavLink :item="{ title: 'Office Codes', icon: openBook, href: '/office-codes' }" />
    <VerticalNavLink :item="{ title: 'Expenditures', icon: pencil, href: '/expenditures' }" />
    <VerticalNavLink :item="{ title: 'Paper Trail', icon: bookmarkTabs, href: '/paper-trail-management' }" />
    <VerticalNavLink :item="{ title: 'Account Access', icon: manOfficer, href: '/access-management' }" />
  </template>

  <!-- 👉 If NOT Admin and NOT Municipal Budget Officer, Accountant, or Treasurer -->
  <template v-else-if="user && !user.is_admin && !isMunicipalBudgetOfficer && !isMunicipalAccountant && !isMunicipalTreasurer">
    <VerticalNavSectionTitle :item="{ heading: 'Review Center' }" />
    <VerticalNavLink :item="{ title: 'Audit Logs', icon: auditLogs, href: '/audit-logs' }" />
    <VerticalNavLink
      :item="{
        title: 'For Approval',
        icon: okHand,
        href: '/for-approval',
        badgeContent: totalApprovalCount > 0 ? totalApprovalCount : undefined,
        badgeClass: 'bg-error'
      }"
    />
  </template>
</template>

<style scoped>
/* MINIMALIST MODERN DESIGN - Clean and Professional with Click Animations */

/* Remove all default padding/margins for clean slate */
:deep(*) {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Section Titles - Minimal and Clean */
:deep(.nav-section-title) {
  font-size: 10px !important;
  font-weight: 500 !important;
  color: #9CA3AF !important;
  text-transform: uppercase !important;
  letter-spacing: 0.8px !important;
  margin: 20px 0 8px 16px !important;
  padding: 0 !important;
}
:deep(.nav-section-title:first-of-type) { margin-top: 12px !important; }

/* Navigation Items */
:deep(.v-list-item) {
  min-height: 40px !important;
  padding: 8px 16px !important;
  margin: 1px 0 !important;
  background: transparent !important;
  border: none !important;
  border-radius: 0 !important;
  transition: all 0.15s ease !important;
  position: relative !important;
  cursor: pointer !important;
  overflow: hidden !important;
}

/* Hover */
:deep(.v-list-item:hover) { background: rgba(0, 0, 0, 0.02) !important; }

/* Active item */
:deep(.v-list-item--active) {
  background: rgba(59, 130, 246, 0.05) !important;
  animation: activeSlide 0.3s ease-out !important;
}
/* Active left border */
:deep(.v-list-item--active::before) {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 2px;
  background: #3B82F6 !important;
  animation: borderSlideIn 0.2s ease-out !important;
}

/* —— THE IMPORTANT CHANGE: use a class instead of :active —— */

/* Ripple element */
:deep(.v-list-item::after) {
  content: '';
  position: absolute;
  top: 50%; left: 50%;
  width: 0; height: 0;
  border-radius: 50%;
  background: rgba(59, 130, 246, 0.3);
  transform: translate(-50%, -50%);
  pointer-events: none;
}

/* Trigger ripple, icon bounce, press scale via .animate-click */
:deep(.v-list-item.animate-click::after) { animation: rippleEffect 0.6s ease-out !important; }
:deep(.v-list-item.animate-click) { transform: scale(0.98) !important; transition: transform 0.1s ease !important; }

/* Flash sweep */
:deep(.v-list-item::before) {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.2), transparent);
  transform: translateX(-100%);
}
:deep(.v-list-item.animate-click::before) { animation: slideFlash 0.5s ease-out !important; }

/* Icons */
:deep(.nav-link-icon),
:deep(.nav-item-icon),
:deep(.v-list-item__prepend img) {
  width: 20px !important; height: 20px !important;
  object-fit: contain !important;
  opacity: 0.7 !important;
  transition: all 0.15s ease !important;
}
:deep(.v-list-item:hover .nav-link-icon),
:deep(.v-list-item:hover .nav-item-icon),
:deep(.v-list-item:hover img) { opacity: 0.9 !important; }

/* Icon bounce when clicked */
:deep(.v-list-item.animate-click .nav-link-icon),
:deep(.v-list-item.animate-click .nav-item-icon),
:deep(.v-list-item.animate-click img) { animation: iconBounce 0.4s ease !important; }

/* Icon spacing */
:deep(.v-list-item__prepend) { margin-right: 12px !important; min-width: 20px !important; }

/* Text */
:deep(.nav-link-title),
:deep(.nav-item-title),
:deep(.v-list-item-title) {
  font-size: 13px !important;
  font-weight: 400 !important;
  color: #374151 !important;
  letter-spacing: 0 !important;
  line-height: 1.5 !important;
  transition: all 0.15s ease !important;
}
:deep(.v-list-item:hover .nav-link-title),
:deep(.v-list-item:hover .nav-item-title),
:deep(.v-list-item:hover .v-list-item-title) { color: #111827 !important; }
:deep(.v-list-item--active .nav-link-title),
:deep(.v-list-item--active .nav-item-title),
:deep(.v-list-item--active .v-list-item-title) { color: #3B82F6 !important; font-weight: 500 !important; }

/* Badge */
:deep(.v-badge__badge),
:deep(.nav-item-badge) {
  min-width: 16px !important; height: 16px !important; padding: 0 4px !important;
  font-size: 10px !important; font-weight: 600 !important; line-height: 16px !important;
  border-radius: 8px !important; background: #DC2626 !important; color: #fff !important;
  border: none !important; box-shadow: none !important;
  animation: badgePulse 2s ease-in-out infinite !important;
}

/* Content alignment */
:deep(.v-list-item__content) { align-items: center !important; display: flex !important; }

/* Remove Vuetify ripple so ours shows */
:deep(.v-ripple__container) { display: none !important; }

/* Focus state */
:deep(.v-list-item:focus-visible) {
  outline: 1px solid #3B82F6 !important; outline-offset: -1px !important;
  background: rgba(59, 130, 246, 0.05) !important;
}

/* Ensure clean white background */
:deep(.v-navigation-drawer__content) { background: #fff !important; }

/* ANIMATION KEYFRAMES */
@keyframes rippleEffect {
  0% { width: 0; height: 0; opacity: 0.5; }
  100% { width: 200px; height: 200px; opacity: 0; }
}
@keyframes iconBounce {
  0% { transform: scale(1); }
  30% { transform: scale(0.85); }
  60% { transform: scale(1.15); }
  100% { transform: scale(1); }
}
@keyframes activeSlide {
  0% { transform: translateX(-4px); opacity: 0.5; }
  100% { transform: translateX(0); opacity: 1; }
}
@keyframes borderSlideIn {
  0% { height: 0; top: 50%; }
  100% { height: 100%; top: 0; }
}
@keyframes badgePulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}
@keyframes slideFlash {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

/* Mobile */
@media (max-width: 960px) {
  :deep(.v-list-item) { min-height: 36px !important; padding: 6px 12px !important; }
  :deep(.nav-link-icon),
  :deep(.nav-item-icon),
  :deep(.v-list-item__prepend img) { width: 18px !important; height: 18px !important; }
  :deep(.nav-link-title),
  :deep(.nav-item-title),
  :deep(.v-list-item-title) { font-size: 12px !important; }
}
</style>