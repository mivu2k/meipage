<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { getMyDownloads } from '@/api/wp'

const { data: downloads, isLoading } = useQuery({ queryKey: ['my-downloads'], queryFn: getMyDownloads })

const filter = ref('')
const types = ['driver', 'firmware', 'manual', 'software', 'brochure', 'config', 'bulletin', 'training', 'datasheet']
const filtered = computed(() =>
  (downloads.value ?? []).filter((d) => !filter.value || d.type === filter.value),
)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-primary">Downloads</h1>
    <p class="mt-1 text-sm text-slate-500">Files assigned to your account: drivers, firmware, manuals, software and more.</p>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        class="rounded-full px-4 py-1.5 text-xs font-semibold capitalize"
        :class="!filter ? 'bg-primary text-white' : 'border border-slate-300 text-slate-600'"
        @click="filter = ''"
      >
        All
      </button>
      <button
        v-for="t in types"
        :key="t"
        class="rounded-full px-4 py-1.5 text-xs font-semibold capitalize"
        :class="filter === t ? 'bg-primary text-white' : 'border border-slate-300 text-slate-600'"
        @click="filter = t"
      >
        {{ t }}
      </button>
    </div>

    <div v-if="isLoading" class="py-16 text-center text-slate-400">Loading…</div>
    <ul v-else class="mt-6 divide-y divide-slate-100 rounded-lg border border-slate-200 bg-white">
      <li v-for="d in filtered" :key="d.id" class="flex items-center justify-between gap-4 p-4">
        <div>
          <div class="text-sm font-medium text-slate-800">{{ d.title }}</div>
          <div class="text-xs text-slate-400 capitalize">
            {{ d.type }} <span v-if="d.version">· v{{ d.version }}</span> <span v-if="d.size">· {{ d.size }}</span>
          </div>
        </div>
        <a v-if="d.url" :href="d.url" class="btn-primary text-xs" download>Download</a>
      </li>
      <li v-if="!filtered.length" class="p-8 text-center text-slate-400">No downloads available.</li>
    </ul>
  </div>
</template>
