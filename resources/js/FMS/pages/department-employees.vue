<script setup>
import DepartmentEmployeeList from '@fms/views/pages/employees/DepartmentEmployeeList.vue'
import teamIcon from '@/../icons/office_building_3d.png'
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center">
              <img
                :src="teamIcon"
                alt="Employees Icon"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">Department Employees</span>
            </div>
          </div>
        </template>

        <VCardText class="text-body-2 text-grey-darken-2">
          This module displays all employees within your department. You can view employee details, search by name or employee number, and filter by various criteria.
        </VCardText>

        <VCardText class="pt-0">
          <DepartmentEmployeeList />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>