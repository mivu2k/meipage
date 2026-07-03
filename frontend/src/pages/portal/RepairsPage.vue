<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query'
import { getMyRepairs } from '@/api/wp'
import type { RepairStage } from '@/api/types'

const { data: repairs, isLoading } = useQuery({ queryKey: ['my-repairs'], queryFn: getMyRepairs })

const stages: { key: RepairStage; label: string }[] = [
  { key: 'received', label: 'Received' },
  { key: 'inspection', label: 'Inspection' },
  { key: 'repair', label: 'Repair' },
  { key: 'testing', label: 'Testing' },
  { key: 'quality_check', label: 'Quality Check' },
  { key: 'ready', label: 'Ready' },
  { key: 'shipped', label: 'Shipped' },
]
const stageIndex = (s: RepairStage) => stages.findIndex((x) => x.key === s)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-primary">Repair Tracking</h1>
    <p class="mt-1 text-sm text-slate-500">Follow your RMA cases through each stage of the repair process.</p>

    <div v-if="isLoading" class="py-16 text-center text-slate-400">Loading…</div>
    <div v-else class="mt-6 space-y-6">
      <div v-for="r in repairs" :key="r.id" class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div>
            <div class="font-semibold text-slate-800">RMA {{ r.rma }} — {{ r.product }}</div>
            <div class="text-xs text-slate-400">Serial: {{ r.serial }}</div>
          </div>
          <span class="rounded-full bg-accent/15 px-3 py-1 text-xs font-semibold text-primary capitalize">
            {{ r.stage.replace('_', ' ') }}
          </span>
        </div>

        <!-- Stage progress -->
        <ol class="mt-6 flex items-center">
          <li v-for="(s, i) in stages" :key="s.key" class="flex flex-1 items-center last:flex-none">
            <div class="flex flex-col items-center">
              <div
                class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
                :class="i <= stageIndex(r.stage) ? 'bg-primary text-white' : 'bg-slate-200 text-slate-400'"
              >
                {{ i + 1 }}
              </div>
              <span class="mt-1 hidden text-[10px] text-slate-500 sm:block">{{ s.label }}</span>
            </div>
            <div v-if="i < stages.length - 1" class="mx-1 h-0.5 flex-1" :class="i < stageIndex(r.stage) ? 'bg-primary' : 'bg-slate-200'" />
          </li>
        </ol>

        <details v-if="r.history?.length" class="mt-4">
          <summary class="cursor-pointer text-xs font-medium text-slate-500">History</summary>
          <ul class="mt-2 space-y-1 text-xs text-slate-600">
            <li v-for="(h, i) in r.history" :key="i">
              <span class="font-medium capitalize">{{ h.stage.replace('_', ' ') }}</span> — {{ new Date(h.date).toLocaleString() }}
              <span v-if="h.note">· {{ h.note }}</span>
            </li>
          </ul>
        </details>
      </div>
      <p v-if="!repairs?.length" class="py-12 text-center text-slate-400">No repair cases.</p>
    </div>
  </div>
</template>
