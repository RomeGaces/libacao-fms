<template>
    <Modal 
        class="plantilla-add-div-modal grid" 
        v-model="showAddDivModal" 
        :title="editMode ? 'Edit Division' : 'Add Division'" 
        size="md" 
        height="md"
    >
        <form class="form-section">
            <div class="form-grid">
                <div class="input-wrapper span-2" style="position: relative;">
                    <label>Department</label>
                    <div v-if="isEmbedded">
                        <Input 
                            type="text" 
                            :value="departmentSearch" 
                            readonly 
                            :disabled="true"
                        />
                    </div>
                    <div v-else>
                        <input 
                            type="text" 
                            v-model="departmentSearch" 
                            placeholder="Select Department" 
                            @focus="showDropdown = true"
                            @blur="hideDropdownWithDelay"
                            class="searchable-select"
                        />
                        <div v-if="showDropdown" class="dropdown">
                            <div 
                                v-for="dept in filteredDepartments" 
                                :key="dept.id" 
                                class="dropdown-item"
                                @mousedown.prevent="selectDepartment(dept)"
                            >
                                {{ dept.department_name }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="input-wrapper span-2">
                    <label>Division Name</label>
                    <Input type="text" v-model="division.division_name" placeholder="Division Name" />
                </div>
            </div>
        </form>
        <template #footer>
            <Button variant="secondary" size="md" @click="closeModal">Cancel</Button>
            <Button variant="primary" size="md" @click="saveDivision">Save</Button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import Modal from "@/components/Common/Modal.vue";
import Button from "@/components/Common/Button.vue";
import Input from "@/components/Common/Input.vue";
import axios from "axios";
import NotificationService from "@/Services/NotificationService"; // 🚨 ADDED

const props = defineProps({
    division: { type: Object, default: () => ({ id: null, department_id: '', division_name: '' }) },
    editMode: { type: Boolean, default: false },
    modelValue: { type: Boolean, default: false },
    departmentId: { type: [String, Number], default: '' }, // 🚨 ADDED
    isEmbedded: { type: Boolean, default: false }, // 🚨 ADDED
});
const emit = defineEmits(['update:modelValue', 'saved']);

const departments = ref([]);
const division = ref({ id: null, department_id: '', division_name: '' });
const departmentSearch = ref('');
const showDropdown = ref(false);

const showAddDivModal = computed({
    get: () => props.modelValue,
    set: val => emit('update:modelValue', val)
});

const fetchDepartments = async () => {
    try {
        const res = await axios.get("/api/departments");
        departments.value = res.data;
    } catch (err) {
        console.error(err);
        NotificationService.error("Failed to load departments data."); // 🚨 ADDED
    }
};

watch(
    () => props.division,
    (newDiv) => {
        division.value = { 
            id: newDiv?.id || null, 
            department_id: newDiv?.department_id || '', 
            division_name: newDiv?.division_name || '' 
        };
        departmentSearch.value = departments.value.find(d => d.id === division.value.department_id)?.department_name || '';
    },
    { immediate: true }
);

// 🚨 ADDED WATCHER: Handles setting and locking the department when embedded
watch(
    () => [props.departmentId, props.isEmbedded, props.modelValue],
    ([deptId, isEmbedded, isVisible]) => {
        if (isEmbedded && isVisible && deptId) {
            division.value.department_id = deptId;
            departmentSearch.value = departments.value.find(d => d.id === deptId)?.department_name || '';
        } else if (!isEmbedded && isVisible) {
            // Reset for standalone use if not editing
            if (!props.editMode) {
                departmentSearch.value = '';
                division.value.department_id = '';
            }
        }
    },
    { immediate: true }
);


const filteredDepartments = computed(() => {
    if (!departmentSearch.value) return departments.value;
    return departments.value.filter(d =>
        d.department_name.toLowerCase().includes(departmentSearch.value.toLowerCase())
    );
});

const selectDepartment = (dept) => {
    division.value.department_id = dept.id;
    departmentSearch.value = dept.department_name;
    showDropdown.value = false;
};

const hideDropdownWithDelay = () => {
    setTimeout(() => showDropdown.value = false, 150);
};

const saveDivision = async () => {
    if (!division.value.division_name || !division.value.department_id) {
        NotificationService.error("Please select a Department and enter a Division Name."); // 🚨 ADDED VALIDATION FEEDBACK
        return;
    }

    try {
        let res;
        if (props.editMode && division.value.id) {
            res = await axios.put(`/api/divisions/${division.value.id}`, {
                department_id: division.value.department_id,
                division_name: division.value.division_name
            });
        } else {
            res = await axios.post('/api/divisions', {
                department_id: division.value.department_id,
                division_name: division.value.division_name
            });
        }

        emit('saved', res.data);
        showAddDivModal.value = false;
        NotificationService.success(props.editMode ? 'Division updated successfully.' : 'Division added successfully.'); // 🚨 ADDED SUCCESS NOTIFICATION
    } catch (err) {
        console.error(err);
        NotificationService.error("Failed to save division."); // 🚨 ADDED ERROR NOTIFICATION
    }
};

const closeModal = () => {
    if (!props.editMode) {
        division.value = { id: null, department_id: '', division_name: '' };
        departmentSearch.value = '';
    }
    showDropdown.value = false;
    showAddDivModal.value = false; 
};

onMounted(fetchDepartments);
</script>

<style scoped>
.dropdown {
    border: 1px solid #ccc;
    max-height: 180px;
    overflow-y: auto;
    background-color: #fff;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    border-radius: 4px;
    margin-top: 2px;
}

.dropdown-item {
    padding: 6px 10px;
    cursor: pointer;
    font-size: 14px;
}

.dropdown-item:hover {
    background-color: #f0f0f0;
}
</style>