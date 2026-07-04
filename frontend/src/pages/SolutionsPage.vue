<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { getSolutions } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'

const { data: solutions, isLoading } = useQuery({ queryKey: ['solutions'], queryFn: getSolutions })

type ViewMode = 'list' | '1' | '2' | '3' | '4'
const VIEW_KEY = 'dtc_solutions_view'
const view = ref<ViewMode>((localStorage.getItem(VIEW_KEY) as ViewMode) || '3')
watch(view, (v) => localStorage.setItem(VIEW_KEY, v))

const gridClass = computed(() => {
  switch (view.value) {
    case '1': return 'grid-cols-1'
    case '2': return 'grid-cols-2'
    case '3': return 'grid-cols-2 md:grid-cols-3'
    case '4': return 'grid-cols-2 md:grid-cols-4'
    default: return ''
  }
})

const options: { key: ViewMode; label: string }[] = [
  { key: 'list', label: 'List' },
  { key: '1', label: '1×' },
  { key: '2', label: '2×' },
  { key: '3', label: '3×' },
  { key: '4', label: '4×' },
]
</script>

<template>
  <div>
    <PageHero title="Solutions" subtitle="End-to-end defense technology solutions: tactical communication, surveillance, command & control, and more." />
    <div class="container-page py-10">
      <!-- View switcher -->
      <div class="mb-8 flex items-center justify-end gap-1" role="group" aria-label="View layout">
        <span class="mr-2 text-xs font-semibold tracking-wide text-slate-400 uppercase">View</span>
        <button
          v-for="opt in options"
          :key="opt.key"
          class="rounded-lg px-3.5 py-2 text-xs font-bold transition"
          :class="view === opt.key ? 'bg-primary text-white shadow' : 'border border-slate-200 text-slate-500 hover:border-accent hover:text-primary'"
          :aria-pressed="view === opt.key"
          @click="view = opt.key"
        >
          {{ opt.label }}
        </button>
      </div>

      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading…</div>

      <!-- List view -->
      <div v-else-if="view === 'list'" class="space-y-4">
        <RouterLink
          v-for="s in solutions"
          :key="s.id"
          :to="`/solutions/${s.slug}`"
          class="card card-hover group flex flex-col gap-5 overflow-hidden p-5 sm:flex-row sm:items-center"
        >
          <img
            v-if="s.image"
            :src="s.image.url"
            :alt="s.image.alt || s.title"
            class="aspect-video w-full shrink-0 rounded-xl object-cover sm:w-56"
            loading="lazy"
          />
          <div class="min-w-0">
            <h2 class="font-display text-lg font-semibold text-primary transition group-hover:text-accent">{{ s.title }}</h2>
            <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ s.excerpt }}</p>
            <span class="mt-3 inline-block text-sm font-semibold text-accent">Explore →</span>
          </div>
        </RouterLink>
      </div>

      <!-- Grid views -->
      <div v-else class="grid gap-6" :class="gridClass">
        <RouterLink
          v-for="s in solutions"
          :key="s.id"
          :to="`/solutions/${s.slug}`"
          class="card card-hover group overflow-hidden"
        >
          <div v-if="s.image" class="overflow-hidden">
            <img
              :src="s.image.url"
              :alt="s.image.alt || s.title"
              class="aspect-video w-full object-cover transition-transform duration-500 group-hover:scale-105"
              loading="lazy"
            />
          </div>
          <div :class="view === '4' ? 'p-4' : 'p-6'">
            <h2 class="font-display font-semibold text-primary transition group-hover:text-accent" :class="view === '4' ? 'text-sm' : 'text-lg'">
              {{ s.title }}
            </h2>
            <p class="mt-2 line-clamp-3 text-sm text-slate-500" :class="view === '4' ? 'hidden sm:block' : ''">{{ s.excerpt }}</p>
          </div>
        </RouterLink>
      </div>
    </div>
  </div>
</template>
