<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppHeader from '@/components/common/AppHeader.vue'

const auth = useAuthStore()
const router = useRouter()

const nav = [
  { label: 'Dashboard', to: '/portal' },
  { label: 'Downloads', to: '/portal/downloads' },
  { label: 'Tickets', to: '/portal/tickets' },
  { label: 'Repair Tracking', to: '/portal/repairs' },
  { label: 'Inquiry History', to: '/portal/inquiries' },
  { label: 'Profile', to: '/portal/profile' },
]

function logout() {
  auth.logout()
  router.push('/')
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-slate-50">
    <AppHeader />
    <div class="container-page flex flex-1 gap-8 py-8">
      <aside class="hidden w-56 shrink-0 lg:block">
        <nav class="space-y-1" aria-label="Portal">
          <RouterLink
            v-for="item in nav"
            :key="item.to"
            :to="item.to"
            class="block rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-white hover:text-primary"
            exact-active-class="bg-white text-primary shadow-sm"
          >
            {{ item.label }}
          </RouterLink>
          <button class="block w-full rounded-md px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50" @click="logout">
            Sign Out
          </button>
        </nav>
      </aside>
      <main class="min-w-0 flex-1">
        <RouterView />
      </main>
    </div>
  </div>
</template>
