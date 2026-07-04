<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useInquiryStore } from '@/stores/inquiry'
import { useQuery } from '@tanstack/vue-query'
import { getProducts, getBrands, getProductCategories } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'
import ProductCard from '@/components/common/ProductCard.vue'
import AppPagination from '@/components/common/AppPagination.vue'

const page = ref(1)
const search = ref('')
const brand = ref('')
const category = ref('')

const params = computed(() => ({
  page: page.value,
  per_page: 12,
  search: search.value || undefined,
  brand: brand.value || undefined,
  category: category.value || undefined,
}))

const { data, isLoading } = useQuery({
  queryKey: ['products', params],
  queryFn: () => getProducts(params.value),
})
const { data: brands } = useQuery({ queryKey: ['brands'], queryFn: getBrands })
const { data: categories } = useQuery({ queryKey: ['product-categories'], queryFn: getProductCategories })

function setPage(n: number) {
  page.value = n
  window.scrollTo({ top: 0 })
}

function resetPage() {
  page.value = 1
}

// View switcher: list or 1–4 column grid, remembered across visits
type ViewMode = 'list' | '1' | '2' | '3' | '4'
const VIEW_KEY = 'dtc_products_view'
const view = ref<ViewMode>((localStorage.getItem(VIEW_KEY) as ViewMode) || '4')
watch(view, (v) => localStorage.setItem(VIEW_KEY, v))

const viewOptions: { key: ViewMode; label: string }[] = [
  { key: 'list', label: 'List' },
  { key: '1', label: '1×' },
  { key: '2', label: '2×' },
  { key: '3', label: '3×' },
  { key: '4', label: '4×' },
]

const gridClass = computed(() => {
  switch (view.value) {
    case '1': return 'grid-cols-1'
    case '2': return 'grid-cols-2'
    case '3': return 'grid-cols-2 md:grid-cols-3'
    default: return 'grid-cols-2 md:grid-cols-4'
  }
})

const inquiry = useInquiryStore()
</script>

<template>
  <div>
    <PageHero title="Product Catalog" subtitle="Browse our range of defense and communication products. Request a quote for any product — no pricing, no checkout." />

    <div class="container-page py-10">
      <div class="mb-8 flex flex-col gap-4 lg:flex-row">
        <input
          v-model="search"
          type="search"
          placeholder="Search products…"
          class="input focus:border-accent focus:outline-none lg:max-w-xs"
          @input="resetPage"
        />
        <select v-model="brand" class="input" @change="resetPage">
          <option value="">All Brands</option>
          <option v-for="b in brands" :key="b.id" :value="b.slug">{{ b.title }}</option>
        </select>
        <select v-model="category" class="input" @change="resetPage">
          <option value="">All Categories</option>
          <option v-for="c in categories" :key="c.id" :value="c.slug">
            {{ c.parent ? '— ' : '' }}{{ c.name }}
          </option>
        </select>

        <!-- View switcher -->
        <div class="flex items-center gap-1.5 lg:ml-auto" role="group" aria-label="View layout">
          <span class="mr-1 text-xs font-semibold tracking-wide text-slate-400 uppercase">View</span>
          <button
            v-for="opt in viewOptions"
            :key="opt.key"
            class="rounded-lg px-3.5 py-2 text-xs font-bold transition"
            :class="view === opt.key ? 'bg-primary text-white shadow' : 'border border-slate-200 text-slate-500 hover:border-accent hover:text-primary'"
            :aria-pressed="view === opt.key"
            @click="view = opt.key"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading products…</div>
      <div v-else-if="!data?.items.length" class="py-20 text-center text-slate-400">No products found.</div>

      <!-- List view -->
      <div v-else-if="view === 'list'" class="space-y-4">
        <div
          v-for="p in data.items"
          :key="p.id"
          class="card card-hover group flex flex-col gap-5 p-5 sm:flex-row sm:items-center"
        >
          <RouterLink :to="`/products/${p.slug}`" class="shrink-0">
            <div class="h-32 w-full rounded-xl bg-gradient-to-br from-slate-50 to-slate-100 sm:w-32">
              <img
                v-if="p.image"
                :src="p.image.url"
                :alt="p.image.alt || p.title"
                class="h-full w-full object-contain p-2"
                loading="lazy"
              />
            </div>
          </RouterLink>
          <div class="min-w-0 flex-1">
            <div v-if="p.brand" class="text-[11px] font-bold tracking-[0.15em] text-accent uppercase">{{ p.brand.name }}</div>
            <RouterLink :to="`/products/${p.slug}`" class="font-display mt-0.5 block font-semibold text-primary transition group-hover:text-accent">
              {{ p.title }}
            </RouterLink>
            <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ p.excerpt }}</p>
          </div>
          <button
            class="btn-outline shrink-0 !px-4 !py-2 text-xs"
            :disabled="inquiry.has(p.id)"
            @click="inquiry.add({ id: p.id, title: p.title, image: p.image?.url ?? null })"
          >
            {{ inquiry.has(p.id) ? '✓ In Inquiry' : '+ Add to Inquiry' }}
          </button>
        </div>
      </div>

      <!-- Grid views -->
      <div v-else class="grid gap-6" :class="gridClass">
        <ProductCard v-for="p in data.items" :key="p.id" :product="p" />
      </div>

      <AppPagination v-if="data" :page="page" :pages="data.pages" @change="setPage" />
    </div>
  </div>
</template>
