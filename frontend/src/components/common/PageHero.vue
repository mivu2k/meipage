<script setup lang="ts">
defineProps<{ title: string; subtitle?: string; kicker?: string }>()
</script>

<template>
  <section class="section-dark py-20">
    <div class="tech-grid" aria-hidden="true" />
    <div class="glow -top-24 -right-24 h-80 w-80" aria-hidden="true" />
    <div class="container-page relative z-10">
      <p v-if="kicker" class="section-kicker">{{ kicker }}</p>
      <h1 v-reveal class="text-4xl font-bold sm:text-5xl">{{ title }}</h1>
      <p v-if="subtitle" v-reveal="100" class="mt-4 max-w-2xl text-lg text-slate-300">{{ subtitle }}</p>
    </div>
  </section>
</template>
