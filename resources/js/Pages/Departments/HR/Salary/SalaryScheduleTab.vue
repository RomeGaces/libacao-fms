<template>
    <section>
        <div class="salary-schedule-log-header">
            <Input class="search-inp" name="search" placeholder="Type salary grade..." size="md" v-model="searchQuery" @input="filterSchedules" />
            <div class="button-group">
                <Button variant="secondary" @click="showUploadModal = true"><img class="icon-upload" src="images/icons/upload.png" /> Upload Salary Schedule</Button>
                <Button variant="primary" :disabled="!hasChanges" @click="confirmSave"><img class="icon-save" src="images/icons/diskette.png" /> Save Changes</Button>
            </div>
        </div>
        <div class="salary-schedule-table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Salary Grade</th>
                        <!-- Steps header -->
                        <th>Step 1</th>
                        <th>Step 2</th>
                        <th>Step 3</th>
                        <th>Step 4</th>
                        <th>Step 5</th>
                        <th>Step 6</th>
                        <th>Step 7</th>
                        <th>Step 8</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="schedule in filteredSchedules" :key="schedule.id">
                        <td class="salary-grade">{{ schedule.salary_grade }}</td>
                        <td><Input v-model.number="schedule.step_1" size="sm" type="number" @input="onFieldChange" /></td>
                        <td><Input v-model.number="schedule.step_2" size="sm" type="number" @input="onFieldChange" /></td>
                        <td><Input v-model.number="schedule.step_3" size="sm" type="number" @input="onFieldChange" /></td>
                        <td><Input v-model.number="schedule.step_4" size="sm" type="number" @input="onFieldChange" /></td>
                        <td><Input v-model.number="schedule.step_5" size="sm" type="number" @input="onFieldChange" /></td>
                        <td><Input v-model.number="schedule.step_6" size="sm" type="number" @input="onFieldChange" /></td>
                        <td><Input v-model.number="schedule.step_7" size="sm" type="number" @input="onFieldChange" /></td>
                        <td><Input v-model.number="schedule.step_8" size="sm" type="number" @input="onFieldChange" /></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Confirmation Modal -->
        <Modal v-model="showModal" title="Confirm Update" size="sm">
            <p>Are you sure you want to update the data?</p>
            <template #footer>
                <Button variant="secondary" @click="showModal = false">Cancel</Button>
                <Button variant="primary" @click="saveChanges">YES</Button>
            </template>
        </Modal>

        <!-- Upload Modal -->
        <Modal v-model="showUploadModal" title="Upload Salary Schedule" size="sm">
            <div class="upload-wrapper">
                <input type="file" ref="fileInput" accept=".csv" @change="handleFileUpload" />
                <div class="upload-actions">
                    <Button variant="outline" @click="downloadTemplate">
                        <img class="icon-download" src="images/icons/download.png" /> Download Template
                    </Button>
                </div>
            </div>
            <template #footer>
                <Button variant="secondary" @click="showUploadModal = false">Cancel</Button>
                <Button variant="primary" @click="submitUpload">Upload</Button>
            </template>
        </Modal>

        <!-- Notification -->
        <Notification
            v-if="notification.visible"
            :type="notification.type"
            :message="notification.message"
            :duration="notification.duration"
            position="top-right"
            @close="notification.visible = false"
        />
    </section>
</template>

<script setup>
import Button from "@/Components/Common/Button.vue";
import Input from "@/Components/Common/Input.vue";
import Modal from "@/Components/Common/Modal.vue";
import Notification from "@/Components/Common/Notification.vue";
import { ref, onMounted } from "vue";
import axios from "axios";

const salarySchedules = ref([]);
const originalSchedules = ref([]);
const filteredSchedules = ref([]);
const searchQuery = ref("");
const hasChanges = ref(false);
const showModal = ref(false);
const showUploadModal = ref(false);
const fileInput = ref(null);
const selectedFile = ref(null);

const notification = ref({
    visible: false,
    message: "",
    type: "info",
    duration: 3000,
});

const showNotification = (message, type = "info") => {
    notification.value = {
        visible: true,
        message,
        type,
        duration: 3000,
    };
};

const fetchSalarySchedules = async () => {
    try {
        const { data } = await axios.get("/api/salary-schedules");
        salarySchedules.value = JSON.parse(JSON.stringify(data));
        originalSchedules.value = JSON.parse(JSON.stringify(data));
        filteredSchedules.value = salarySchedules.value;
        hasChanges.value = false;
    } catch (err) {
        console.error("Failed to load salary schedules:", err);
        showNotification("Failed to load salary schedules.", "error");
    }
};

const filterSchedules = () => {
    if (!searchQuery.value) {
        filteredSchedules.value = salarySchedules.value;
    } else {
        filteredSchedules.value = salarySchedules.value.filter(schedule =>
            schedule.salary_grade.toString().includes(searchQuery.value)
        );
    }
};

const onFieldChange = () => {
    hasChanges.value = JSON.stringify(salarySchedules.value) !== JSON.stringify(originalSchedules.value);
};

const confirmSave = () => {
    if (hasChanges.value) {
        showModal.value = true;
    }
};

const saveChanges = async () => {
    try {
        await axios.put("/api/salary-schedules", salarySchedules.value);
        await fetchSalarySchedules();
        showModal.value = false;
        showNotification("Salary schedules successfully updated!", "success");
    } catch (error) {
        console.error("Error updating schedules:", error);
        showNotification("Failed to update salary schedules.", "error");
    }
};

// ===================== UPLOAD FEATURE =====================
const handleFileUpload = (event) => {
    selectedFile.value = event.target.files[0];
};

const submitUpload = async () => {
if (!selectedFile.value) {
    showNotification("Please select a file to upload.", "warning");
    return;
}

const formData = new FormData();
formData.append("file", selectedFile.value);

try {
    await axios.post("/api/salary-schedules/upload", formData, {
    headers: { "Content-Type": "multipart/form-data" },
    });
    await fetchSalarySchedules();
    showUploadModal.value = false;
    showNotification("Salary schedule imported successfully!", "success");
} catch (error) {
    console.error("Error importing file:", error);
    showNotification("Failed to import salary schedule.", "error");
}
};

const downloadTemplate = async () => {
    try {
        const response = await axios.get("/api/salary-schedules/template", {
            responseType: "blob",
        });
        const blob = new Blob([response.data], { type: "text/csv" });
        const link = document.createElement("a");
        link.href = window.URL.createObjectURL(blob);
        link.download = "salary_schedule_template.csv";
        link.click();
        showNotification("Template downloaded successfully!", "success");
    } catch (error) {
        console.error("Error downloading template:", error);
        showNotification("Failed to download template.", "error");
    }
};

onMounted(fetchSalarySchedules);
</script>

<style scoped>
.upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    text-align: center;
}
.upload-wrapper input[type="file"] {
    margin: 0 auto;
}
.upload-actions {
    display: flex;
    justify-content: center;
}
</style>
