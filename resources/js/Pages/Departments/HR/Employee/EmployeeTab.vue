<template>
    <section id="employee">
        <div class="employee-header">
          <input
            type="text"
            v-model="search"
            placeholder="Search employee ..."
            class="search-bar"
          />
          <Button variant="primary" @click="openAddEmployee">
            <img class="icon" src="images/icons/plus.png"/> Add Employee
          </Button>
        </div>
  
        <div class="employee-table-wrapper">
          <table class="employee-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Date of Birth</th>
                <th>Agency Employee No.</th>
                <th>Sex</th>
                <th>Civil Status</th>
                <th>Citizenship</th>
                <th>Department</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="emp in filteredEmployees" :key="emp.id">
                <td>{{ emp.first_name }} {{ emp.last_name }}</td>
                <td>{{ emp.birth_date }}</td>
                <td>{{ emp.agency_employee_no }}</td>
                <td>{{ emp.sex }}</td>
                <td>{{ emp.civil_status }}</td>
                <td>{{ emp.citizenship }}</td>
                <td>{{ emp.department?.department_name }}</td>
                <td>
                  <Tooltip text="View Details" placement="top">
                    <Button class="btn-action" variant="secondary" size="xs" @click="openViewEmployee(emp)">
                      <img class="icon" src="images/icons/view.png"/>
                    </Button>
                  </Tooltip>
                  
                  <Button class="btn-action" variant="secondary" size="xs" @click="openEditEmployee(emp)">
                    <img class="icon" src="images/icons/edit.png"/>
                  </Button>

                  <Button variant="secondary" size="xs" @click="confirmDelete(emp)">
                    <img class="icon" src="images/icons/delete.png"/>
                  </Button>
                </td>
              </tr>
            </tbody>
          </table>
  
          <!-- Modal for Add/Edit Employee -->
          <PDSFormModal ref="pdsModalRef" :formData="selectedEmployee" @saved="fetchEmployees" />
  
          <!-- Modal for View Employee (Page 1) -->
          <PDSFormViewModal ref="viewModalRef" :formData="selectedEmployee" />

          <Modal
            v-model="showDeleteModal"
            title="Confirm Deletion"
            size="sm"
            height="auto"
          >
            <div class="p-3">
              <p>
                Are you sure you want to delete
                <strong>{{ employeeToDelete?.first_name }} {{ employeeToDelete?.last_name }}</strong>?
              </p>
            </div>

            <template #footer>
              <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
              <Button variant="danger" @click="deleteEmployee">Delete</Button>
            </template>
          </Modal>

        </div>
    </section>
</template>
  
<script setup>
import { ref, computed, onMounted } from "vue";
import Button from "@/Components/Common/Button.vue";
import employeeService from "@/Services/employeeService.js";
import PDSFormModal from '@/Components/PDS/PDSFormModal.vue';
import PDSFormViewModal from '@/Components/PDS/View/PDSFormViewModal.vue';
import Tooltip from "@/Components/Common/Tooltip.vue";
import Input from "@/Components/Common/Input.vue";

import Modal from "@/Components/Common/Modal.vue";        
import NotificationService from "@/Services/NotificationService";

const employees = ref([]);
const search = ref("");

const pdsModalRef = ref(null);
const viewModalRef = ref(null);

const selectedEmployee = ref(null);

const showDeleteModal = ref(false);
const employeeToDelete = ref(null);

async function fetchEmployees() {
  try {
    const response = await employeeService.getAll();
    employees.value = response.data;
  } catch (error) {
    console.error("Error fetching employees:", error);
    NotificationService.error("Failed to load employees.");
  }
}

function openAddEmployee() {
  selectedEmployee.value = getEmptyFormData();;
  if (pdsModalRef.value) pdsModalRef.value.open();
}

function openViewEmployee(emp) {
  selectedEmployee.value = emp;
  if (viewModalRef.value) viewModalRef.value.open();
}

function openEditEmployee(emp) {
  selectedEmployee.value = JSON.parse(JSON.stringify(emp));

  if (pdsModalRef.value) pdsModalRef.value.open();
}

function confirmDelete(emp) {
  employeeToDelete.value = emp;
  showDeleteModal.value = true;
}

async function deleteEmployee() {
  if (!employeeToDelete.value) return;

  try {
    await employeeService.delete(employeeToDelete.value.id);
    NotificationService.success("Employee deleted successfully.");
    showDeleteModal.value = false;
    fetchEmployees();
  } catch (error) {
    console.error("Error deleting employee:", error);
    NotificationService.error("An error occurred while deleting the employee.");
  }
}

onMounted(() => {
  fetchEmployees();
});

const filteredEmployees = computed(() =>
  employees.value.filter(emp =>
    `${emp.first_name} ${emp.last_name}`
      .toLowerCase()
      .includes(search.value.toLowerCase())
  )
);

function getEmptyFormData() {
  return {
    first_name: '',
    middle_name: '',
    last_name: '',
    name_extension: '',
    birth_date: '',
    place_of_birth: '',
    sex: '',
    civil_status: '',
    citizenship: '',
    height: '',
    weight: '',
    blood_type: '',
    gsis_id_no: '',
    pagibig_id_no: '',
    philhealth_no: '',
    sss_no: '',
    tin_no: '',
    agency_employee_no: '',
    residential_address: '',
    residential_zip: '',
    permanent_address: '',
    permanent_zip: '',
    telephone_no: '',
    mobile_no: '',
    email: '',
    ctc_number: '',
    ctc_place_of_issuance: '',
    ctc_date_of_issuance: '',

    spouse: {},
    father: {},
    mother: {},
    children: [],
    educations: [],
    eligibilities: [],
    work_experiences: [],
    voluntary_works: [],
    trainings: [],
    other_infos: [],
    references: []
  };
}
</script>
