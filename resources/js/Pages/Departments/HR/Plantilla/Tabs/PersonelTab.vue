<template>
    <section id="personel">
      <div class="personel-header">
        <Input
          class="search-inp" 
          name="search" 
          placeholder="Search..." 
          size="md" 
        />
        <Button variant="secondary" class="btn-print-report"><img class="icon-print" src="images/icons/printer1.png" /> Print Report</Button>
        <!-- <Button variant="primary">
          <img class="icon" src="images/icons/plus.png"/> Add Personel
        </Button> -->
      </div>
  
      <div class="personel-table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Office</th>
              <th>Item No.</th>
              <th>Position Title</th>
              <th>Name of Incumbent</th>
              <th>Salary Grade/Step</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Office of the Mayor</td>
              <td>1</td>
              <td>Municipal Mayor</td>
              <td>
                <select>
                  <option>VACANT</option>
                  <option>Juan Dela Cruz</option>
                  <option>Maria Santos</option>
                </select>
              </td>
              <td>27/1</td>
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
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </template>
<script setup>
import Button from "@/Components/Common/Button.vue";
import Input from "@/Components/Common/Input.vue";
import Tooltip from "@/Components/Common/Tooltip.vue";
</script>