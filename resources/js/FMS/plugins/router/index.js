import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory('/build/'), // ✅ FIXED - hardcoded base path
  routes,
})

export default function (app) {
  app.use(router)
}
export { router }