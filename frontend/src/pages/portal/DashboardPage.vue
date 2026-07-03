<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query'
import { useAuthStore } from '@/stores/auth'
import { getMyTickets, getMyRepairs, getMyDownloads } from '@/api/wp'

const auth = useAuthStore()
const { data: tickets } = useQuery({ queryKey: ['my-tickets'], queryFn: getMyTickets })
const { data: repairs } = useQuery({ queryKey: ['my-repairs'], queryFn: getMyRepairs })
const { data: downloads } = useQuery({ queryKey: ['my-downloads'], queryFn: getMyDownloads })
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-primary">Welcome, {{ auth.user?.name }}</h1>
    <p class="mt-1 text-sm text-slate-500">Your customer portal overview.</p>

    <div class="mt-8 grid gap-6 sm:grid-cols-3">
      <RouterLink to="/portal/tickets" class="rounded-lg border border-slate-200 bg-white p-6 hover:border-accent">
        <div class="text-3xl font-bold text-primary">{{ tickets?.filter((t) => t.status !== 'closed').length ?? '—' }}</div>
        <div class="mt-1 text-sm text-slate-500">Open Tickets</div>
      </RouterLink>
      <RouterLink to="/portal/repairs" class="rounded-lg border border-slate-200 bg-white p-6 hover:border-accent">
        <div class="text-3xl font-bold text-primary">{{ repairs?.filter((r) => r.stage !== 'shipped').length ?? '—' }}</div>
        <div class="mt-1 text-sm text-slate-500">Active Repairs</div>
      </RouterLink>
      <RouterLink to="/portal/downloads" class="rounded-lg border border-slate-200 bg-white p-6 hover:border-accent">
        <div class="text-3xl font-bold text-primary">{{ downloads?.length ?? '—' }}</div>
        <div class="mt-1 text-sm text-slate-500">Available Downloads</div>
      </RouterLink>
    </div>

    <h2 class="mt-10 mb-4 text-lg font-semibold text-primary">Recent Tickets</h2>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs text-slate-500 uppercase">
          <tr>
            <th class="px-4 py-3">Subject</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Updated</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="t in tickets?.slice(0, 5)" :key="t.id">
            <td class="px-4 py-3 font-medium text-slate-800">{{ t.subject }}</td>
            <td class="px-4 py-3 capitalize">{{ t.type }}</td>
            <td class="px-4 py-3 capitalize">{{ t.status.replace('_', ' ') }}</td>
            <td class="px-4 py-3 text-slate-500">{{ new Date(t.updated).toLocaleDateString() }}</td>
          </tr>
          <tr v-if="!tickets?.length">
            <td colspan="4" class="px-4 py-6 text-center text-slate-400">No tickets yet.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
