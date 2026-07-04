<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const username = ref('')
const password = ref('')
const error = ref('')

async function submit() {
  error.value = ''
  try {
    await auth.login(username.value, password.value)
    router.push((route.query.redirect as string) || '/portal')
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Login failed'
  }
}
</script>

<template>
  <div class="flex min-h-[60vh] items-center justify-center py-16">
    <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
      <h1 class="text-2xl font-bold text-primary">Customer Sign In</h1>
      <p class="mt-1 text-sm text-slate-500">Access downloads, tickets, repair tracking and more.</p>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <input v-model="username" required placeholder="Username or email" autocomplete="username" class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
        <input v-model="password" required type="password" placeholder="Password" autocomplete="current-password" class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
        <button type="submit" class="btn-primary w-full justify-center" :disabled="auth.loading">
          {{ auth.loading ? 'Signing in…' : 'Sign In' }}
        </button>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
      </form>

      <p class="mt-6 text-center text-xs text-slate-400">
        No account yet?
        <RouterLink to="/register" class="text-accent hover:underline">Request portal access</RouterLink>
        — reviewed and approved by our team.
      </p>
    </div>
  </div>
</template>
