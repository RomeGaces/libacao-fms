<template>
    <Modal class="plantilla-add-dept-modal grid" v-model="modalVisible" :title="editMode ? 'Edit Department' : 'Add Department'" size="md" height="md">
        <form class="form-section" @submit.prevent="saveDepartment">
            <div class="form-grid">
                <div class="input-wrapper span-2">
                    <label>Department Name</label>
                    <Input type="text" v-model="departmentName" />
                    <span v-if="errors.department_name" class="error-text">{{ errors.department_name[0] }}</span>
                </div>
            </div>
        </form>
        <template #footer>
            <Button variant="secondary" size="md" @click="closeModal">Cancel</Button>
            <Button variant="primary" size="md" @click="saveDepartment">{{ editMode ? 'Update' : 'Save' }}</Button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import Modal from "@/components/Common/Modal.vue";
import Button from "@/components/Common/Button.vue";
import Input from "@/components/Common/Input.vue";
import axios from "axios";

const props = defineProps({
    modelValue: Boolean,
    department: Object,
    editMode: Boolean
});

const emit = defineEmits(["update:modelValue", "saved"]);

const showAddDeptModal = ref(false);
const departmentName = ref("");
const errors = ref({});

const modalVisible = computed({
    get: () => props.modelValue,
    set: (val) => emit("update:modelValue", val),
});

watch(() => props.modelValue, val => showAddDeptModal.value = val);
watch(() => props.department, val => departmentName.value = val ? val.department_name : "");

const closeModal = () => {
    errors.value = {};
    emit("update:modelValue", false);
};

const saveDepartment = async () => {
    try {
        let response;
        if (props.editMode && props.department) {
            response = await axios.put(`/api/departments/${props.department.id}`, { department_name: departmentName.value });
        } else {
            response = await axios.post("/api/departments", { department_name: departmentName.value });
        }
        emit("saved", response.data);
        closeModal();
    } catch (err) {
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors;
        }
    }
};
</script>

<style scoped>
.error-text { color: red; font-size: 0.85rem; }
</style>
