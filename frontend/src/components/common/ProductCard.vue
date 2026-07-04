<script setup lang="ts">
import type { Product } from '@/api/types'
import { useInquiryStore } from '@/stores/inquiry'

const props = defineProps<{ product: Product }>()
const inquiry = useInquiryStore()

function addToInquiry() {
  inquiry.add({
    id: props.product.id,
    title: props.product.title,
    image: props.product.image?.url ?? null,
  })
}
</script>

<template>
  <div class="card card-hover group flex flex-col overflow-hidden">
    <RouterLink :to="`/products/${product.slug}`" class="block overflow-hidden">
      <div class="relative aspect-square bg-gradient-to-br from-slate-50 to-slate-100">
        <img
          v-if="product.image"
          :src="product.image.url"
          :alt="product.image.alt || product.title"
          class="h-full w-full object-contain p-5 transition-transform duration-500 group-hover:scale-105"
          loading="lazy"
        />
        <div
          v-else
          class="flex h-full items-center justify-center text-4xl font-bold text-slate-200"
          aria-hidden="true"
        >
          {{ product.title.slice(0, 2).toUpperCase() }}
        </div>
      </div>
    </RouterLink>
    <div class="flex flex-1 flex-col p-5">
      <div v-if="product.brand" class="text-[11px] font-bold tracking-[0.15em] text-accent uppercase">
        {{ product.brand.name }}
      </div>
      <RouterLink
        :to="`/products/${product.slug}`"
        class="font-display mt-1.5 font-semibold text-primary transition group-hover:text-accent"
      >
        {{ product.title }}
      </RouterLink>
      <p class="mt-1.5 line-clamp-2 flex-1 text-sm text-slate-500">{{ product.excerpt }}</p>
      <button
        class="mt-4 w-full rounded-lg border border-slate-200 py-2.5 text-xs font-semibold text-slate-600 transition hover:border-accent hover:bg-accent/5 hover:text-primary disabled:border-accent/40 disabled:bg-accent/10 disabled:text-primary"
        :disabled="inquiry.has(product.id)"
        @click="addToInquiry"
      >
        {{ inquiry.has(product.id) ? '✓ In Inquiry List' : '+ Add to Inquiry' }}
      </button>
    </div>
  </div>
</template>
