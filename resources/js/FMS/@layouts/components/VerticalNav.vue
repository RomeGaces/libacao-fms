<script setup>
import logo from '@images/logo.png?raw'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useDisplay } from 'vuetify'

const props = defineProps({
  tag: {
    type: null,
    required: false,
    default: 'aside',
  },
  isOverlayNavActive: {
    type: Boolean,
    required: true,
  },
  toggleIsOverlayNavActive: {
    type: Function,
    required: true,
  },
})

const { mdAndDown } = useDisplay()
const refNav = ref()

// Close overlay nav on route change
const route = useRoute()
watch(() => route.path, () => props.toggleIsOverlayNavActive(false))

const isVerticalNavScrolled = ref(false)
const updateIsVerticalNavScrolled = val => (isVerticalNavScrolled.value = val)
const handleNavScroll = evt => {
  isVerticalNavScrolled.value = evt.target.scrollTop > 0
}
</script>

<template>
  <Component
    :is="props.tag"
    ref="refNav"
    data-allow-mismatch
    class="layout-vertical-nav"
    :class="[
      {
        visible: isOverlayNavActive,
        scrolled: isVerticalNavScrolled,
        'overlay-nav': mdAndDown,
      },
    ]"
  >
    <!-- 👉 Header -->
    <div class="nav-header">
      <slot name="nav-header">
        <RouterLink to="/" class="app-logo app-title-wrapper">
          <div class="d-flex" v-html="logo" />
          <h1 class="leading-normal">Libacao Aklan</h1>
        </RouterLink>
      </slot>
    </div>

    <slot name="before-nav-items">
      <div class="vertical-nav-items-shadow" />
    </slot>

    <slot
      name="nav-items"
      :update-is-vertical-nav-scrolled="updateIsVerticalNavScrolled"
    >
      <PerfectScrollbar
        tag="ul"
        class="nav-items"
        :options="{ wheelPropagation: false }"
        @ps-scroll-y="handleNavScroll"
      >
        <slot />
      </PerfectScrollbar>
    </slot>

    <slot name="after-nav-items" />
  </Component>
</template>

<style lang="scss" scoped>
.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;

  .app-logo-title {
    font-size: 1.25rem;
    font-weight: 500;
    line-height: 1.75rem;
    text-transform: uppercase;
  }
}
</style>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@layouts/styles/mixins";

// 👉 Vertical Nav
.layout-vertical-nav {
  position: fixed;
  z-index: variables.$layout-vertical-nav-z-index;
  display: flex;
  flex-direction: column;
  block-size: 100%;
  inline-size: variables.$layout-vertical-nav-width;
  inset-block-start: 0;
  inset-inline-start: 0;
  transition: inline-size 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
  will-change: transform, inline-size;

  /* adjustable header height */
  --nav-header-height: 88px;

  .nav-header {
    flex: 0 0 var(--nav-header-height);
    display: flex;
    align-items: center;
    justify-content: flex-start;
    position: sticky;
    top: 0;
    z-index: 2;
    background: #fff;
    padding-inline: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);

    .header-action {
      cursor: pointer;

      @at-root {
        #{variables.$selector-vertical-nav-mini} .nav-header .header-action {
          &.nav-pin,
          &.nav-unpin {
            display: none !important;
          }
        }
      }
    }
  }

  &.scrolled .nav-header {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    border-bottom-color: transparent;
  }

  .app-title-wrapper {
    margin-inline-end: auto;
  }

  /* scroll area */
  .nav-items {
    flex: 1 1 auto;
    min-height: 0; /* needed for flexbox scroll */
  }

  .nav-item-title {
    overflow: hidden;
    margin-inline-end: auto;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  // 👉 Collapsed
  .layout-vertical-nav-collapsed & {
    &:not(.hovered) {
      inline-size: variables.$layout-vertical-nav-collapsed-width;
    }
  }
}

/* Small screen vertical nav transition */
@media (max-width: 1279px) {
  .layout-vertical-nav {
    &:not(.visible) {
      transform: translateX(-#{variables.$layout-vertical-nav-width});

      @include mixins.rtl {
        transform: translateX(variables.$layout-vertical-nav-width);
      }
    }

    transition: transform 0.25s ease-in-out;
  }
}
</style>
