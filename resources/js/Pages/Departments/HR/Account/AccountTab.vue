<template>
    <section id="account">
      <div class="account-header">
        <Input
          class="search-inp" 
          name="search" 
          placeholder="Search Account..." 
          size="md" 
        />
        <div class="button-group">
          <Button variant="secondary" class="archive" @click="openArchiveModal">
            <img class="icon archive-icon" src="images/icons/archive.png"/> Archives
          </Button>
          <Button variant="primary" class="add" @click="openAddAccountModal">
            <img class="icon add-icon" src="images/icons/plus.png"/> Add Account
          </Button>
        </div>
      </div>
  
      <div class="account-table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Employee ID</th>
              <th>Name</th>
              <th>Access Level</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>EMP001</td>
              <td>Juan Dela Cruz</td>
              <td>Admin</td>
              <td>
                <div class="checkbox-wrapper">
                  <input type="checkbox" checked>
                </div>
              </td>
              <td>
                <Tooltip text="View Details" placement="top">
                  <Button class="btn-action" variant="secondary" size="xs">
                    <img class="icon" src="images/icons/view.png"/>
                  </Button>
                </Tooltip>
                
                <Button class="btn-action" variant="secondary" size="xs">
                  <img class="icon" src="images/icons/edit.png"/>
                </Button>
                <Button class="btn-action" variant="secondary" size="xs">
                  <img class="icon" src="images/icons/delete.png"/>
                </Button>
                <Button class="btn-action" variant="secondary" size="xs">
                  <img class="icon" src="images/icons/archive.png"/>
                </Button>
              </td>
            </tr>
            <tr>
              <td>EMP001</td>
              <td>Juan Dela Cruz</td>
              <td>Admin</td>
              <td>
                <div class="checkbox-wrapper">
                  <input type="checkbox">
                </div>
              </td>
              <td>
                <Tooltip text="View Details" placement="top">
                  <Button class="btn-action" variant="secondary" size="xs">
                    <img class="icon" src="images/icons/view.png"/>
                  </Button>
                </Tooltip>
                
                <Button class="btn-action" variant="secondary" size="xs">
                  <img class="icon" src="images/icons/edit.png"/>
                </Button>
                <Button class="btn-action" variant="secondary" size="xs">
                  <img class="icon" src="images/icons/delete.png"/>
                </Button>
                <Button class="btn-action" variant="secondary" size="xs">
                  <img class="icon" src="images/icons/archive.png"/>
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <AddAccountModal v-model="showAddAccountModal" />
      <ArchiveModal v-model="showArchiveModal" />
    </section>
  </template>
<script setup>
import Button from "@/Components/Common/Button.vue";
import Input from "@/Components/Common/Input.vue";
import Tooltip from "@/Components/Common/Tooltip.vue";
import AddAccountModal from "./Modals/AddAccountModal.vue";
import ArchiveModal from "./Modals/AchiveModal.vue";

const showAddAccountModal = ref(false);
const showArchiveModal = ref(false);

const openAddAccountModal = () => showAddAccountModal.value = true;
const openArchiveModal = () => showArchiveModal.value = true;
</script>