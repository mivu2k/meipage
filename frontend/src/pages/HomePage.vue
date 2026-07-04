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
    <section class="section-dark">
      <HeroCanvas :variant="home?.hero_animation || 'network'" />
      <div class="glow -bottom-32 -left-32 h-96 w-96" aria-hidden="true" />
      <div class="container-page relative z-10 flex min-h-[78vh] flex-col items-start justify-center py-28">
        <p v-reveal class="section-kicker flex items-center gap-2">
          <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-accent" aria-hidden="true" />
          {{ company?.slogan || 'Mission-Critical Technology' }}
        </p>
        <h1 v-reveal="100" class="max-w-4xl text-4xl leading-[1.05] font-bold tracking-tight sm:text-6xl lg:text-7xl">
          {{ home?.hero_title || 'Advanced Defense & Communication Systems' }}
        </h1>
        <p v-reveal="200" class="mt-6 max-w-2xl text-lg text-slate-300">
          {{ home?.hero_subtitle || 'Integrated tactical communication, surveillance and command & control solutions for defense and security organizations.' }}
        </p>
        <div v-reveal="300" class="mt-9 flex flex-wrap gap-4">
          <RouterLink v-magnetic :to="home?.hero_cta_link || '/solutions'" class="btn-accent">
            {{ home?.hero_cta_label || 'Explore Solutions' }} →
          </RouterLink>
          <RouterLink v-magnetic to="/contact" class="btn-ghost-dark">Contact Us</RouterLink>
        </div>
      </div>
    </section>

    <!-- Statistics -->
    <section v-if="home?.statistics?.length" class="border-b border-slate-200 bg-white">
      <div class="container-page grid grid-cols-2 gap-8 py-14 sm:grid-cols-4">
        <div v-for="(stat, i) in home.statistics" :key="stat.label" v-reveal="i * 80" class="text-center">
          <div class="font-display text-4xl font-bold text-primary sm:text-5xl">{{ stat.value }}</div>
          <div class="mt-2 text-sm text-slate-500">{{ stat.label }}</div>
        </div>
      </div>
    </section>

    <!-- Featured Solutions -->
    <section class="bg-slate-50 py-24">
      <div class="container-page">
        <div class="mb-10 flex items-end justify-between">
          <div>
            <p class="section-kicker">What we deliver</p>
            <h2 class="section-title">Solutions</h2>
          </div>
          <RouterLink to="/solutions" class="text-sm font-semibold text-accent hover:underline">View all →</RouterLink>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <RouterLink
            v-for="(sol, i) in solutions?.slice(0, 4)"
            :key="sol.id"
            v-reveal="i * 80"
            :to="`/solutions/${sol.slug}`"
            class="card card-hover group p-7"
          >
            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-accent/10 text-accent transition group-hover:bg-accent group-hover:text-surface" aria-hidden="true">
              ◈
            </div>
            <h3 class="font-display font-semibold text-primary transition group-hover:text-accent">{{ sol.title }}</h3>
            <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ sol.excerpt }}</p>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-24">
      <div class="container-page">
        <div class="mb-10 flex items-end justify-between">
          <div>
            <p class="section-kicker">Catalog</p>
            <h2 class="section-title">Featured Products</h2>
          </div>
          <RouterLink to="/products" class="text-sm font-semibold text-accent hover:underline">Browse catalog →</RouterLink>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div v-for="(p, i) in products?.items" :key="p.id" v-reveal="i * 80">
            <ProductCard :product="p" />
          </div>
        </div>
      </div>
    </section>

    <!-- Brands -->
    <section v-if="brands?.length" class="border-y border-slate-200 bg-white py-14">
      <div class="container-page">
        <h2 class="mb-10 text-center text-xs font-bold tracking-[0.3em] text-slate-400 uppercase">Trusted Brands & Partners</h2>
        <div class="flex flex-wrap items-center justify-center gap-x-14 gap-y-8">
          <RouterLink
            v-for="b in brands"
            :key="b.id"
            :to="`/brands/${b.slug}`"
            class="opacity-50 grayscale transition duration-300 hover:opacity-100 hover:grayscale-0"
          >
            <img v-if="b.logo" :src="b.logo.url" :alt="b.title" class="h-10 w-auto" loading="lazy" />
            <span v-else class="font-display text-lg font-semibold text-slate-500">{{ b.title }}</span>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Latest News -->
    <section v-if="posts?.items?.length" class="py-24">
      <div class="container-page">
        <div class="mb-10 flex items-end justify-between">
          <div>
            <p class="section-kicker">Insights</p>
            <h2 class="section-title">Latest News</h2>
          </div>
          <RouterLink to="/news" class="text-sm font-semibold text-accent hover:underline">All news →</RouterLink>
        </div>
        <div class="grid gap-6 md:grid-cols-3">
          <RouterLink
            v-for="(post, i) in posts.items"
            :key="post.id"
            v-reveal="i * 80"
            :to="`/news/${post.slug}`"
            class="card card-hover group overflow-hidden"
          >
            <div class="overflow-hidden">
              <img
                v-if="post.image"
                :src="post.image.url"
                :alt="post.image.alt"
                class="aspect-video w-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
              />
            </div>
            <div class="p-6">
              <time class="text-xs font-medium text-slate-400">{{ new Date(post.date).toLocaleDateString() }}</time>
              <h3 class="font-display mt-2 font-semibold text-primary transition group-hover:text-accent">{{ post.title }}</h3>
              <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ post.excerpt }}</p>
            </div>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="section-dark py-24">
      <div class="tech-grid" aria-hidden="true" />
      <div class="glow top-0 right-1/4 h-64 w-64" aria-hidden="true" />
      <div class="container-page relative z-10 flex flex-col items-center text-center">
        <h2 v-reveal class="max-w-2xl text-3xl font-bold sm:text-4xl">
          {{ home?.cta?.title || 'Ready to discuss your requirements?' }}
        </h2>
        <p v-reveal="100" class="mt-4 max-w-2xl text-slate-300">
          {{ home?.cta?.text || 'Our engineers will help you design the right solution for your mission.' }}
        </p>
        <RouterLink v-reveal="200" v-magnetic :to="home?.cta?.button_link || '/contact'" class="btn-accent mt-9">
          {{ home?.cta?.button_label || 'Request Consultation' }} →
        </RouterLink>
      </div>
    </section>
  </div>
</template>
