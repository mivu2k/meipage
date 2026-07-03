<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { getMyTickets, createTicket, replyTicket } from '@/api/wp'
import type { Ticket } from '@/api/types'

const route = useRoute()
const qc = useQueryClient()
const { data: tickets, isLoading } = useQuery({ queryKey: ['my-tickets'], queryFn: getMyTickets })

const showNew = ref(false)
const form = ref<{ subject: string; type: Ticket['type']; message: string }>({
  subject: '',
  type: 'technical',
  message: '',
})
const expanded = ref<number | null>(null)
const reply = ref('')

onMounted(() => {
  const t = route.query.new as string | undefined
  if (t) {
    showNew.value = true
    form.value.type = (t as Ticket['type']) || 'technical'
  }
})

const create = useMutation({
  mutationFn: () => createTicket(form.value),
  onSuccess: () => {
    showNew.value = false
    form.value = { subject: '', type: 'technical', message: '' }
    qc.invalidateQueries({ queryKey: ['my-tickets'] })
  },
})

const sendReply = useMutation({
  mutationFn: (id: number) => replyTicket(id, reply.value),
  onSuccess: () => {
    reply.value = ''
    qc.invalidateQueries({ queryKey: ['my-tickets'] })
  },
})

const statusColor: Record<string, string> = {
  open: 'bg-blue-100 text-blue-700',
  in_progress: 'bg-amber-100 text-amber-700',
  waiting: 'bg-purple-100 text-purple-700',
  resolved: 'bg-green-100 text-green-700',
  closed: 'bg-slate-100 text-slate-500',
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-primary">Support Tickets</h1>
      <button class="btn-primary" @click="showNew = !showNew">{{ showNew ? 'Cancel' : 'New Ticket' }}</button>
    </div>

    <form v-if="showNew" class="mt-6 space-y-3 rounded-lg border border-slate-200 bg-white p-6" @submit.prevent="create.mutate()">
      <div class="grid gap-3 sm:grid-cols-2">
        <input v-model="form.subject" required placeholder="Subject" class="rounded-md border border-slate-300 px-3 py-2 text-sm" />
        <select v-model="form.type" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
          <option value="technical">Technical Support</option>
          <option value="warranty">Warranty Request</option>
          <option value="repair">Repair Request</option>
          <option value="documentation">Documentation Request</option>
          <option value="training">Training Request</option>
        </select>
      </div>
      <textarea v-model="form.message" required rows="4" placeholder="Describe your issue…" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
      <button type="submit" class="btn-primary" :disabled="create.isPending.value">
        {{ create.isPending.value ? 'Creating…' : 'Create Ticket' }}
      </button>
    </form>

    <div v-if="isLoading" class="py-16 text-center text-slate-400">Loading…</div>
    <div v-else class="mt-6 space-y-3">
      <div v-for="t in tickets" :key="t.id" class="rounded-lg border border-slate-200 bg-white">
        <button class="flex w-full items-center justify-between gap-4 p-4 text-left" @click="expanded = expanded === t.id ? null : t.id">
          <div>
            <div class="text-sm font-semibold text-slate-800">#{{ t.id }} — {{ t.subject }}</div>
            <div class="text-xs text-slate-400 capitalize">{{ t.type }} · updated {{ new Date(t.updated).toLocaleDateString() }}</div>
          </div>
          <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" :class="statusColor[t.status]">
            {{ t.status.replace('_', ' ') }}
          </span>
        </button>
        <div v-if="expanded === t.id" class="border-t border-slate-100 p-4">
          <div v-for="(m, i) in t.messages" :key="i" class="mb-3 rounded-md bg-slate-50 p-3 text-sm">
            <div class="text-xs font-semibold text-slate-500">{{ m.author }} · {{ new Date(m.date).toLocaleString() }}</div>
            <p class="mt-1 text-slate-700">{{ m.body }}</p>
          </div>
          <form v-if="t.status !== 'closed'" class="mt-3 flex gap-2" @submit.prevent="sendReply.mutate(t.id)">
            <input v-model="reply" required placeholder="Write a reply…" class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <button type="submit" class="btn-primary text-sm" :disabled="sendReply.isPending.value">Send</button>
          </form>
        </div>
      </div>
      <p v-if="!tickets?.length" class="py-12 text-center text-slate-400">No tickets yet.</p>
    </div>
  </div>
</template>
