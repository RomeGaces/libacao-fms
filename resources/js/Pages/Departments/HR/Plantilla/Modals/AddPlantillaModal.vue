<template>
    <Modal class="add-plantilla-modal grid" v-model="showAddPlantillaModal" title="Add Plantilla" size="md" height="md">
        <form class="form-section">
            <div class="form-grid">
                <div class="input-wrapper">
                    <label>Department</label>
                    <Input type="text" />
                </div>
                <div class="input-wrapper">
                    <label>Division</label>
                    <Input type="text" />
                </div>
                <div class="input-wrapper span-2">
                    <label>Position Title</label>
                    <Input type="text" />
                </div>
                <div class="input-wrapper span-1">
                    <label>Salary Grade</label>
                    <Input type="text" />
                </div>
                <div>
                    <label>Status</label>
                    <select>
                        <option value="">Select</option>
                        <option value="">Permanent</option>
                        <option value="">Contractual</option>
                        <option value="">Job Order</option>
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

    const showAddPlantillaModal = ref(false);
</script>
