<script setup lang="ts">
import { ref } from 'vue'
import { useMutation } from '@tanstack/vue-query'
import { useInquiryStore } from '@/stores/inquiry'
import { submitInquiry } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'

const inquiry = useInquiryStore()
const form = ref({ name: '', email: '', organization: '', country: '', message: '' })

const submit = useMutation({
  mutationFn: () =>
    submitInquiry({
      ...form.value,
      products: inquiry.items.map((i) => ({ id: i.id, title: i.title, quantity: i.quantity })),
    }),
  onSuccess: () => inquiry.clear(),
})
</script>

<template>
  <div>
    <PageHero title="Request a Quote" subtitle="Review your selected products and submit a single inquiry. Our sales team will respond with a quotation." />

    <div class="container-page max-w-5xl py-12">
      <div v-if="submit.isSuccess.value" class="rounded-lg bg-green-50 p-10 text-center">
        <h2 class="text-xl font-bold text-green-700">Inquiry Submitted</h2>
        <p class="mt-2 text-green-600">Thank you. Our sales team will contact you with a quotation shortly.</p>
        <RouterLink to="/products" class="btn-primary mt-6">Continue Browsing</RouterLink>
      </div>

      <template v-else>
        <div v-if="!inquiry.items.length" class="py-16 text-center">
          <p class="text-slate-400">Your inquiry list is empty.</p>
          <RouterLink to="/products" class="btn-primary mt-6">Browse Products</RouterLink>
        </div>

        <div v-else class="grid gap-10 lg:grid-cols-2">
          <!-- Selected products -->
          <div>
            <h2 class="section-title mb-6">Selected Products ({{ inquiry.count }})</h2>
            <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200">
              <li v-for="item in inquiry.items" :key="item.id" class="flex items-center gap-4 p-4">
                <img v-if="item.image" :src="item.image" :alt="item.title" class="h-14 w-14 rounded-md bg-slate-50 object-contain" />
                <div class="min-w-0 flex-1">
                  <div class="truncate text-sm font-medium text-slate-800">{{ item.title }}</div>
                </div>
                <input
                  type="number"
                  min="1"
                  :value="item.quantity"
                  class="w-16 rounded-md border border-slate-300 px-2 py-1 text-center text-sm"
                  aria-label="Quantity"
                  @change="inquiry.setQuantity(item.id, Number(($event.target as HTMLInputElement).value))"
                />
                <button class="text-slate-400 hover:text-red-600" aria-label="Remove" @click="inquiry.remove(item.id)">✕</button>
              </li>
            </ul>
          </div>

          <!-- Inquiry form -->
          <div>
            <h2 class="section-title mb-6">Your Details</h2>
            <form class="space-y-4" @submit.prevent="submit.mutate()">
              <input v-model="form.name" required placeholder="Full name" class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
              <input v-model="form.email" required type="email" placeholder="Work email" class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
              <input v-model="form.organization" required placeholder="Organization" class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
              <input v-model="form.country" required placeholder="Country" class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
              <textarea v-model="form.message" rows="5" placeholder="Additional requirements, quantities, deadlines…" class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
              <button type="submit" class="btn-primary w-full justify-center" :disabled="submit.isPending.value">
                {{ submit.isPending.value ? 'Submitting…' : 'Submit Inquiry' }}
              </button>
              <p v-if="submit.isError.value" class="text-sm text-red-600">Submission failed. Please try again.</p>
            </form>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
