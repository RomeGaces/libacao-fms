<template>
    <Modal
      class="change-password-modal"
      v-model="showChangePasswordModal"
      title="Change Password"
      size="sm"
      height="sm"
    >
      <!-- ✅ validateOnInput makes errors disappear on typing -->
      <Form @submit="onSubmit" id="change-pass-form" validateOnInput>
        <div class="new-pass">
          <label>New Password</label>
          <Field
            name="new_password"
            v-model="newPassword"
            type="password"
            placeholder="Enter new password"
            class="input"
            :rules="validateNewPass"
          />
          <ErrorMessage name="new_password" class="error input-error-msg" />
        </div>
  
        <div>
          <label>Confirm Password</label>
          <Field
            name="confirm_password"
            v-model="confirmPassword"
            type="password"
            placeholder="Confirm password"
            class="input"
            :rules="validateConPass"
          />
          <ErrorMessage name="confirm_password" class="error input-error-msg" />
        </div>
      </Form>
  
      <template #footer>
        <Button
          variant="secondary"
          size="md"
          type="submit"
          form="change-pass-form"
        >
          Change Password
        </Button>
      </template>
    </Modal>
  </template>
  
  <script setup>
  import { ref } from "vue";
  import Modal from "@/components/Common/Modal.vue";
  import Button from "@/components/Common/Button.vue";
  import { Form, Field, ErrorMessage } from "vee-validate";
  
  const showChangePasswordModal = ref(false);
  const newPassword = ref("");
  const confirmPassword = ref("");
  
  // Validation rules
  const validateNewPass = (value) => {
    if (!value) return "New password is required";
    return true;
  };
  
  const validateConPass = (value) => {
    if (!value) return "Please confirm your password";
    if (value !== newPassword.value) return "Passwords do not match";
    return true;
  };
  
  // Form submit
  const onSubmit = (values) => {
    console.log("Password changed!", values);
    alert("Password successfully changed!");
    newPassword.value = "";
    confirmPassword.value = "";
    close();
  };
  
  // modal controls
  const open = () => (showChangePasswordModal.value = true);
  const close = () => (showChangePasswordModal.value = false);
  
  defineExpose({ open, close });
  </script>
  