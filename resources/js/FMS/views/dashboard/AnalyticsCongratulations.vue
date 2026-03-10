<script setup>
import illustrationJohnDark from '@images/cards/illustration-john-dark.png'
import illustrationJohnLight from '@images/cards/illustration-john-light.png'
import { computed, onMounted, ref } from 'vue'
import { useTheme } from 'vuetify'

// Vuetify theme
const { global } = useTheme()
const illustrationJohn = computed(() =>
  global.name.value === 'dark' ? illustrationJohnDark : illustrationJohnLight
)

// ✅ State for quote
const dailyQuote = ref("Loading your daily quote...")

// ✅ Function to fetch from OpenAI
// async function fetchDailyQuote() {
//   try {
//     const response = await fetch("https://api.openai.com/v1/chat/completions", {
//       method: "POST",
//       headers: {
//         "Content-Type": "application/json",
//         "Authorization": `Bearer ${import.meta.env.VITE_OPENAI_API_KEY}`, // 👈 from .env
//       },
//       body: JSON.stringify({
//         model: "gpt-4o-mini",
//         messages: [
//           { role: "system", content: "You are a helpful assistant that gives motivational quotes." },
//           { role: "user", content: "Give me one motivational quote." }
//         ],
//       }),
//     })

//     const data = await response.json()

//     if (data?.choices?.[0]?.message?.content) {
//       dailyQuote.value = data.choices[0].message.content.trim()
//     } else {
//       dailyQuote.value = "Stay positive and keep going!"
//     }

//   } catch (err) {
//     console.error(err)
//     dailyQuote.value = "Stay positive and keep going!"
//   }
// }

// Run when component loads
onMounted(() => {
  // fetchDailyQuote()
})
</script>

<template>
  <VCard class="text-center text-sm-start">
    <VRow no-gutters>
      <VCol cols="12" sm="8" order="2" order-sm="1">
        <VCardItem class="pb-3">
          <VCardTitle class="text-primary">
            Daily Motivation 🌟
          </VCardTitle>
        </VCardItem>

        <VCardText>
          {{ dailyQuote }}
          <br>
          <VBtn
            variant="tonal"
            class="mt-6"
            size="small"
            @click="fetchDailyQuote"
          >
            Refresh Quote
          </VBtn>
        </VCardText>
      </VCol>

      <VCol
        cols="12"
        sm="4"
        order="1"
        order-sm="2"
        class="text-center"
      >
        <img
          :src="illustrationJohn"
          :height="$vuetify.display.xs ? '150' : '182'"
          :class="$vuetify.display.xs ? 'mt-6 mb-n2' : 'position-absolute'"
          class="john-illustration flip-in-rtl"
        >
      </VCol>
    </VRow>
  </VCard>
</template>

<style lang="scss" scoped>
.john-illustration {
  inset-block-end: -0.125rem;
  inset-inline-end: 3.5rem;
}
</style>
