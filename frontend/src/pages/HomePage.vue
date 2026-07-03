<script setup lang="ts">
import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useSettingsStore } from '@/stores/settings'
import { getProducts, getSolutions, getBrands, getPosts } from '@/api/wp'
import HeroCanvas from '@/components/home/HeroCanvas.vue'
import ProductCard from '@/components/common/ProductCard.vue'

const store = useSettingsStore()
const home = computed(() => store.settings?.homepage)
const company = computed(() => store.settings?.company)

const { data: products } = useQuery({
  queryKey: ['products', 'featured'],
  queryFn: () => getProducts({ per_page: 4 }),
})
const { data: solutions } = useQuery({ queryKey: ['solutions'], queryFn: getSolutions })
const { data: brands } = useQuery({ queryKey: ['brands'], queryFn: getBrands })
const { data: posts } = useQuery({
  queryKey: ['posts', 'latest'],
  queryFn: () => getPosts({ per_page: 3 }),
})
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-surface text-white">
      <HeroCanvas :variant="home?.hero_animation || 'network'" />
      <div class="container-page relative z-10 flex min-h-[70vh] flex-col items-start justify-center py-24">
        <p class="mb-3 text-sm font-semibold tracking-[0.25em] text-accent uppercase">
          {{ company?.slogan || 'Mission-Critical Technology' }}
        </p>
        <h1 class="max-w-3xl text-4xl leading-tight font-bold sm:text-5xl lg:text-6xl">
          {{ home?.hero_title || 'Advanced Defense & Communication Systems' }}
        </h1>
        <p class="mt-5 max-w-2xl text-lg text-slate-300">
          {{ home?.hero_subtitle || 'Integrated tactical communication, surveillance and command & control solutions for defense and security organizations.' }}
        </p>
        <div class="mt-8 flex flex-wrap gap-4">
          <RouterLink :to="home?.hero_cta_link || '/solutions'" class="btn-primary bg-accent text-primary hover:bg-accent/80">
            {{ home?.hero_cta_label || 'Explore Solutions' }}
          </RouterLink>
          <RouterLink to="/contact" class="btn-outline border-white/40 text-white hover:border-accent hover:text-accent">
            Contact Us
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Statistics -->
    <section v-if="home?.statistics?.length" class="border-b border-slate-200 bg-white">
      <div class="container-page grid grid-cols-2 gap-8 py-12 sm:grid-cols-4">
        <div v-for="stat in home.statistics" :key="stat.label" class="text-center">
          <div class="text-3xl font-bold text-primary sm:text-4xl">{{ stat.value }}</div>
          <div class="mt-1 text-sm text-slate-500">{{ stat.label }}</div>
        </div>
      </div>
    </section>

    <!-- Featured Solutions -->
    <section class="bg-slate-50 py-16">
      <div class="container-page">
        <div class="mb-8 flex items-end justify-between">
          <h2 class="section-title">Solutions</h2>
          <RouterLink to="/solutions" class="text-sm font-semibold text-accent hover:underline">View all →</RouterLink>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <RouterLink
            v-for="sol in solutions?.slice(0, 4)"
            :key="sol.id"
            :to="`/solutions/${sol.slug}`"
            class="group rounded-lg border border-slate-200 bg-white p-6 transition hover:border-accent hover:shadow-lg"
          >
            <h3 class="font-semibold text-primary group-hover:text-accent">{{ sol.title }}</h3>
            <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ sol.excerpt }}</p>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16">
      <div class="container-page">
        <div class="mb-8 flex items-end justify-between">
          <h2 class="section-title">Featured Products</h2>
          <RouterLink to="/products" class="text-sm font-semibold text-accent hover:underline">Browse catalog →</RouterLink>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <ProductCard v-for="p in products?.items" :key="p.id" :product="p" />
        </div>
      </div>
    </section>

    <!-- Brands -->
    <section v-if="brands?.length" class="border-y border-slate-200 bg-white py-12">
      <div class="container-page">
        <h2 class="mb-8 text-center text-sm font-semibold tracking-widest text-slate-400 uppercase">Trusted Brands & Partners</h2>
        <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6">
          <RouterLink v-for="b in brands" :key="b.id" :to="`/brands/${b.slug}`" class="opacity-60 grayscale transition hover:opacity-100 hover:grayscale-0">
            <img v-if="b.logo" :src="b.logo.url" :alt="b.title" class="h-10 w-auto" loading="lazy" />
            <span v-else class="font-semibold text-slate-500">{{ b.title }}</span>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Latest News -->
    <section v-if="posts?.items?.length" class="py-16">
      <div class="container-page">
        <div class="mb-8 flex items-end justify-between">
          <h2 class="section-title">Latest News</h2>
          <RouterLink to="/news" class="text-sm font-semibold text-accent hover:underline">All news →</RouterLink>
        </div>
        <div class="grid gap-6 md:grid-cols-3">
          <RouterLink
            v-for="post in posts.items"
            :key="post.id"
            :to="`/news/${post.slug}`"
            class="group overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:shadow-lg"
          >
            <img v-if="post.image" :src="post.image.url" :alt="post.image.alt" class="aspect-video w-full object-cover" loading="lazy" />
            <div class="p-5">
              <time class="text-xs text-slate-400">{{ new Date(post.date).toLocaleDateString() }}</time>
              <h3 class="mt-1 font-semibold text-primary group-hover:text-accent">{{ post.title }}</h3>
              <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ post.excerpt }}</p>
            </div>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-primary py-16 text-white">
      <div class="container-page flex flex-col items-center text-center">
        <h2 class="text-3xl font-bold">{{ home?.cta?.title || 'Ready to discuss your requirements?' }}</h2>
        <p class="mt-3 max-w-2xl text-slate-300">
          {{ home?.cta?.text || 'Our engineers will help you design the right solution for your mission.' }}
        </p>
        <RouterLink :to="home?.cta?.button_link || '/contact'" class="btn-primary mt-7 bg-accent text-primary hover:bg-accent/80">
          {{ home?.cta?.button_label || 'Request Consultation' }}
        </RouterLink>
      </div>
    </section>
  </div>
</template>
