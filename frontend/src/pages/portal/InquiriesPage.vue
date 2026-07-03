<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query'
import { getMyInquiries } from '@/api/wp'

const { data: inquiries, isLoading } = useQuery({ queryKey: ['my-inquiries'], queryFn: getMyInquiries })
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-primary">Inquiry History</h1>
    <p class="mt-1 text-sm text-slate-500">Quotation requests you have submitted.</p>

    <div v-if="isLoading" class="py-16 text-center text-slate-400">Loading…</div>
    <div v-else class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs text-slate-500 uppercase">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3">Products</th>
            <th class="px-4 py-3">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="q in inquiries" :key="q.id">
            <td class="px-4 py-3 font-medium">{{ q.id }}</td>
            <td class="px-4 py-3 text-slate-500">{{ new Date(q.date).toLocaleDateString() }}</td>
            <td class="px-4 py-3">{{ q.products.join(', ') }}</td>
            <td class="px-4 py-3 capitalize">{{ q.status }}</td>
          </tr>
          <tr v-if="!inquiries?.length">
            <td colspan="4" class="px-4 py-8 text-center text-slate-400">No inquiries submitted yet.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
