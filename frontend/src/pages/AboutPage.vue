<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query'
import { getPage } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'

// The About page body (profile, history, vision, mission, values, leadership,
// certifications, facilities, timeline) is fully authored in WordPress.
const { data: page, isLoading } = useQuery({
  queryKey: ['page', 'about'],
  queryFn: () => getPage('about'),
})
</script>

<template>
  <div>
    <PageHero title="About Us" :subtitle="page?.excerpt" />
    <div class="container-page max-w-4xl py-12">
      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading…</div>
      <div v-else-if="page" class="prose-wp text-slate-700" v-html="page.content" />
      <p v-else class="py-20 text-center text-slate-400">
        Create a page with slug <code>about</code> in WordPress to populate this section.
      </p>
    </div>
  </div>
</template>
