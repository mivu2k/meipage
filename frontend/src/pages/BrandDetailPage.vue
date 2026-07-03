<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { getBrand, getProducts } from '@/api/wp'
import ProductCard from '@/components/common/ProductCard.vue'

const route = useRoute()
const slug = computed(() => route.params.slug as string)

const { data: brand, isLoading } = useQuery({
  queryKey: ['brand', slug],
  queryFn: () => getBrand(slug.value),
})
const { data: products } = useQuery({
  queryKey: ['products', 'brand', slug],
  queryFn: () => getProducts({ brand: slug.value, per_page: 24 }),
})
</script>

<template>
  <div>
    <div v-if="isLoading" class="container-page py-20 text-center text-slate-400">Loading…</div>
    <template v-else-if="brand">
      <section class="bg-primary py-14 text-white">
        <div class="container-page flex flex-col items-start gap-6 sm:flex-row sm:items-center">
          <div v-if="brand.logo" class="rounded-lg bg-white p-4">
            <img :src="brand.logo.url" :alt="brand.title" class="h-14 w-auto object-contain" />
          </div>
          <div>
            <h1 class="text-3xl font-bold sm:text-4xl">{{ brand.title }}</h1>
            <p v-if="brand.country" class="mt-1 text-slate-300">{{ brand.country }}</p>
            <a v-if="brand.website" :href="brand.website" target="_blank" rel="noopener" class="mt-1 inline-block text-sm text-accent hover:underline">
              Official website ↗
            </a>
          </div>
        </div>
      </section>

      <div class="container-page py-10">
        <div class="prose-wp max-w-3xl text-slate-700" v-html="brand.content" />

        <h2 class="section-title mt-12 mb-6">Products by {{ brand.title }}</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <ProductCard v-for="p in products?.items" :key="p.id" :product="p" />
        </div>
        <p v-if="products && !products.items.length" class="text-slate-400">No products listed for this brand yet.</p>
      </div>
    </template>
  </div>
</template>
