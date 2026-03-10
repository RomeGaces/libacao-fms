<script setup>
import { ref, onMounted } from "vue";
import avatar1 from "@images/avatars/avatar-1.png";
import axios from "axios";
import api from "@fms/utils/api";

const user = ref(null); // hold user data

onMounted(async () => {
  try {
    const { data } = await api.get("/user"); 
    user.value = data;
    localStorage.setItem("user_info", JSON.stringify(data));
  } catch (err) {
    console.error("Failed to fetch user:", err.response?.data || err.message);
  }
});

async function logout() {
  try {
    await axios.post("/logout");
  } catch (error) {
    console.error("Logout failed:", error.response?.data || error.message);
  } finally {
    // always clear token & redirect
    localStorage.removeItem("api_token");
    localStorage.removeItem("user_info");
    window.location.href = "/login";
  }
}
</script>

<template>
  <VBadge
    dot
    location="bottom right"
    offset-x="3"
    offset-y="3"
    color="success"
    bordered
  >
    <VAvatar
      class="cursor-pointer"
      color="primary"
      variant="tonal"
    >
      <VImg :src="avatar1" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="14px"
      >
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                  >
                    <VImg :src="avatar1" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ user?.name || "Loading..." }}
            </VListItemTitle>
            <VListItemSubtitle>
              {{ user?.is_admin ? "Admin" : "User" }}
            </VListItemSubtitle>
          </VListItem>

          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="bx-user" size="22" />
            </template>
            <VListItemTitle>Profile</VListItemTitle>
          </VListItem>

          <!-- 👉 Settings -->
          <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="bx-cog" size="22" />
            </template>
            <VListItemTitle>Settings</VListItemTitle>
          </VListItem>

          <!-- 👉 Pricing -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="bx-dollar" size="22" />
            </template>
            <VListItemTitle>Pricing</VListItemTitle>
          </VListItem> -->

          <!-- 👉 FAQ -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="bx-help-circle" size="22" />
            </template>
            <VListItemTitle>FAQ</VListItemTitle>
          </VListItem> -->

          <!-- Divider -->
          <VDivider class="my-2" />

          <!-- 👉 Logout -->
          <VListItem @click="logout">
            <template #prepend>
              <VIcon class="me-2" icon="bx-log-out" size="22" />
            </template>
            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
