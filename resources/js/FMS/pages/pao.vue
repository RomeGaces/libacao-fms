<script setup>
import { ref } from 'vue';

// Child component for displaying table
import PaoRequestsTable from '@fms/views/pages/pao/pao.vue';

// Icon alias (custom imported icon)
import puzzlePiece from '@/../icons/puzzle_piece_3d.png';


// For triggering refresh in table
const refreshKey = ref(0)

function viewRequest(request) {
  console.log('View request:', request)
  // 🔹 Open modal or route to detail page later
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <!-- Card Title with Action Button -->
        <template #title>
          <div class="d-flex justify-space-between align-center">
            <!-- 🔹 Puzzle Icon + Title -->
            <div class="d-flex align-center">
              <img
                :src="puzzlePiece"
                alt="Puzzle Icon"
                class="me-3"
                style="width: 28px; height: 28px;"
              />
              <span class="text-h6">Program Appropriation</span>
            </div>
          </div>
        </template>

        <!-- Description -->
        <VCardText class="text-body-2 text-grey-darken-2">
          Request for the preparation of a Programmed Appropriation and Obligation by Object of Expenditure report showing the authorized budget allocations and corresponding obligations for proper monitoring and financial planning.
        </VCardText>

        <!-- Injected Table -->
        <VCardText class="pt-0">
          <PaoRequestsTable
            :key="refreshKey"
            @fmsview="viewRequest"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
