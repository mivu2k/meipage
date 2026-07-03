<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { getProducts, getBrands, getProductCategories } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'
import ProductCard from '@/components/common/ProductCard.vue'

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

function resetPage() {
  page.value = 1
}
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
          class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm focus:border-accent focus:outline-none lg:max-w-xs"
          @input="resetPage"
        />
        <select v-model="brand" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm" @change="resetPage">
          <option value="">All Brands</option>
          <option v-for="b in brands" :key="b.id" :value="b.slug">{{ b.title }}</option>
        </select>
        <select v-model="category" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm" @change="resetPage">
          <option value="">All Categories</option>
          <option v-for="c in categories" :key="c.id" :value="c.slug">
            {{ c.parent ? '— ' : '' }}{{ c.name }}
          </option>
        </select>
      </div>

      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading products…</div>
      <div v-else-if="!data?.items.length" class="py-20 text-center text-slate-400">No products found.</div>
      <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <ProductCard v-for="p in data.items" :key="p.id" :product="p" />
      </div>

      <nav v-if="data && data.pages > 1" class="mt-10 flex justify-center gap-2" aria-label="Pagination">
        <button
          v-for="n in data.pages"
          :key="n"
          class="h-9 w-9 rounded-md text-sm font-medium"
          :class="n === page ? 'bg-primary text-white' : 'border border-slate-300 text-slate-600 hover:border-primary'"
          @click="page = n"
        >
          {{ n }}
        </button>
      </nav>
    </div>
  </div>
</template>
