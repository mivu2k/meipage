<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query'
import { getSolutions } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'

const { data: solutions, isLoading } = useQuery({ queryKey: ['solutions'], queryFn: getSolutions })
</script>

<template>
  <div>
    <PageHero title="Solutions" subtitle="End-to-end defense technology solutions: tactical communication, surveillance, command & control, and more." />
    <div class="container-page py-10">
      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading…</div>
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <RouterLink
          v-for="s in solutions"
          :key="s.id"
          :to="`/solutions/${s.slug}`"
          class="group overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-accent hover:shadow-lg"
        >
          <img v-if="s.image" :src="s.image.url" :alt="s.image.alt || s.title" class="aspect-video w-full object-cover" loading="lazy" />
          <div class="p-6">
            <h2 class="text-lg font-semibold text-primary group-hover:text-accent">{{ s.title }}</h2>
            <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ s.excerpt }}</p>
          </div>
        </RouterLink>
      </div>
    </div>
  </div>
</template>
