<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { getPost } from '@/api/wp'

const route = useRoute()
const slug = computed(() => route.params.slug as string)
const { data: post, isLoading } = useQuery({
  queryKey: ['post', slug],
  queryFn: () => getPost(slug.value),
})
</script>

<template>
  <article class="container-page max-w-3xl py-12">
    <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading…</div>
    <template v-else-if="post">
      <nav class="mb-6 text-sm text-slate-500">
        <RouterLink to="/news" class="hover:text-primary">← Back to News</RouterLink>
      </nav>
      <h1 class="text-3xl font-bold text-primary sm:text-4xl">{{ post.title }}</h1>
      <div class="mt-3 flex items-center gap-3 text-sm text-slate-400">
        <time>{{ new Date(post.date).toLocaleDateString() }}</time>
        <span v-if="post.author">· {{ post.author }}</span>
      </div>
      <img v-if="post.image" :src="post.image.url" :alt="post.image.alt" class="mt-8 w-full rounded-lg" />
      <div class="prose-wp mt-8 text-slate-700" v-html="post.content" />
    </template>
  </article>
</template>
