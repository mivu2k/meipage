<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { getProduct } from '@/api/wp'
import { useInquiryStore } from '@/stores/inquiry'

const route = useRoute()
const slug = computed(() => route.params.slug as string)
const inquiry = useInquiryStore()
const activeImage = ref(0)
const tab = ref<'features' | 'specs' | 'applications' | 'downloads'>('features')

const { data: product, isLoading } = useQuery({
  queryKey: ['product', slug],
  queryFn: () => getProduct(slug.value),
})

const images = computed(() => {
  if (!product.value) return []
  return [product.value.image, ...product.value.gallery].filter((i) => i !== null)
})

function addToInquiry() {
  if (!product.value) return
  inquiry.add({
    id: product.value.id,
    title: product.value.title,
    image: product.value.image?.url ?? null,
  })
}
</script>

<template>
  <div class="container-page py-10">
    <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading…</div>

    <div v-else-if="product">
      <nav class="mb-6 text-sm text-slate-500" aria-label="Breadcrumb">
        <RouterLink to="/products" class="hover:text-primary">Products</RouterLink>
        <span class="mx-2">/</span>
        <span class="text-slate-800">{{ product.title }}</span>
      </nav>

      <div class="grid gap-10 lg:grid-cols-2">
        <!-- Gallery -->
        <div>
          <div class="aspect-square rounded-lg border border-slate-200 bg-slate-50">
            <img
              v-if="images[activeImage]"
              :src="images[activeImage]!.url"
              :alt="images[activeImage]!.alt || product.title"
              class="h-full w-full object-contain p-6"
            />
          </div>
          <div v-if="images.length > 1" class="mt-3 flex gap-2 overflow-x-auto">
            <button
              v-for="(img, i) in images"
              :key="img!.id"
              class="h-16 w-16 shrink-0 rounded-md border-2 bg-slate-50 p-1"
              :class="i === activeImage ? 'border-accent' : 'border-slate-200'"
              @click="activeImage = i"
            >
              <img :src="img!.url" :alt="img!.alt" class="h-full w-full object-contain" />
            </button>
          </div>
        </div>

        <!-- Info -->
        <div>
          <div v-if="product.brand" class="text-sm font-semibold tracking-wide text-accent uppercase">
            {{ product.brand.name }}
          </div>
          <h1 class="mt-1 text-3xl font-bold text-primary">{{ product.title }}</h1>
          <div class="prose-wp mt-4 text-slate-600" v-html="product.excerpt" />

          <div class="mt-8 flex flex-wrap gap-3">
            <button class="btn-primary" :disabled="inquiry.has(product.id)" @click="addToInquiry">
              {{ inquiry.has(product.id) ? '✓ In Inquiry List' : 'Add to Inquiry' }}
            </button>
            <RouterLink to="/inquiry" class="btn-outline">Request Quote</RouterLink>
            <RouterLink to="/contact" class="btn-outline">Contact Expert</RouterLink>
          </div>

          <ul v-if="product.certifications?.length" class="mt-6 flex flex-wrap gap-2">
            <li v-for="cert in product.certifications" :key="cert" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
              {{ cert }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mt-14">
        <div class="flex gap-1 border-b border-slate-200" role="tablist">
          <button
            v-for="t in (['features', 'specs', 'applications', 'downloads'] as const)"
            :key="t"
            role="tab"
            :aria-selected="tab === t"
            class="rounded-t-md px-5 py-2.5 text-sm font-semibold capitalize"
            :class="tab === t ? 'border-b-2 border-accent text-primary' : 'text-slate-500 hover:text-primary'"
            @click="tab = t"
          >
            {{ t === 'specs' ? 'Specifications' : t }}
          </button>
        </div>

        <div class="py-8">
          <ul v-if="tab === 'features'" class="grid gap-3 sm:grid-cols-2">
            <li v-for="f in product.features" :key="f" class="flex items-start gap-2 text-sm text-slate-700">
              <span class="mt-0.5 text-accent">✓</span>{{ f }}
            </li>
            <li v-if="!product.features?.length" class="text-slate-400">No features listed.</li>
          </ul>

          <div v-else-if="tab === 'specs'">
            <div v-if="product.specifications_html" class="specs-html max-w-4xl" v-html="product.specifications_html" />
            <table v-else-if="product.specifications?.length" class="w-full max-w-3xl text-sm">
              <tbody>
                <tr v-for="spec in product.specifications" :key="spec.label" class="border-b border-slate-100">
                  <td class="w-1/3 py-2.5 font-medium text-slate-500">{{ spec.label }}</td>
                  <td class="py-2.5 text-slate-800">{{ spec.value }}</td>
                </tr>
              </tbody>
            </table>
            <p v-else class="text-slate-400">No specifications listed.</p>
          </div>

          <ul v-else-if="tab === 'applications'" class="grid gap-3 sm:grid-cols-2">
            <li v-for="a in product.applications" :key="a" class="flex items-start gap-2 text-sm text-slate-700">
              <span class="mt-0.5 text-accent">▸</span>{{ a }}
            </li>
          </ul>

          <ul v-else class="max-w-3xl divide-y divide-slate-100">
            <li v-for="d in product.downloads" :key="d.id" class="flex items-center justify-between py-3">
              <div>
                <div class="text-sm font-medium text-slate-800">{{ d.title }}</div>
                <div class="text-xs text-slate-400 capitalize">{{ d.type }} <span v-if="d.version">· v{{ d.version }}</span> <span v-if="d.size">· {{ d.size }}</span></div>
              </div>
              <a v-if="d.url" :href="d.url" class="btn-outline text-xs" download>Download</a>
              <RouterLink v-else to="/login" class="text-xs font-medium text-slate-400">Login required</RouterLink>
            </li>
            <li v-if="!product.downloads?.length" class="py-3 text-slate-400">No public downloads.</li>
          </ul>
        </div>
      </div>

      <div class="prose-wp mt-6 max-w-3xl text-slate-700" v-html="product.content" />
    </div>
  </div>
</template>

<style scoped>
/* Styling for pasted HTML specification tables */
.specs-html :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin: 1.25rem 0;
  font-size: 0.875rem;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 0 0 1px var(--color-slate-200);
}
.specs-html :deep(th),
.specs-html :deep(td) {
  padding: 0.65rem 1rem;
  text-align: left;
  border-bottom: 1px solid var(--color-slate-100);
}
.specs-html :deep(th),
.specs-html :deep(thead td) {
  background: var(--dtc-primary, #0b1f3a);
  color: #fff;
  font-weight: 600;
}
.specs-html :deep(tbody tr:nth-child(even)) {
  background: var(--color-slate-50);
}
.specs-html :deep(caption),
.specs-html :deep(h3),
.specs-html :deep(h4) {
  font-family: var(--font-display);
  font-weight: 700;
  color: var(--dtc-primary, #0b1f3a);
  margin: 1.5rem 0 0.5rem;
  text-align: left;
}
</style>
