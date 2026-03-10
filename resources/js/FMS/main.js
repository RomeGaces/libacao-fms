import App from '@fms/App.vue'
import { registerPlugins } from '@core/utils/plugins'
import { createApp } from 'vue'

// Styles
import '@core-scss/template/index.scss'
import '@layouts/styles/index.scss'
import '@styles/styles.scss'

// Fonts
import '@fontsource/inter/400.css'
import '@fontsource/inter/500.css'
import '@fontsource/inter/600.css'
import "@fontsource/poppins";

// Create vue app
const app = createApp(App)

// Register plugins
registerPlugins(app)

// Mount vue app
app.mount('#app')
