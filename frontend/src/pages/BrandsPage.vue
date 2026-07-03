<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query'
import { getBrands } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'

const { data: brands, isLoading } = useQuery({ queryKey: ['brands'], queryFn: getBrands })
</script>

<template>
  <div>
    <PageHero title="Brands" subtitle="Manufacturers and technology partners we represent." />
    <div class="container-page py-10">
      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading…</div>
      <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <RouterLink
          v-for="b in brands"
          :key="b.id"
          :to="`/brands/${b.slug}`"
          class="group flex flex-col items-center rounded-lg border border-slate-200 bg-white p-8 text-center transition hover:border-accent hover:shadow-lg"
        >
          <img v-if="b.logo" :src="b.logo.url" :alt="b.title" class="mb-4 h-14 w-auto object-contain" loading="lazy" />
          <h2 class="font-semibold text-primary group-hover:text-accent">{{ b.title }}</h2>
          <p v-if="b.country" class="mt-1 text-sm text-slate-500">{{ b.country }}</p>
        </RouterLink>
      </div>
    </div>
  </div>
</template>
