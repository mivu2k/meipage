<script setup lang="ts">
import { ref } from 'vue'
import { useQuery, useMutation } from '@tanstack/vue-query'
import { getBranches, submitContact } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'

const { data: branches, isLoading } = useQuery({ queryKey: ['branches'], queryFn: getBranches })

const form = ref({ name: '', email: '', subject: '', message: '', department: '' })
const contact = useMutation({ mutationFn: () => submitContact(form.value) })
</script>

<template>
  <div>
    <PageHero title="Contact Us" subtitle="Reach any of our offices, or send us a message directly." />

    <div class="container-page grid gap-12 py-12 lg:grid-cols-2">
      <!-- Branches -->
      <div>
        <h2 class="section-title mb-6">Our Offices</h2>
        <div v-if="isLoading" class="text-slate-400">Loading…</div>
        <div v-else class="space-y-6">
          <div v-for="b in branches" :key="b.id" class="rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-primary">{{ b.title }}</h3>
            <p class="mt-2 text-sm text-slate-600">{{ b.address }}</p>
            <div class="mt-3 space-y-1 text-sm">
              <p v-if="b.phone"><span class="font-medium text-slate-500">Phone:</span> {{ b.phone }}</p>
              <p v-if="b.email">
                <span class="font-medium text-slate-500">Email:</span>
                <a :href="`mailto:${b.email}`" class="text-accent hover:underline"> {{ b.email }}</a>
              </p>
              <p v-if="b.working_hours"><span class="font-medium text-slate-500">Hours:</span> {{ b.working_hours }}</p>
            </div>
            <div v-if="b.departments?.length" class="mt-4 grid gap-2 sm:grid-cols-2">
              <div v-for="d in b.departments" :key="d.name" class="rounded-md bg-slate-50 p-3 text-xs">
                <div class="font-semibold text-slate-700">{{ d.name }}</div>
                <div class="text-slate-500">{{ d.phone }}</div>
                <div class="text-slate-500">{{ d.email }}</div>
              </div>
            </div>
            <a
              v-if="b.lat && b.lng"
              :href="`https://www.google.com/maps?q=${b.lat},${b.lng}`"
              target="_blank"
              rel="noopener"
              class="mt-4 inline-block text-sm font-medium text-accent hover:underline"
            >
              View on Google Maps ↗
            </a>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div>
        <h2 class="section-title mb-6">Send a Message</h2>
        <div v-if="contact.isSuccess.value" class="rounded-md bg-green-50 p-6 text-green-700">
          Your message has been sent. We will respond as soon as possible.
        </div>
        <form v-else class="space-y-4" @submit.prevent="contact.mutate()">
          <div class="grid gap-4 sm:grid-cols-2">
            <input v-model="form.name" required placeholder="Full name" class="input" />
            <input v-model="form.email" required type="email" placeholder="Email" class="input" />
          </div>
          <select v-model="form.department" class="input">
            <option value="">Select department (optional)</option>
            <option>Sales</option>
            <option>Projects</option>
            <option>Support</option>
            <option>HR</option>
            <option>Accounts</option>
          </select>
          <input v-model="form.subject" required placeholder="Subject" class="input" />
          <textarea v-model="form.message" required rows="6" placeholder="Your message…" class="input" />
          <button type="submit" class="btn-primary" :disabled="contact.isPending.value">
            {{ contact.isPending.value ? 'Sending…' : 'Send Message' }}
          </button>
          <p v-if="contact.isError.value" class="text-sm text-red-600">Failed to send. Please try again.</p>
        </form>
      </div>
    </div>
  </div>
</template>
