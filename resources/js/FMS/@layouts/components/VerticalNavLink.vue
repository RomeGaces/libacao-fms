<script setup>
const props = defineProps({
  item: {
    type: null,
    required: true,
  },
})

// Check if the icon looks like an image path
const isImageIcon = computed(() =>
  typeof props.item.icon === 'string' &&
  (props.item.icon.endsWith('.png') || props.item.icon.endsWith('.jpg') || props.item.icon.endsWith('.jpeg') || props.item.icon.endsWith('.gif'))
)
</script>

<template>
  <li
    class="nav-link"
    :class="{ disabled: item.disable }"
  >
    <Component
      :is="item.to ? 'RouterLink' : (item.href ? 'RouterLink' : 'a')"
      :to="item.to || item.href"
      v-bind="item.target ? { target: item.target } : {}"
    >
      <!-- 👉 If PNG/JPG icon -->
      <template v-if="isImageIcon">
        <img
          :src="item.icon"
          alt="icon"
          class="nav-item-img"
        />
      </template>

      <!-- 👉 If normal VIcon -->
      <template v-else>
        <VIcon
          :icon="item.icon || 'bxs-circle'"
          class="nav-item-icon"
        />
      </template>

      <!-- 👉 Title -->
      <span class="nav-item-title">
        {{ item.title }}
      </span>

      <!-- 👉 Badge -->
      <span
        v-if="item.badgeContent"
        class="nav-item-badge"
        :class="item.badgeClass"
      >
        {{ item.badgeContent }}
      </span>
    </Component>
  </li>
</template>

<style lang="scss">
.layout-vertical-nav {
  .nav-link a {
    display: flex;
    align-items: center;
    cursor: pointer;
    text-decoration: none;
  }

  .nav-item-icon {
    display: inline-block;
    margin-right: 0.4rem;
    font-size: 16px; // ⬅ smaller icon
  }

  .nav-item-img {
    width: 16px;   // ⬅ smaller img icon
    height: 16px;
    margin-right: 0.4rem;
    object-fit: contain;
    display: inline-block;
  }

  .nav-item-title {
    flex-grow: 1;
    font-size: 12px; // ⬅ smaller font
    line-height: 1.2;
  }

  .nav-item-badge {
    margin-left: auto;
    font-size: 0.65rem; // ⬅ smaller badge font
    padding: 1px 4px;
    border-radius: 6px;
  }
}
</style>
