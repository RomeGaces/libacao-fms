<template>
  <header class="header">
    <button class="menu-btn" @click="toggleSidebar">☰</button>

    <div class="logo">
      <img src="images/logo.png" alt="Logo" />
      <div class="logo-text">
        <strong>HRMS</strong>
        <small>LGU LIBACAO</small>
      </div>
    </div>

    <Dropdown placement="bottom-right">
      <template #trigger>
        <div class="user" ref="userDiv">
          <span>Hello, {{ user?.name }}</span>
          <img src="images/profile.webp" alt="User Avatar" />
        </div>
      </template>
      <!-- Dropdown menu items -->
      <a href="#" @click.prevent="openModal">Change Password</a>
      <a href="#" @click.prevent="logout">Logout</a>
    </Dropdown>

    <!-- Change Password Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="change-password-modal">
        <h3>Change Password</h3>

        <div class="form-group">
          <label>Current Password</label>
          <input type="password" v-model="form.current_password" placeholder="Enter current password" />
        </div>

        <div class="form-group">
          <label>New Password</label>
          <input type="password" v-model="form.new_password" placeholder="Enter new password" />
        </div>

        <div class="form-group">
          <label>Confirm New Password</label>
          <input type="password" v-model="form.new_password_confirmation" placeholder="Confirm new password" />
        </div>

        <div class="modal-actions">
          <button class="save-btn" @click="changePassword">Save</button>
          <button class="cancel-btn" @click="closeModal">Cancel</button>
        </div>

        <p v-if="error" class="error">{{ error }}</p>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import Dropdown from "@/components/Common/Dropdown.vue";

const showModal = ref(false);
const error = ref("");

const form = ref({
  current_password: "",
  new_password: "",
  new_password_confirmation: ""
});

// Correctly read reactive user from Inertia props
const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const emit = defineEmits(["toggle-sidebar"]);

function openModal() {
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  error.value = "";
}

function toggleSidebar() {
  emit("toggle-sidebar");
}

async function logout() {
  try {
    await axios.post("/logout");
    window.location.href = "/login";
  } catch (error) {
    console.error("Logout failed:", error);
  }
}

async function changePassword() {
  try {
    await axios.post("/change-password", form.value);

    alert("Password updated successfully");

    closeModal();

    form.value = {
      current_password: "",
      new_password: "",
      new_password_confirmation: ""
    };
  } catch (err) {
    error.value = err.response?.data?.message || "Password update failed";
  }
}
</script>
<style>
.modal-overlay {
  position: fixed;

  top: 0;
  left: 0;
  right: 0;
  bottom: 0;

  width: 100vw;
  height: 100vh;

  display: flex;
  justify-content: center;
  align-items: center;

  background: rgba(0,0,0,0.35);
  z-index: 9999;
}
.change-password-modal {
  position:relative;
  background: white;
  width: 420px;
  max-width: 90%;

  padding: 30px;
  border-radius: 10px;

  box-shadow: 0 10px 30px rgba(0,0,0,0.2);

  display: flex;
  flex-direction: column;
  gap: 12px;

  height: auto;       /* IMPORTANT */
  max-height: 90vh;   /* prevents overflow */
}
.form-group {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.form-group input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.save-btn {
  background: #409eff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
}

.cancel-btn {
  background: #ccc;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
}

.error {
  color: red;
  margin-top: 10px;
}

.modal input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
</style>