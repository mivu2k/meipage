import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { VueQueryPlugin } from '@tanstack/vue-query'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import { useSettingsStore } from './stores/settings'
import './style.css'

const app = createApp(App)
app.use(createPinia())
app.use(VueQueryPlugin, {
  queryClientConfig: {
    defaultOptions: {
      queries: { staleTime: 5 * 60 * 1000, retry: 1, refetchOnWindowFocus: false },
    },
  },
})
app.use(router)

// Boot: restore auth session and load site settings before first render.
const auth = useAuthStore()
const settings = useSettingsStore()
Promise.allSettled([auth.restore(), settings.load()]).finally(() => {
  app.mount('#app')
})
