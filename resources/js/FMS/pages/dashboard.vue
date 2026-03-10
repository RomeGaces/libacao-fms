<script setup>
import { ref, onMounted } from "vue" 
import api from "@fms/utils/api";

import AnalyticsCongratulations from '@fms/views/dashboard/AnalyticsCongratulations.vue'
import AnalyticsFinanceTabs from '@fms/views/dashboard/AnalyticsFinanceTab.vue'
import AnalyticsOrderStatistics from '@fms/views/dashboard/AnalyticsOrderStatistics.vue'
import AnalyticsProfitReport from '@fms/views/dashboard/AnalyticsProfitReport.vue'
import AnalyticsTotalRevenue from '@fms/views/dashboard/AnalyticsTotalRevenue.vue'
import AnalyticsTransactions from '@fms/views/dashboard/AnalyticsTransactions.vue'

// 👉 Images
import chart from '@images/cards/chart-success.png'
import card from '@images/cards/credit-card-primary.png'
import paypal from '@images/cards/paypal-error.png'
import wallet from '@images/cards/wallet-info.png'


// Token for simplicity (could move to .env or store)
onMounted(async () => {
  try {
    const { data } = await api.get("/user"); // no need to repeat headers
    console.log(data);
  } catch (err) {
    console.error(err);
  }
});
</script>

<template>
  <VRow>
    <!-- 👉 Congratulations -->
    <VCol
      cols="12"
      md="8"
    >
      <AnalyticsCongratulations />
    </VCol>

    <VCol
      cols="12"
      sm="4"
    >
      <VRow>
        <!-- 👉 Profit -->
        <VCol
          cols="12"
          md="6"
        >
          <CardStatisticsVertical
            v-bind="{
              title: 'Profit',
              image: chart,
              stats: '$12,628',
              change: 72.80,
            }"
          />
        </VCol>

        <!-- 👉 Sales -->
        <VCol
          cols="12"
          md="6"
        >
          <CardStatisticsVertical
            v-bind="{
              title: 'Sales',
              image: wallet,
              stats: '$4,679',
              change: 28.42,
            }"
          />
        </VCol>
      </VRow>
    </VCol>

    <!-- 👉 Total Revenue -->
    <VCol
      cols="12"
      md="8"
      order="2"
      order-md="1"
    >
      <AnalyticsTotalRevenue />
    </VCol>

    <VCol
      cols="12"
      sm="8"
      md="4"
      order="1"
      order-md="2"
    >
      <VRow>
        <!-- 👉 Payments -->
        <VCol
          cols="12"
          sm="6"
        >
          <CardStatisticsVertical
            v-bind=" {
              title: 'Payments',
              image: paypal,
              stats: '$2,468',
              change: -14.82,
            }"
          />
        </VCol>

        <!-- 👉 Revenue -->
        <VCol
          cols="12"
          sm="6"
        >
          <CardStatisticsVertical
            v-bind="{
              title: 'Transactions',
              image: card,
              stats: '$14,857',
              change: 28.14,
            }"
          />
        </VCol>
      </VRow>

      <VRow>
        <!-- 👉 Profit Report -->
        <VCol
          cols="12"
          sm="12"
        >
          <AnalyticsProfitReport />
        </VCol>
      </VRow>
    </VCol>

    <!-- 👉 Order Statistics -->
    <VCol
      cols="12"
      md="4"
      sm="6"
      order="3"
    >
      <AnalyticsOrderStatistics />
    </VCol>

    <!-- 👉 Tabs chart -->
    <VCol
      cols="12"
      md="4"
      sm="6"
      order="3"
    >
      <AnalyticsFinanceTabs />
    </VCol>

    <!-- 👉 Transactions -->
    <VCol
      cols="12"
      md="4"
      sm="6"
      order="3"
    >
      <AnalyticsTransactions />
    </VCol>
  </VRow>
</template>
