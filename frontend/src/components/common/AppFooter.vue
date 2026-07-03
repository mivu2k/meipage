<script setup lang="ts">
import { computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const store = useSettingsStore()
const s = computed(() => store.settings)
const year = new Date().getFullYear()
</script>

<template>
  <footer class="bg-primary text-slate-300">
    <div class="container-page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <img v-if="s?.company.logo_dark" :src="s.company.logo_dark" :alt="s.company.name" class="mb-4 h-9 w-auto" />
        <h3 v-else class="mb-4 text-lg font-bold text-white">{{ s?.company.name || 'Defense Tech' }}</h3>
        <p class="text-sm leading-relaxed">{{ s?.footer.about || s?.company.slogan }}</p>
      </div>
      <div>
        <h4 class="mb-4 text-sm font-semibold tracking-wider text-white uppercase">Explore</h4>
        <ul class="space-y-2 text-sm">
          <li><RouterLink to="/products" class="hover:text-accent">Products</RouterLink></li>
          <li><RouterLink to="/solutions" class="hover:text-accent">Solutions</RouterLink></li>
          <li><RouterLink to="/brands" class="hover:text-accent">Brands</RouterLink></li>
          <li><RouterLink to="/news" class="hover:text-accent">News</RouterLink></li>
          <li><RouterLink to="/careers" class="hover:text-accent">Careers</RouterLink></li>
        </ul>
      </div>
      <div>
        <h4 class="mb-4 text-sm font-semibold tracking-wider text-white uppercase">Support</h4>
        <ul class="space-y-2 text-sm">
          <li><RouterLink to="/support" class="hover:text-accent">Support Center</RouterLink></li>
          <li><RouterLink to="/portal" class="hover:text-accent">Customer Portal</RouterLink></li>
          <li><RouterLink to="/portal/downloads" class="hover:text-accent">Downloads</RouterLink></li>
          <li><RouterLink to="/contact" class="hover:text-accent">Contact</RouterLink></li>
        </ul>
      </div>
      <div>
        <h4 class="mb-4 text-sm font-semibold tracking-wider text-white uppercase">Contact</h4>
        <ul class="space-y-2 text-sm">
          <li v-if="s?.contact.phone">{{ s.contact.phone }}</li>
          <li v-if="s?.contact.email">
            <a :href="`mailto:${s.contact.email}`" class="hover:text-accent">{{ s.contact.email }}</a>
          </li>
          <li v-if="s?.contact.working_hours">{{ s.contact.working_hours }}</li>
        </ul>
        <div class="mt-4 flex gap-4">
          <a v-for="(url, name) in s?.social" :key="name" :href="url" target="_blank" rel="noopener" class="text-sm capitalize hover:text-accent">
            {{ name }}
          </a>
        </div>
      </div>
    </div>
    <div class="border-t border-white/10">
      <div class="container-page py-5 text-xs text-slate-400">
        {{ s?.footer.copyright || `© ${year} ${s?.company.name || ''}. All rights reserved.` }}
      </div>
    </div>
  </footer>
</template>
