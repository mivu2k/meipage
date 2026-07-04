<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useInquiryStore } from '@/stores/inquiry'
import { useAuthStore } from '@/stores/auth'

const settings = useSettingsStore()
const inquiry = useInquiryStore()
const auth = useAuthStore()
const mobileOpen = ref(false)
const scrolled = ref(false)

const onScroll = () => (scrolled.value = window.scrollY > 8)
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))

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
  <header
    class="sticky top-0 z-40 border-b transition-all duration-300"
    :class="scrolled ? 'border-slate-200/80 bg-white/85 shadow-sm shadow-slate-900/5 backdrop-blur-xl' : 'border-transparent bg-white/60 backdrop-blur-lg'"
  >
    <div class="container-page flex h-[4.25rem] items-center justify-between gap-6">
      <RouterLink to="/" class="flex items-center gap-3" aria-label="Home">
        <img v-if="company?.logo" :src="company.logo" :alt="company.name" class="h-9 w-auto" />
        <span v-else class="font-display text-xl font-bold tracking-tight text-primary">
          {{ company?.name || 'Defense Tech' }}<span class="text-accent">.</span>
        </span>
      </RouterLink>

      <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
        <RouterLink
          v-for="item in nav"
          :key="item.url"
          :to="item.url"
          class="nav-link relative rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-primary"
          active-class="text-primary is-active"
        >
          {{ item.label }}
        </RouterLink>
      </nav>

      <div class="flex items-center gap-2">
        <RouterLink
          to="/inquiry"
          class="relative rounded-lg p-2.5 text-slate-600 transition hover:bg-slate-100 hover:text-primary"
          aria-label="Inquiry list"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
          </svg>
          <span
            v-if="inquiry.count"
            class="absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1 text-[11px] font-bold text-surface"
          >
            {{ inquiry.count }}
          </span>
        </RouterLink>
        <RouterLink v-if="auth.isAuthenticated" to="/portal" class="btn-primary hidden !py-2.5 sm:inline-flex">Portal</RouterLink>
        <template v-else>
          <RouterLink to="/login" class="btn-outline hidden !py-2.5 sm:inline-flex">Sign In</RouterLink>
          <RouterLink to="/register" class="btn-primary hidden !py-2.5 md:inline-flex">Register</RouterLink>
        </template>
        <button
          class="rounded-lg p-2.5 text-slate-600 transition hover:bg-slate-100 lg:hidden"
          :aria-expanded="mobileOpen"
          aria-label="Toggle menu"
          @click="mobileOpen = !mobileOpen"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path v-if="!mobileOpen" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
          </svg>
        </button>
      </div>
    </div>

    <Transition name="slide">
      <nav v-if="mobileOpen" class="border-t border-slate-200 bg-white/95 backdrop-blur-xl lg:hidden" aria-label="Mobile">
        <div class="container-page flex flex-col py-3">
          <RouterLink
            v-for="item in nav"
            :key="item.url"
            :to="item.url"
            class="rounded-lg px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-primary"
            @click="mobileOpen = false"
          >
            {{ item.label }}
          </RouterLink>
          <template v-if="!auth.isAuthenticated">
            <RouterLink
              to="/login"
              class="mt-2 rounded-lg border border-slate-300 px-3 py-3 text-center text-sm font-semibold text-slate-700"
              @click="mobileOpen = false"
            >
              Sign In
            </RouterLink>
            <RouterLink
              to="/register"
              class="mt-2 rounded-lg bg-primary px-3 py-3 text-center text-sm font-semibold text-white"
              @click="mobileOpen = false"
            >
              Register
            </RouterLink>
          </template>
        </div>
      </nav>
    </Transition>
  </header>
</template>

<style scoped>
.nav-link::after {
  content: '';
  position: absolute;
  left: 0.75rem;
  right: 0.75rem;
  bottom: 0.25rem;
  height: 2px;
  border-radius: 2px;
  background: var(--dtc-accent);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.25s ease;
}
.nav-link:hover::after,
.nav-link.is-active::after {
  transform: scaleX(1);
}

.slide-enter-active,
.slide-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
