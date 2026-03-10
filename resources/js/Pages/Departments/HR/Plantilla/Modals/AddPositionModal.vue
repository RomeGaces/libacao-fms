<template>
    <Modal class="plantilla-add-position-modal grid" v-model="showAddPositionModal" title="Add Position" size="md" height="md">
        <form class="form-section">
            <div class="form-grid">
                <div class="input-wrapper">
                    <label>Plantilla Item No.</label>
                    <Input v-model="form.plantilla_item_number" type="text" />
                </div>

                <div>
                    <label>Salary Grade</label>
                    <select v-model="form.salary_grade" @change="onGradeChange">
                        <option value="">Select</option>
                        <option v-for="s in salarySchedules" :key="s.salary_grade" :value="s.salary_grade">
                            {{ s.salary_grade }}
                        </option>
                    </select>
                </div>

                <div>
                    <label>Step</label>
                    <select v-model="form.step" @change="onStepChange">
                        <option value="">Select</option>
                        <option v-for="step in steps" :key="step" :value="step">{{ step }}</option>
                    </select>
                </div>

                <div>
                    <label>Monthly Rate</label>
                    <Input :value="computedMonthlyRate" type="text" readonly />
                </div>

                <div>
                    <label>Eligibility</label>
                    <select v-model="form.eligibility_requirement">
                        <option value="">Select</option>
                        <option>First Level</option>
                        <option>Second Level</option>
                    </select>
                </div>

                <div class="input-wrapper span-2">
                    <label>Department</label>
                    <select v-model="form.department_id" @change="onDepartmentChange">
                        <option value="">Select</option>
                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.department_name }}</option>
                    </select>
                </div>

                <div class="input-wrapper span-2">
                    <label>Division</label>
                    <select v-model="form.division_id" @change="handleDivisionSelect" :key="divisionSelectKey">
                        <option value="">Select</option>
                        <option v-for="div in filteredDivisions" :key="div.id" :value="div.id">{{ div.division_name }}</option>
                        <option value="add_new">+ Add Division</option>
                    </select>
                </div>

                <div class="input-wrapper span-2">
                    <label>Educational Requirement</label>
                    <select v-model="form.educational_requirement">
                        <option value="">Select</option>
                        <option>Elementary Graduate</option>
                        <option>Highschool Graduate</option>
                        <option>College Graduate</option>
                    </select>
                </div>

                <div class="input-wrapper span-2">
                    <label>Experience</label>
                    <Input v-model="form.experience" type="text" />
                </div>
            </div>
        </form>

        <template #footer>
            <Button variant="secondary" size="md" @click="closeModal">Cancel</Button>
            <Button variant="primary" size="md" @click="save">Save</Button>
        </template>

        <AddDivisionModal 
            v-model="showAddDivisionModal" 
            :department-id="form.department_id" 
            :is-embedded="true"
            @saved="onDivisionAdded"
        />
    </Modal>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from "vue";
import axios from "axios";

import Modal from "@/components/Common/Modal.vue";
import Button from "@/components/Common/Button.vue";
import Input from "@/components/Common/Input.vue";
import AddDivisionModal from "./AddDivisionModal.vue";
import NotificationService from "@/Services/NotificationService";

const props = defineProps({
  modelValue: Boolean,
  editData: Object,
});
const emit = defineEmits(["update:modelValue", "saved"]);

const showAddPositionModal = computed({
  get: () => props.modelValue,
  set: (val) => emit("update:modelValue", val),
});

const form = reactive({
  plantilla_item_number: "",
  salary_grade: "",
  step: "",
  eligibility_requirement: "",
  educational_requirement: "",
  department_id: "",
  division_id: "",
  experience: "",
});

const salarySchedules = ref([]);
const departments = ref([]);
const divisions = ref([]);
const steps = ref([]);
const showAddDivisionModal = ref(false);
const divisionSelectKey = ref(0);

watch(
  () => props.editData,
  (val) => {
    if (val) {
      Object.assign(form, { ...val });
      steps.value = val.salary_grade ? Array.from({ length: 8 }, (_, i) => i + 1) : [];
    } else {
      Object.keys(form).forEach((key) => (form[key] = ""));
      steps.value = [];
    }
  },
  { immediate: true }
);

watch(
  showAddPositionModal, 
  (isVisible) => {
    if (isVisible) {
      loadDivisions();
    }
  }
);

const filteredDivisions = computed(() =>
  divisions.value.filter((d) => d.department_id === form.department_id)
);
const computedMonthlyRate = computed(() => {
  const grade = salarySchedules.value.find((s) => s.salary_grade == form.salary_grade);
  const rate = grade && form.step ? grade[`step_${form.step}`] : null;

  if (rate !== null) {
      return Number(rate).toLocaleString('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
      });
  }
  return "";
});

const loadSalarySchedules = async () => {
  try {
    const res = await axios.get("/api/salary-schedules");
    salarySchedules.value = res.data;
  } catch {
    NotificationService.error("Failed to load salary schedules.");
  }
};
const loadDepartments = async () => {
  try {
    const res = await axios.get("/api/departments");
    departments.value = res.data;
  } catch {
    NotificationService.error("Failed to load departments.");
  }
};
const loadDivisions = async () => {
  try {
    const res = await axios.get("/api/divisions");
    divisions.value = res.data;
  } catch {
    NotificationService.error("Failed to load divisions.");
  }
};

const onGradeChange = () => {
  steps.value = [];
  form.step = "";
  if (form.salary_grade) steps.value = Array.from({ length: 8 }, (_, i) => i + 1);
};
const onStepChange = () => {};
const onDepartmentChange = () => (form.division_id = "");

const handleDivisionSelect = () => {
  if (form.division_id === "add_new") {
    if (!form.department_id) {
        NotificationService.error("Please select a Department first before adding a new Division.");
        form.division_id = "";
        return;
    }
    showAddDivisionModal.value = true;
    form.division_id = "";
  }
};

const onDivisionAdded = async (res) => {
  const division = res.data || res; // Handles different API response structures
  if (!division || !division.id) return;

  await loadDivisions(); // Reload divisions to get the new entry
  form.division_id = division.id; // Select the newly added division
};

const save = async () => {
  if (!form.plantilla_item_number || !form.salary_grade || !form.step || !form.department_id || !form.educational_requirement || !form.eligibility_requirement) {
    NotificationService.error("Please fill out all required fields.");
    return;
  } 

  try {
    let res;
    if (props.editData?.id) {
      res = await axios.put(`/api/plantillas/${props.editData.id}`, form);
    } else {
      res = await axios.post('/api/plantillas', form);
    }

    emit('saved', res.data.data || res.data);
    NotificationService.success(res.data.message || "Position saved successfully.");
    showAddPositionModal.value = false;
  } catch (err) {
    console.error(err);
    NotificationService.error("Failed to save position.");
  }
};

const closeModal = () => {
  Object.keys(form).forEach((key) => (form[key] = ""));
  showAddPositionModal.value = false;
};

onMounted(() => {
  loadSalarySchedules();
  loadDepartments();
  loadDivisions();
});
</script>