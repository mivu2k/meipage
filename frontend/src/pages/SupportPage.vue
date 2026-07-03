<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import PageHero from '@/components/common/PageHero.vue'

const auth = useAuthStore()

const services = [
  { title: 'Technical Support', desc: 'Get help from our engineers on configuration, integration and troubleshooting.', type: 'technical' },
  { title: 'Warranty Request', desc: 'Submit a warranty claim for a registered product.', type: 'warranty' },
  { title: 'Repair Request', desc: 'Open an RMA and track your repair through every stage.', type: 'repair' },
  { title: 'Documentation Request', desc: 'Request manuals, datasheets or certificates not publicly available.', type: 'documentation' },
  { title: 'Training Request', desc: 'Arrange product or solution training for your team.', type: 'training' },
]
</script>

<template>
  <div>
    <PageHero title="Support Center" subtitle="Open a ticket and our support team will assist you. Repairs are tracked from reception to shipment." />
    <div class="container-page py-12">
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="svc in services" :key="svc.type" class="flex flex-col rounded-lg border border-slate-200 p-6">
          <h2 class="font-semibold text-primary">{{ svc.title }}</h2>
          <p class="mt-2 flex-1 text-sm text-slate-600">{{ svc.desc }}</p>
          <RouterLink
            :to="auth.isAuthenticated ? { path: '/portal/tickets', query: { new: svc.type } } : { path: '/login', query: { redirect: `/portal/tickets?new=${svc.type}` } }"
            class="btn-outline mt-4 justify-center text-sm"
          >
            Create Ticket
          </RouterLink>
        </div>
      </div>

      <div class="mt-14 rounded-lg bg-slate-50 p-8">
        <h2 class="section-title mb-6">Repair Tracking Stages</h2>
        <ol class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-600">
          <template v-for="(stage, i) in ['Received', 'Inspection', 'Repair', 'Testing', 'Quality Check', 'Ready', 'Shipped']" :key="stage">
            <li class="rounded-full border border-slate-300 bg-white px-4 py-1.5">{{ stage }}</li>
            <span v-if="i < 6" class="text-slate-300" aria-hidden="true">→</span>
          </template>
        </ol>
        <p class="mt-4 text-sm text-slate-500">
          Track your repairs in real time from the <RouterLink to="/portal/repairs" class="text-accent hover:underline">customer portal</RouterLink>.
        </p>
      </div>
    </div>
  </div>
</template>
