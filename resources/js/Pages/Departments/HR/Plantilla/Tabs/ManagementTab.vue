<template>
  <div class="management-header">
    <Input class="search-inp" name="search" placeholder="Search..." size="md" />
    <div class="button-group">
      <Button variant="secondary">
        <img class="icon-print" src="images/icons/printer1.png" /> Print Report
      </Button>
      <Button variant="primary" @click="openAddPositionModal">
        <img class="icon-add" src="images/icons/plus.png" /> Add Position
      </Button>
    </div>
  </div>

  <div class="management-table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Item No</th>
          <th>Salary Grade</th>
          <th>Step</th>
          <th>Monthly Rate</th>
          <th>Eligibility</th>
          <th>Education</th>
          <th>Department</th>
          <th>Division</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="p in plantillas" :key="p.id">
          <td>{{ p.plantilla_item_number }}</td>
          <td>{{ p.salary_grade }}</td>
          <td>{{ p.step }}</td>
          <td>{{ p.monthly_rate }}</td>
          <td>{{ p.eligibility_requirement }}</td>
          <td>{{ p.educational_requirement }}</td>
          <td>{{ p.department?.department_name }}</td>
          <td>{{ p.division?.division_name }}</td>
          <td>
            <Tooltip text="View Details" placement="top">
              <Button class="btn-action" variant="secondary" size="xs">
                <img class="icon" src="images/icons/view.png" />
              </Button>
            </Tooltip>

            <Button
              class="btn-action"
              variant="secondary"
              size="xs"
              @click="editPosition(p)"
            >
              <img class="icon" src="images/icons/edit.png" />
            </Button>

            <Button
              class="btn-action"
              variant="danger"
              size="xs"
              @click="openDeleteModal(p)"
            >
              <img class="icon" src="images/icons/delete.png" />
            </Button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Add / Edit Modal -->
    <AddPositionModal
      v-model="showAddPositionModal"
      :edit-data="editingPosition"
      @saved="onPositionSaved"
    />

    <!-- Delete Confirmation Modal -->
    <Modal v-model="showDeleteModal" title="Confirm Delete" size="sm" height="xs">
      <template #default>
        <p>
          Are you sure you want to delete
          <strong>{{ deleteTarget?.plantilla_item_number }}</strong>?
        </p>
      </template>
      <template #footer>
        <Button variant="secondary" size="md" @click="closeDeleteModal">
          Cancel
        </Button>
        <Button variant="primary" size="md" @click="deletePosition">
          Delete
        </Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

import Button from "@/Components/Common/Button.vue";
import Input from "@/Components/Common/Input.vue";
import Tooltip from "@/Components/Common/Tooltip.vue";
import AddPositionModal from "../Modals/AddPositionModal.vue";
import Modal from "@/Components/Common/Modal.vue";
import NotificationService from "@/Services/NotificationService";

const plantillas = ref([]);
const showAddPositionModal = ref(false);
const editingPosition = ref(null);

const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const openAddPositionModal = () => {
  editingPosition.value = null;
  showAddPositionModal.value = true;
};

const editPosition = async (p) => {
  editingPosition.value = null;
  showAddPositionModal.value = true;

  try {
    const res = await axios.get(`/api/plantillas/${p.id}`);
    editingPosition.value = res.data;
  } catch (err) {
    editingPosition.value = { ...p };
    NotificationService.error("Failed to load full position, using cached data.");
  }
};

const openDeleteModal = (p) => {
  deleteTarget.value = p;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  deleteTarget.value = null;
  showDeleteModal.value = false;
};

const deletePosition = async () => {
  if (!deleteTarget.value) return;
  try {
    await axios.delete(`/api/plantillas/${deleteTarget.value.id}`);
    plantillas.value = plantillas.value.filter((p) => p.id !== deleteTarget.value.id);
    NotificationService.success("Position deleted successfully.");
  } catch (err) {
    NotificationService.error("Failed to delete position.");
  } finally {
    closeDeleteModal();
  }
};

const onPositionSaved = (saved) => {
  const index = plantillas.value.findIndex((p) => p.id === saved.id);
  if (index !== -1) plantillas.value[index] = saved;
  else plantillas.value.push(saved);

  NotificationService.success(`Position ${index !== -1 ? "updated" : "added"} successfully.`);
};

const loadPlantillas = async () => {
  try {
    const res = await axios.get("/api/plantillas");
    plantillas.value = res.data;
  } catch (err) {
    console.log("Failed to load plantilla positions.");
  }
};

onMounted(loadPlantillas);
</script>
