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
  <div class="group flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:shadow-lg">
    <RouterLink :to="`/products/${product.slug}`" class="block">
      <div class="aspect-square bg-slate-100">
        <img
          v-if="product.image"
          :src="product.image.url"
          :alt="product.image.alt || product.title"
          class="h-full w-full object-contain p-4"
          loading="lazy"
        />
      </div>
    </RouterLink>
    <div class="flex flex-1 flex-col p-4">
      <div v-if="product.brand" class="text-xs font-semibold tracking-wide text-accent uppercase">
        {{ product.brand.name }}
      </div>
      <RouterLink :to="`/products/${product.slug}`" class="mt-1 font-semibold text-primary group-hover:text-accent">
        {{ product.title }}
      </RouterLink>
      <p class="mt-1 line-clamp-2 flex-1 text-sm text-slate-600">{{ product.excerpt }}</p>
      <button
        class="btn-outline mt-4 justify-center text-xs"
        :disabled="inquiry.has(product.id)"
        @click="addToInquiry"
      >
        {{ inquiry.has(product.id) ? 'In Inquiry List' : 'Add to Inquiry' }}
      </button>
    </div>
  </div>
</template>
