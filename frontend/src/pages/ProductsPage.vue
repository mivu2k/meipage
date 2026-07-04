<script setup lang="ts">
import { ref, computed } from 'vue'
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
      </div>

      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading products…</div>
      <div v-else-if="!data?.items.length" class="py-20 text-center text-slate-400">No products found.</div>
      <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <ProductCard v-for="p in data.items" :key="p.id" :product="p" />
      </div>

      <AppPagination v-if="data" :page="page" :pages="data.pages" @change="setPage" />
    </div>
  </div>
</template>
