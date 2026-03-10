<script setup>
import Footer from '@fms/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@fms/layouts/components/NavbarThemeSwitcher.vue'
import NavItems from '@fms/layouts/components/NavItems.vue'
import UserProfile from '@fms/layouts/components/UserProfile.vue'

// ✅ Use PNG logo
import logo from '@images/logo.png'

import VerticalNavLayout from '@layouts/components/VerticalNavLayout.vue'
</script>

<template>
  <VerticalNavLayout>
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <!-- 👉 Vertical nav toggle in overlay mode -->
        <IconBtn
          class="ms-n3 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon icon="bx-menu" />
        </IconBtn>

        <!-- 👉 Search -->
        <div
          class="d-flex align-center cursor-pointer ms-lg-n3"
          style="user-select: none;"
        >
          <IconBtn>
            <VIcon icon="bx-search" />
          </IconBtn>

          <span class="d-none d-md-flex align-center text-disabled ms-2">
            <span class="me-2">Search</span>
            <span class="meta-key">&#8984;K</span>
          </span>
        </div>

        <VSpacer />
<!-- 
        <IconBtn
          href="https://github.com/themeselection/sneat-vuetify-vuejs-laravel-admin-template-free"
          target="_blank"
          rel="noopener noreferrer"
        >
          <VIcon icon="bxl-github" />
        </IconBtn> -->
<!-- 
        <IconBtn>
          <VIcon icon="bx-bell" />
        </IconBtn> -->

        <NavbarThemeSwitcher class="me-1" />

        <UserProfile />
      </div>
    </template>

    <!-- 👉 Vertical Nav Header -->
    <template #vertical-nav-header="{ toggleIsOverlayNavActive }">
      <RouterLink
        to="/"
        class="app-logo app-title-wrapper"
      >
        <!-- ✅ Logo image -->
        <img
          :src="logo"
          alt="App Logo"
          class="app-logo-img"
        />

        <!-- ✅ Title + Subtitle -->
        <div class="app-logo-text">
          <h1 class="app-logo-title">FMS</h1>
          <p class="app-logo-subtitle">Libacao Aklan</p>
        </div>
      </RouterLink>

      <IconBtn
        class="d-block d-lg-none"
        @click="toggleIsOverlayNavActive(false)"
      >
        <VIcon icon="bx-x" />
      </IconBtn>
    </template>

    <!-- 👉 Vertical Nav Content -->
    <template #vertical-nav-content>
      <NavItems />
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>
  </VerticalNavLayout>
</template>

<style lang="scss" scoped>
.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;

  .app-logo-img {
    width: 40px;
    height: 40px;
    object-fit: contain;
  }

  .app-logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
  }

  .app-logo-title {
    font-size: 1.75rem;   /* Bigger title */
    font-weight: 800;
    text-transform: none !important;  /* ✅ stop forcing uppercase/lowercase */
    margin: 0;
  }

  .app-logo-subtitle {
    font-size: 0.8rem;
    font-weight: 400;
    color: var(--v-theme-on-surface-variant);
    text-transform: none !important;  /* ✅ keep natural casing */
    margin: 0;
  }
}
</style>
