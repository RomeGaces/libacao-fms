<script setup>
import CardBasic from '@fms/views/pages/cards/card-basic/CardBasic.vue'
import CardNavigation from '@fms/views/pages/cards/card-basic/CardNavigation.vue'
import CardSolid from '@fms/views/pages/cards/card-basic/CardSolid.vue'
</script>

<template>
  <div>
    <p class="text-2xl mb-6">
      Basic Cards
    </p>

    <CardBasic />

    <p class="text-2xl mb-6 mt-14">
      Navigation Cards
    </p>

    <CardNavigation />

    <p class="text-2xl mt-14 mb-6 ">
      Solid Cards
    </p>

    <CardSolid />
  </div>
</template>
