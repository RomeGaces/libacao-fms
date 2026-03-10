<template>
    <Modal class="add-account-modal grid" v-model="showAddAccountModal" title="Add Account" size="md" height="md">
        <form class="form-section">
            <div class="form-grid">
                <div class="input-wrapper">
                    <label>Fullname</label>
                    <Input type="text" />
                </div>
                <div class="input-wrapper">
                    <label>Mobile/Contact Number</label>
                    <Input type="text" />
                </div>
                <div class="input-wrapper span-2">
                    <label>Email Address</label>
                    <Input type="text" />
                </div>
                <div class="input-wrapper span-1">
                    <label>Date of Birth</label>
                    <Input type="date" />
                </div>
                <div>
                    <label>Access Level</label>
                    <select>
                        <option value="">Admin</option>
                        <option value="">Supervisor</option>
                        <option value="">Staff Recruitment</option>
                        <option value="">Staff Attendance and Payroll</option>
                        <option value="">Staff Record</option>
                        <option value="">Staff Leave</option>
                        <option value="">Staff Learning</option>
                        <option value="">Staff Compensation</option>
                    </select>
                </div>
            </div>
        </form>
        <template #footer>
            <Button variant="secondary" size="md" @click="$emit('update:modelValue', false)">Cancel</Button>
            <Button variant="primary" size="md">Save</Button>
        </template>
    </Modal>
</template>

<script setup>
    import { ref } from "vue";
    import Modal from "@/components/Common/Modal.vue";
    import Button from "@/components/Common/Button.vue";
    import Input from "@/Components/Common/Input.vue";

    const showAddAccountModal = ref(false);
</script>
