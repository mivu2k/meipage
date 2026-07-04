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

// v-reveal: fades elements in as they scroll into view (see .reveal in style.css)
const revealObserver = new IntersectionObserver(
  (entries) => {
    for (const e of entries) {
      if (e.isIntersecting) {
        e.target.classList.add('is-visible')
        revealObserver.unobserve(e.target)
      }
    }
  },
  { threshold: 0.12 },
)
app.directive('reveal', {
  mounted(el: HTMLElement, binding) {
    el.classList.add('reveal')
    if (typeof binding.value === 'number') el.style.transitionDelay = `${binding.value}ms`
    revealObserver.observe(el)
  },
})

// v-magnetic: buttons subtly follow the cursor while hovered (agency-style)
app.directive('magnetic', {
  mounted(el: HTMLElement) {
    if (!window.matchMedia('(pointer: fine)').matches) return
    const strength = 0.25
    const onMove = (e: MouseEvent) => {
      const r = el.getBoundingClientRect()
      const x = (e.clientX - r.left - r.width / 2) * strength
      const y = (e.clientY - r.top - r.height / 2) * strength
      el.style.transform = `translate(${x}px, ${y}px)`
    }
    const onLeave = () => {
      el.style.transition = 'transform 0.35s cubic-bezier(0.16, 1, 0.3, 1)'
      el.style.transform = ''
      setTimeout(() => (el.style.transition = ''), 350)
    }
    el.addEventListener('mousemove', onMove)
    el.addEventListener('mouseleave', onLeave)
  },
})

// Boot: restore auth session and load site settings before first render.
const auth = useAuthStore()
const settings = useSettingsStore()
Promise.allSettled([auth.restore(), settings.load()]).finally(() => {
  app.mount('#app')
})
