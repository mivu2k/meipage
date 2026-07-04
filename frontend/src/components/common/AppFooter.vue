<script setup lang="ts">
import { computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const store = useSettingsStore()
const s = computed(() => store.settings)
const year = new Date().getFullYear()
</script>

<template>
  <footer class="section-dark">
    <div class="h-px w-full bg-gradient-to-r from-transparent via-accent/60 to-transparent" aria-hidden="true" />
    <div class="tech-grid opacity-40" aria-hidden="true" />
    <div class="container-page relative z-10 grid gap-12 py-16 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <img v-if="s?.company.logo_dark" :src="s.company.logo_dark" :alt="s.company.name" class="mb-5 h-9 w-auto" />
        <h3 v-else class="font-display mb-5 text-xl font-bold text-white">
          {{ s?.company.name || 'Defense Tech' }}<span class="text-accent">.</span>
        </h3>
        <p class="text-sm leading-relaxed text-slate-400">{{ s?.footer.about || s?.company.slogan }}</p>
        <div class="mt-5 flex gap-3">
          <a
            v-for="(url, name) in s?.social"
            :key="name"
            :href="url"
            target="_blank"
            rel="noopener"
            class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-medium text-slate-300 capitalize transition hover:border-accent hover:text-accent"
          >
            {{ name }}
          </a>
        </div>
      </div>
      <div>
        <h4 class="mb-5 text-xs font-bold tracking-[0.2em] text-slate-300 uppercase">Explore</h4>
        <ul class="space-y-3 text-sm text-slate-400">
          <li><RouterLink to="/products" class="transition hover:text-accent">Products</RouterLink></li>
          <li><RouterLink to="/solutions" class="transition hover:text-accent">Solutions</RouterLink></li>
          <li><RouterLink to="/brands" class="transition hover:text-accent">Brands</RouterLink></li>
          <li><RouterLink to="/news" class="transition hover:text-accent">News</RouterLink></li>
          <li><RouterLink to="/careers" class="transition hover:text-accent">Careers</RouterLink></li>
        </ul>
      </div>
      <div>
        <h4 class="mb-5 text-xs font-bold tracking-[0.2em] text-slate-300 uppercase">Support</h4>
        <ul class="space-y-3 text-sm text-slate-400">
          <li><RouterLink to="/support" class="transition hover:text-accent">Support Center</RouterLink></li>
          <li><RouterLink to="/portal" class="transition hover:text-accent">Customer Portal</RouterLink></li>
          <li><RouterLink to="/portal/downloads" class="transition hover:text-accent">Downloads</RouterLink></li>
          <li><RouterLink to="/register" class="transition hover:text-accent">Request Access</RouterLink></li>
          <li><RouterLink to="/contact" class="transition hover:text-accent">Contact</RouterLink></li>
        </ul>
      </div>
      <div>
        <h4 class="mb-5 text-xs font-bold tracking-[0.2em] text-slate-300 uppercase">Contact</h4>
        <ul class="space-y-3 text-sm text-slate-400">
          <li v-if="s?.contact.phone">{{ s.contact.phone }}</li>
          <li v-if="s?.contact.email">
            <a :href="`mailto:${s.contact.email}`" class="transition hover:text-accent">{{ s.contact.email }}</a>
          </li>
          <li v-if="s?.contact.working_hours">{{ s.contact.working_hours }}</li>
        </ul>
      </div>
    </div>
    <div class="relative z-10 border-t border-white/10">
      <div class="container-page flex flex-col items-center justify-between gap-2 py-6 text-xs text-slate-500 sm:flex-row">
        <span>{{ s?.footer.copyright || `© ${year} ${s?.company.name || ''}. All rights reserved.` }}</span>
        <span class="text-slate-600">Mission-critical technology, delivered.</span>
      </div>
    </div>
  </footer>
</template>
