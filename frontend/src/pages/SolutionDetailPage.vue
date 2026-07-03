<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useQuery, useMutation } from '@tanstack/vue-query'
import { getSolution, submitConsultation } from '@/api/wp'

const route = useRoute()
const slug = computed(() => route.params.slug as string)

const { data: solution, isLoading } = useQuery({
  queryKey: ['solution', slug],
  queryFn: () => getSolution(slug.value),
})

const form = ref({ name: '', email: '', organization: '', message: '' })
const consult = useMutation({
  mutationFn: () =>
    submitConsultation({ ...form.value, solution: solution.value?.title ?? slug.value }),
})
</script>

<template>
  <div>
    <div v-if="isLoading" class="container-page py-20 text-center text-slate-400">Loading…</div>
    <template v-else-if="solution">
      <section class="relative bg-primary py-16 text-white">
        <div class="container-page">
          <h1 class="max-w-3xl text-3xl font-bold sm:text-4xl">{{ solution.title }}</h1>
          <p class="mt-4 max-w-2xl text-slate-300">{{ solution.excerpt }}</p>
        </div>
      </section>

      <div class="container-page grid gap-12 py-12 lg:grid-cols-3">
        <div class="lg:col-span-2">
          <div class="prose-wp text-slate-700" v-html="solution.content" />

          <template v-if="solution.architecture">
            <h2 class="section-title mt-10 mb-4">Architecture</h2>
            <div class="prose-wp text-slate-700" v-html="solution.architecture" />
          </template>

          <template v-if="solution.components?.length">
            <h2 class="section-title mt-10 mb-4">Components</h2>
            <ul class="grid gap-3 sm:grid-cols-2">
              <li v-for="c in solution.components" :key="c" class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                {{ c }}
              </li>
            </ul>
          </template>

          <template v-if="solution.benefits?.length">
            <h2 class="section-title mt-10 mb-4">Benefits</h2>
            <ul class="space-y-2">
              <li v-for="b in solution.benefits" :key="b" class="flex items-start gap-2 text-sm text-slate-700">
                <span class="mt-0.5 text-accent">✓</span>{{ b }}
              </li>
            </ul>
          </template>

          <template v-if="solution.case_studies?.length">
            <h2 class="section-title mt-10 mb-4">Case Studies</h2>
            <div class="space-y-4">
              <div v-for="cs in solution.case_studies" :key="cs.title" class="rounded-lg border border-slate-200 p-5">
                <h3 class="font-semibold text-primary">{{ cs.title }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ cs.summary }}</p>
              </div>
            </div>
          </template>
        </div>

        <!-- Consultation sidebar -->
        <aside>
          <div class="sticky top-24 rounded-lg border border-slate-200 bg-slate-50 p-6">
            <h2 class="text-lg font-bold text-primary">Request Consultation</h2>
            <p class="mt-1 text-sm text-slate-500">Ask a technical question or discuss this solution with our engineers.</p>

            <div v-if="consult.isSuccess.value" class="mt-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
              Thank you — our team will contact you shortly.
            </div>
            <form v-else class="mt-4 space-y-3" @submit.prevent="consult.mutate()">
              <input v-model="form.name" required placeholder="Full name" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <input v-model="form.email" required type="email" placeholder="Work email" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <input v-model="form.organization" required placeholder="Organization" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <textarea v-model="form.message" required rows="4" placeholder="Your requirements…" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <button type="submit" class="btn-primary w-full justify-center" :disabled="consult.isPending.value">
                {{ consult.isPending.value ? 'Sending…' : 'Submit Request' }}
              </button>
              <p v-if="consult.isError.value" class="text-xs text-red-600">Submission failed. Please try again.</p>
            </form>
          </div>
        </aside>
      </div>
    </template>
  </div>
</template>
