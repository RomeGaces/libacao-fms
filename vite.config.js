import laravel from 'laravel-vite-plugin'
import { fileURLToPath } from 'node:url'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { defineConfig } from 'vite'
import vuetify from 'vite-plugin-vuetify'
import svgLoader from 'vite-svg-loader'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
  // Laravel integration
  laravel({
    input: [
      'resources/css/app.scss',
      'resources/js/app.js',
      'resources/js/FMS/main.js',
    ],
    refresh: true,
  }),

  // Vue 3 plugin
  vue({
    template: {
      transformAssetUrls: {
        base: null,
        includeAbsolute: false,
      },
    },
  }),

  // Vue JSX support
  vueJsx(),

  // Vuetify plugin
  vuetify({
    styles: {
      configFile: 'resources/styles/variables/_vuetify.scss',
    },
  }),

  // Auto-register components
  Components({
    dirs: ['resources/js/FMS/@core/components', 'resources/js/components'],
    dts: true,
    resolvers: [
      componentName => {
        // Auto import `VueApexCharts`
        if (componentName === 'VueApexCharts')
          return { name: 'default', from: 'vue3-apexcharts', as: 'VueApexCharts' }
      },
    ],
  }),

  // Auto-import commonly used packages
  AutoImport({
    imports: ['vue', 'vue-router', '@vueuse/core', '@vueuse/math', 'pinia'],
    vueTemplate: true,
    ignore: ['useCookies', 'useStorage'],
    eslintrc: {
      enabled: true,
      filepath: './.eslintrc-auto-import.json',
    },
  }),

  // SVG loader for inline SVGs
  svgLoader(),
],
  define: { 'process.env': {} },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      '@core-scss': fileURLToPath(new URL('./resources/styles/@core', import.meta.url)),
      '@fms': fileURLToPath(new URL('./resources/js/FMS', import.meta.url)),
      '@core': fileURLToPath(new URL('./resources/js/FMS/@core', import.meta.url)),
      '@layouts': fileURLToPath(new URL('./resources/js/FMS/@layouts', import.meta.url)),
      '@images': fileURLToPath(new URL('./resources/images/', import.meta.url)),
      '@styles': fileURLToPath(new URL('./resources/styles/', import.meta.url)),
      '@configured-variables': fileURLToPath(new URL('./resources/styles/variables/_template.scss', import.meta.url)),
    },
  },
  build: {
    chunkSizeWarningLimit: 5000,
  },
  optimizeDeps: {
    exclude: ['vuetify'],
    entries: [
      './resources/js/FMS/**/*.vue',
    ],
  },
})