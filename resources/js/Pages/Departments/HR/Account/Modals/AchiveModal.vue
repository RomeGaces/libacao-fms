<template>
    <Modal class="archive-modal grid" v-model="showArchiveModal" title="Archives" size="lg" height="lg">
        <div class="archive-table-wrapper">
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
              <td>Inactive</td>
              <td>
                <Tooltip text="Restore" placement="top">
                  <Button class="btn-action" variant="secondary" size="xs">
                    <img class="icon" src="images/icons/reset.png"/>
                  </Button>
                </Tooltip>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Modal>
</template>

<script setup>
    import { ref } from "vue";
    import Modal from "@/components/Common/Modal.vue";
    import Button from "@/components/Common/Button.vue";
    import Tooltip from "@/Components/Common/Tooltip.vue";

    const showArchiveModal = ref(false);
</script>
