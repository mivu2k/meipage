import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { setToken, getToken } from '@/api/client'
import { login as apiLogin, getMe } from '@/api/wp'
import type { AuthUser } from '@/api/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => user.value !== null)
  const hasRole = (role: string) => user.value?.roles.includes(role) ?? false

  async function login(username: string, password: string) {
    loading.value = true
    try {
      const res = await apiLogin(username, password)
      setToken(res.token)
      user.value = res.user
    } finally {
      loading.value = false
    }
  }

  function logout() {
    setToken(null)
    user.value = null
  }

  /** Restore session from a stored JWT on app boot. */
  async function restore() {
    if (!getToken()) return
    try {
      user.value = await getMe()
    } catch {
      logout()
    }
  }

  return { user, loading, isAuthenticated, hasRole, login, logout, restore }
})
