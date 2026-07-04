<script setup lang="ts">
/**
 * Smart pagination: prev/next arrows and a condensed page window with
 * ellipsis, so 100 pages render as « 1 … 5 6 [7] 8 9 … 100 ».
 */
import { computed } from 'vue'

const props = defineProps<{ page: number; pages: number }>()
const emit = defineEmits<{ (e: 'change', page: number): void }>()

const items = computed<(number | '…')[]>(() => {
  const { page, pages } = props
  if (pages <= 7) return Array.from({ length: pages }, (_, i) => i + 1)
  const window = [page - 1, page, page + 1].filter((n) => n > 1 && n < pages)
  const out: (number | '…')[] = [1]
  if (window[0] && window[0] > 2) out.push('…')
  out.push(...window)
  if (window.length && (window.at(-1) as number) < pages - 1) out.push('…')
  out.push(pages)
  return out
})

function go(n: number) {
  if (n >= 1 && n <= props.pages && n !== props.page) emit('change', n)
}
</script>

<template>
  <nav v-if="pages > 1" class="mt-12 flex items-center justify-center gap-1.5" aria-label="Pagination">
    <button
      class="h-10 w-10 rounded-lg border border-slate-200 text-slate-500 transition hover:border-accent hover:text-primary disabled:opacity-30 disabled:hover:border-slate-200"
      :disabled="page === 1"
      aria-label="Previous page"
      @click="go(page - 1)"
    >
      ←
    </button>
    <template v-for="(item, i) in items" :key="i">
      <span v-if="item === '…'" class="px-1.5 text-slate-400">…</span>
      <button
        v-else
        class="h-10 min-w-10 rounded-lg px-2 text-sm font-semibold transition"
        :class="item === page ? 'bg-primary text-white shadow' : 'border border-slate-200 text-slate-600 hover:border-accent hover:text-primary'"
        :aria-current="item === page ? 'page' : undefined"
        @click="go(item)"
      >
        {{ item }}
      </button>
    </template>
    <button
      class="h-10 w-10 rounded-lg border border-slate-200 text-slate-500 transition hover:border-accent hover:text-primary disabled:opacity-30 disabled:hover:border-slate-200"
      :disabled="page === pages"
      aria-label="Next page"
      @click="go(page + 1)"
    >
      →
    </button>
  </nav>
</template>
