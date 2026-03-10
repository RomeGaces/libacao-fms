<template>
    <Modal v-model="showForgotPasswordModal" title="Forgot Password" size="sm" height="xs">
      <div class="input-group">
        <label>Employee ID</label>
        <Input
          v-model="employeeId"
          size="md"
          @input="clearError"
        />
        <span v-if="error" class="error input-error-msg">{{ error }}</span>
      </div>
  
      <template #footer>
        <Button variant="secondary" size="md" @click="handleSubmit">
          Change Password
        </Button>
      </template>
    </Modal>
  </template>
  
  <script setup>
  import { ref } from "vue";
  import Modal from "@/components/Common/Modal.vue";
  import Button from "@/components/Common/Button.vue";
  import Input from "@/components/Common/Input.vue";
  
  const showForgotPasswordModal = ref(false);
  const employeeId = ref("");
  const error = ref("");
  
  // Open / close modal
  const open = () => (showForgotPasswordModal.value = true);
  const close = () => (showForgotPasswordModal.value = false);
  
  // Clear error while typing
  const clearError = () => {
    if (employeeId.value) error.value = "";
  };
  
  // Handle submit
  const handleSubmit = () => {
    if (!employeeId.value) {
      error.value = "Employee ID is required";
      return;
    }
  
    console.log("Employee ID submitted:", employeeId.value);
    // Call API here
  
    close();
    employeeId.value = "";
    error.value = "";
  };
  
  defineExpose({ open, close });
  </script>

  