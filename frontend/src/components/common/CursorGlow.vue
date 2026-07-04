<script setup lang="ts">
/**
 * Custom cursor: a small accent dot with a trailing ring that grows over
 * interactive elements — the agency-site touch. Desktop pointers only;
 * disabled for touch devices and reduced-motion users.
 */
import { onMounted, onBeforeUnmount, ref } from 'vue'

const enabled = ref(false)
const dot = ref<HTMLDivElement | null>(null)
const ring = ref<HTMLDivElement | null>(null)

let raf = 0
const mouse = { x: -100, y: -100 }
const pos = { x: -100, y: -100 }
let hovering = false

const onMove = (e: PointerEvent) => {
  mouse.x = e.clientX
  mouse.y = e.clientY
  const target = e.target as HTMLElement
  hovering = !!target.closest('a, button, [role="tab"], input, select, textarea, label')
}

const tick = () => {
  raf = requestAnimationFrame(tick)
  pos.x += (mouse.x - pos.x) * 0.16
  pos.y += (mouse.y - pos.y) * 0.16
  if (dot.value) dot.value.style.transform = `translate(${mouse.x}px, ${mouse.y}px)`
  if (ring.value) {
    ring.value.style.transform = `translate(${pos.x}px, ${pos.y}px) scale(${hovering ? 2.2 : 1})`
    ring.value.style.opacity = hovering ? '0.9' : '0.5'
  }
}

onMounted(() => {
  const fine = window.matchMedia('(pointer: fine)').matches
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  if (!fine || reduced) return
  enabled.value = true
  window.addEventListener('pointermove', onMove, { passive: true })
  tick()
})

onBeforeUnmount(() => {
  cancelAnimationFrame(raf)
  window.removeEventListener('pointermove', onMove)
})
</script>

<template>
  <template v-if="enabled">
    <div ref="dot" class="cursor-dot" aria-hidden="true" />
    <div ref="ring" class="cursor-ring" aria-hidden="true" />
  </template>
</template>

<style scoped>
.cursor-dot,
.cursor-ring {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 9999;
  pointer-events: none;
  border-radius: 9999px;
}
.cursor-dot {
  width: 6px;
  height: 6px;
  margin: -3px 0 0 -3px;
  background: var(--dtc-accent, #2dd4bf);
}
.cursor-ring {
  width: 34px;
  height: 34px;
  margin: -17px 0 0 -17px;
  border: 1.5px solid var(--dtc-accent, #2dd4bf);
  opacity: 0.5;
  transition: opacity 0.25s ease;
  will-change: transform;
}
</style>
