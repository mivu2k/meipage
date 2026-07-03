<script setup lang="ts">
defineProps<{ title: string; subtitle?: string }>()
</script>

<template>
  <section class="bg-primary py-14 text-white">
    <div class="container-page">
      <h1 class="text-3xl font-bold sm:text-4xl">{{ title }}</h1>
      <p v-if="subtitle" class="mt-3 max-w-2xl text-slate-300">{{ subtitle }}</p>
    </div>
  </section>
</template>
