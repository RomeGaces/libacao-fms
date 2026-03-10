<template>
    <section id="deptdiv">
        <div class="deptdiv-table-container">
            <!-- Left Table -->
            <div class="deptdiv-table-wrapper">
                <h4>DEPARTMENT</h4>
                <div class="deptdiv-header">
                    <Input class="search-inp" v-model="searchQuery" placeholder="Search ..." size="md" />
                    <Button variant="primary" @click="openAddDeptModal">
                        <img class="icon-add" src="images/icons/plus.png" /> Add Department
                    </Button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="dept in filteredDepartments" :key="dept.id">
                            <td>{{ dept.department_name }}</td>
                            <td>
                                <Button class="btn-action" variant="secondary" size="xs" @click="openEditDeptModal(dept)">
                                    <img class="icon" src="images/icons/edit.png" />
                                </Button>
                                <Button v-if="!isProtected(dept)" class="btn-action" variant="danger" size="xs" @click="openDeleteModal(dept)">
                                    <img class="icon" src="images/icons/delete.png" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Right Table -->
            <div class="deptdiv-table-wrapper">
                <h4>DIVISION</h4>
                <div class="deptdiv-header">
                    <Input class="search-inp" v-model="divisionSearchQuery" placeholder="Search ..." size="md" />
                    <Button variant="primary" @click="openAddDivModal">
                        <img class="icon-add" src="images/icons/plus.png" /> Add Division
                    </Button>
                </div>

                <div v-for="dept in groupedDepartmentsWithDivisions" :key="dept.id" class="department-group">
                    <h5 class="department-title">{{ dept.department_name }}</h5>
                    <table>
                        <thead>
                            <tr>
                                <th>Division</th>
                                <th>Plantilla</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="div in dept.divisions" :key="div.id">
                                <td>{{ div.division_name }}</td>
                                <td>
                                    <ul>
                                        <li>No Plantilla</li>
                                    </ul>
                                </td>
                                <td>
                                    <Button class="btn-action" variant="secondary" size="xl" @click="openAddPlantillaModal">Add Plantilla</Button>
                                    <Button class="btn-action" variant="secondary" size="xs" @click="openEditDivModal(div)">
                                        <img class="icon" src="images/icons/edit.png" />
                                    </Button>
                                    <Button class="btn-action" variant="danger" size="xs" @click="openDeleteDivModal(div)">
                                        <img class="icon" src="images/icons/delete.png" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <AddDeptModal v-model="showAddDeptModal" @saved="addDepartment" :department="selectedDept" :edit-mode="isEditMode" />
        <AddDivisionModal v-model="showAddDivModal" @saved="addDivision" :division="selectedDiv" :edit-mode="isEditDivMode" />
        <AddPlantillaModal v-model="showAddPlantillaModal" />

        <!-- Delete Confirmation Modal for Departments -->
        <Modal v-model="showDeleteModal" title="Confirm Delete" size="sm" height="xs">
            <template #default>
                <p>Are you sure you want to delete <strong>{{ deleteTarget?.department_name }}</strong>?</p>
            </template>
            <template #footer>
                <Button variant="secondary" size="md" @click="closeDeleteModal">Cancel</Button>
                <Button variant="primary" size="md" @click="deleteDepartment">Delete</Button>
            </template>
        </Modal>

        <!-- Delete Confirmation Modal for Divisions -->
        <Modal v-model="showDeleteDivModal" title="Confirm Delete" size="sm" height="xs">
            <template #default>
                <p>Are you sure you want to delete <strong>{{ deleteDivTarget?.division_name }}</strong>?</p>
            </template>
            <template #footer>
                <Button variant="secondary" size="md" @click="closeDeleteDivModal">Cancel</Button>
                <Button variant="primary" size="md" @click="deleteDivision">Delete</Button>
            </template>
        </Modal>

        <Notification
            v-for="(notif, index) in notifications"
            :key="index"
            :type="notif.type"
            :message="notif.message"
            @close="removeNotification(index)"
        />
    </section>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import Button from "@/Components/Common/Button.vue";
import Input from "@/Components/Common/Input.vue";
import AddDeptModal from "../Modals/AddDeptModal.vue";
import AddDivisionModal from "../Modals/AddDivisionModal.vue";
import AddPlantillaModal from "../Modals/AddPlantillaModal.vue";
import Modal from "@/Components/Common/Modal.vue";
import Notification from "@/Components/Common/Notification.vue";
import axios from "axios";

const showAddDeptModal = ref(false);
const showAddDivModal = ref(false);
const showAddPlantillaModal = ref(false);
const showDeleteModal = ref(false);
const showDeleteDivModal = ref(false);

const departments = ref([]);
const divisions = ref([]);
const searchQuery = ref("");
const divisionSearchQuery = ref("");
const notifications = ref([]);

const selectedDept = ref(null);
const selectedDiv = ref(null);
const isEditMode = ref(false);
const isEditDivMode = ref(false);

const deleteTarget = ref(null);
const deleteDivTarget = ref(null);
const protectedCodes = ['LM001','LM005','LM006','LM007'];
const isProtected = (dept) => protectedCodes.includes(dept.department_code);

const openAddDeptModal = () => {
    selectedDept.value = null;
    isEditMode.value = false;
    showAddDeptModal.value = true;
};

const openEditDeptModal = (dept) => {
    selectedDept.value = dept;
    isEditMode.value = true;
    showAddDeptModal.value = true;
};

const closeDeleteModal = () => {
    deleteTarget.value = null;
    showDeleteModal.value = false;
};

const openDeleteModal = (dept) => {
    deleteTarget.value = dept;
    showDeleteModal.value = true;
};

const openAddDivModal = () => {
    selectedDiv.value = null;
    isEditDivMode.value = false;
    showAddDivModal.value = true;
};

const openEditDivModal = (div) => {
    selectedDiv.value = div;
    isEditDivMode.value = true;
    showAddDivModal.value = true;
};

const closeDeleteDivModal = () => {
    deleteDivTarget.value = null;
    showDeleteDivModal.value = false;
};

const openDeleteDivModal = (div) => {
    deleteDivTarget.value = div;
    showDeleteDivModal.value = true;
};

const fetchDepartments = async () => {
    try {
        const response = await axios.get("/api/departments");
        departments.value = response.data;
    } catch (err) {
        notify("Failed to fetch departments", "error");
    }
};

const fetchDivisions = async () => {
    try {
        const response = await axios.get("/api/divisions");
        divisions.value = response.data;
    } catch (err) {
        notify("Failed to fetch divisions", "error");
    }
};

const addDepartment = (newDept) => {
    if (isEditMode.value) {
        const index = departments.value.findIndex(d => d.id === newDept.id);
        departments.value[index] = newDept;
        notify("Department updated successfully", "success");
    } else {
        departments.value.push(newDept);
        notify("Department added successfully", "success");
    }
};

const addDivision = (newDiv) => {
    try {
        if (isEditDivMode.value) {
            const index = divisions.value.findIndex(d => d.id === newDiv.id);
            if (index !== -1) {
                const dept = departments.value.find(d => d.id === newDiv.department_id);
                divisions.value[index] = { ...newDiv, department: dept };
            }
            notify("Division updated successfully", "success");
        } else {
            const dept = departments.value.find(d => d.id === newDiv.department_id);
            divisions.value.push({ ...newDiv, department: dept });
            notify("Division added successfully", "success");
        }
        showAddDivModal.value = false;
        selectedDiv.value = null;
        isEditDivMode.value = false;
    } catch (err) {
        notify("Failed to save division", "error");
    }
};

const deleteDepartment = async () => {
    if (!deleteTarget.value) return;
    try {
        await axios.delete(`/api/departments/${deleteTarget.value.id}`);
        departments.value = departments.value.filter(d => d.id !== deleteTarget.value.id);
        notify("Department deleted successfully", "success");
    } catch (err) {
        notify("Failed to delete department", "error");
    } finally {
        closeDeleteModal();
    }
};

const deleteDivision = async () => {
    if (!deleteDivTarget.value) return;
    try {
        await axios.delete(`/api/divisions/${deleteDivTarget.value.id}`);
        divisions.value = divisions.value.filter(d => d.id !== deleteDivTarget.value.id);
        notify("Division deleted successfully", "success");
    } catch (err) {
        notify("Failed to delete division", "error");
    } finally {
        closeDeleteDivModal();
    }
};

const notify = (message, type = "info") => notifications.value.push({ message, type });
const removeNotification = (index) => notifications.value.splice(index, 1);

const filteredDepartments = computed(() => {
    if (!searchQuery.value) return departments.value;
    return departments.value.filter(d =>
        d.department_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const groupedDepartmentsWithDivisions = computed(() => {
    return departments.value
        .map(dept => {
            const deptDivisions = divisions.value
                .filter(d => d.department_id === dept.id)
                .filter(d => !divisionSearchQuery.value || d.division_name.toLowerCase().includes(divisionSearchQuery.value.toLowerCase()));
            return { ...dept, divisions: deptDivisions };
        })
        .filter(dept => dept.divisions.length > 0);
});

onMounted(() => {
    fetchDepartments();
    fetchDivisions();
});

const openAddPlantillaModal = () => showAddPlantillaModal.value = true;
</script>

<style scoped>
.department-group {
    margin-bottom: 20px;
}

.department-title {
    background-color: #f7f7f7;
    font-weight: bold;
    padding: 8px 12px;
    margin: 0;
}

.department-row td {
    background-color: #f7f7f7;
    font-weight: bold;
}

td ul {
    margin: 0;
    padding-left: 1em;
    list-style-position: inside;
}

</style>
