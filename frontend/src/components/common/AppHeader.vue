<script setup lang="ts">
import { ref, computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useInquiryStore } from '@/stores/inquiry'
import { useAuthStore } from '@/stores/auth'

const settings = useSettingsStore()
const inquiry = useInquiryStore()
const auth = useAuthStore()
const mobileOpen = ref(false)

const fallbackNav = [
  { label: 'Products', url: '/products' },
  { label: 'Brands', url: '/brands' },
  { label: 'Solutions', url: '/solutions' },
  { label: 'News', url: '/news' },
  { label: 'About', url: '/about' },
  { label: 'Support', url: '/support' },
  { label: 'Careers', url: '/careers' },
  { label: 'Contact', url: '/contact' },
]
const nav = computed(() => settings.settings?.menus?.primary ?? fallbackNav)
const company = computed(() => settings.settings?.company)
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="container-page flex h-16 items-center justify-between gap-6">
      <RouterLink to="/" class="flex items-center gap-3" aria-label="Home">
        <img v-if="company?.logo" :src="company.logo" :alt="company.name" class="h-9 w-auto" />
        <span v-else class="text-lg font-bold text-primary">{{ company?.name || 'Defense Tech' }}</span>
      </RouterLink>

      <nav class="hidden items-center gap-6 lg:flex" aria-label="Primary">
        <RouterLink
          v-for="item in nav"
          :key="item.url"
          :to="item.url"
          class="text-sm font-medium text-slate-600 transition hover:text-primary"
          active-class="text-primary"
        >
          {{ item.label }}
        </RouterLink>
      </nav>

      <div class="flex items-center gap-3">
        <RouterLink
          to="/inquiry"
          class="relative rounded-md p-2 text-slate-600 hover:text-primary"
          aria-label="Inquiry list"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
          </svg>
          <span
            v-if="inquiry.count"
            class="absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1 text-xs font-bold text-primary"
          >
            {{ inquiry.count }}
          </span>
        </RouterLink>
        <RouterLink v-if="auth.isAuthenticated" to="/portal" class="btn-primary hidden sm:inline-flex">Portal</RouterLink>
        <RouterLink v-else to="/login" class="btn-outline hidden sm:inline-flex">Sign In</RouterLink>
        <button
          class="rounded-md p-2 text-slate-600 lg:hidden"
          aria-label="Toggle menu"
          @click="mobileOpen = !mobileOpen"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <nav v-if="mobileOpen" class="border-t border-slate-200 bg-white lg:hidden" aria-label="Mobile">
      <div class="container-page flex flex-col py-2">
        <RouterLink
          v-for="item in nav"
          :key="item.url"
          :to="item.url"
          class="py-2.5 text-sm font-medium text-slate-700"
          @click="mobileOpen = false"
        >
          {{ item.label }}
        </RouterLink>
        <RouterLink v-if="!auth.isAuthenticated" to="/login" class="py-2.5 text-sm font-semibold text-primary" @click="mobileOpen = false">
          Sign In
        </RouterLink>
      </div>
    </nav>
  </header>
</template>
