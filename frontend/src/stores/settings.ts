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
    if (settings.value?.company.name) {
      document.title = settings.value.company.name
    }
  }

  return { settings, loaded, load }
})
