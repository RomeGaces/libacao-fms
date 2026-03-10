import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import defaults from './defaults'
import { icons } from './icons'

// Styles
import '@core-scss/template/libs/vuetify/index.scss'
import 'vuetify/styles'

// 🔹 Import Poppins font
import "@fontsource/poppins";
export default function (app) {
  const vuetify = createVuetify({
    components,       // ✅ Register all components
    directives,       // ✅ Register all directives
    aliases: {
      IconBtn: components.VBtn,
    },
    defaults: {
      ...defaults,
      global: {
        style: {
          fontFamily: 'Poppins, sans-serif',
        },
      },
    },
    icons,
    theme: {
      defaultTheme: 'light',
      themes: {
        light: {
          colors: {
            primary: '#1E88E5',
            secondary: '#FFC107',
            success: '#4CAF50',
            info: '#2196F3',
            warning: '#FB8C00',
            error: '#FF5252',
            background: '#F5F5F5',
            surface: '#FFFFFF',
          },
        },
        dark: {
          colors: {
            primary: '#1E88E5',
            secondary: '#FFC107',
            success: '#4CAF50',
            info: '#2196F3',
            warning: '#FB8C00',
            error: '#FF5252',
            background: '#121212',
            surface: '#1E1E1E',
          },
        },
      },
    },
  })

  app.use(vuetify)
}
