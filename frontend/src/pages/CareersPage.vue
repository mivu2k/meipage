<script setup lang="ts">
import { ref } from 'vue'
import { useQuery, useMutation } from '@tanstack/vue-query'
import { getCareers, submitApplication } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'

const { data: positions, isLoading } = useQuery({ queryKey: ['careers'], queryFn: getCareers })

const applyingTo = ref<number | null>(null)
const form = ref({ name: '', email: '', phone: '', cover_letter: '' })

const apply = useMutation({
  mutationFn: () => submitApplication({ ...form.value, position: applyingTo.value! }),
  onSuccess: () => {
    applyingTo.value = null
    form.value = { name: '', email: '', phone: '', cover_letter: '' }
  },
})
</script>

<template>
  <div>
    <PageHero title="Careers" subtitle="Join our team of engineers and specialists building mission-critical technology." />
    <div class="container-page max-w-4xl py-12">
      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading…</div>
      <p v-else-if="!positions?.length" class="py-20 text-center text-slate-400">No open positions at the moment. Check back soon.</p>

      <div v-else class="space-y-4">
        <div v-if="apply.isSuccess.value" class="rounded-md bg-green-50 p-4 text-sm text-green-700">
          Application submitted — thank you! Our HR team will be in touch.
        </div>
        <div v-for="job in positions" :key="job.id" class="rounded-lg border border-slate-200 p-6">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-primary">{{ job.title }}</h2>
              <p class="mt-1 text-sm text-slate-500">
                {{ job.department }} · {{ job.location }} · <span class="capitalize">{{ job.type }}</span>
              </p>
            </div>
            <button class="btn-primary" @click="applyingTo = applyingTo === job.id ? null : job.id">
              {{ applyingTo === job.id ? 'Close' : 'Apply' }}
            </button>
          </div>
          <div class="prose-wp mt-3 text-sm text-slate-600" v-html="job.content" />

          <form v-if="applyingTo === job.id" class="mt-6 space-y-3 border-t border-slate-100 pt-6" @submit.prevent="apply.mutate()">
            <div class="grid gap-3 sm:grid-cols-3">
              <input v-model="form.name" required placeholder="Full name" class="input" />
              <input v-model="form.email" required type="email" placeholder="Email" class="input" />
              <input v-model="form.phone" placeholder="Phone" class="input" />
            </div>
            <textarea v-model="form.cover_letter" rows="4" placeholder="Cover letter / link to your resume…" class="input" />
            <button type="submit" class="btn-primary" :disabled="apply.isPending.value">
              {{ apply.isPending.value ? 'Submitting…' : 'Submit Application' }}
            </button>
            <p v-if="apply.isError.value" class="text-xs text-red-600">Submission failed. Please try again.</p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
