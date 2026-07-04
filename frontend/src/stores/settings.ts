import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getSettings } from '@/api/wp'
import type { SiteSettings } from '@/api/types'

/** Global site settings loaded once from WordPress; drives branding, menus, theme. */
export const useSettingsStore = defineStore('settings', () => {
  const settings = ref<SiteSettings | null>(null)
  const loaded = ref(false)

  async function load() {
    try {
      settings.value = await getSettings()
      applyTheme()
    } catch (e) {
      console.error('Failed to load site settings', e)
    } finally {
      loaded.value = true
    }
  }

  function applyTheme() {
    const theme = settings.value?.theme
    if (!theme) return
    const root = document.documentElement
    if (theme.primary) root.style.setProperty('--dtc-primary', theme.primary)
    if (theme.secondary) root.style.setProperty('--dtc-secondary', theme.secondary)
    if (theme.accent) root.style.setProperty('--dtc-accent', theme.accent)
    const company = settings.value?.company
    if (company?.name) {
      document.title = company.slogan ? `${company.name} — ${company.slogan}` : company.name
    }
    if (company?.favicon) {
      let link = document.querySelector<HTMLLinkElement>('link[rel="icon"]')
      if (!link) {
        link = document.createElement('link')
        link.rel = 'icon'
        document.head.appendChild(link)
      }
      link.href = company.favicon
      link.removeAttribute('type') // let the browser infer png/ico/svg
    }
  }

  return { settings, loaded, load }
})
