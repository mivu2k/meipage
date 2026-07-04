<script setup lang="ts">
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { getPosts } from '@/api/wp'
import PageHero from '@/components/common/PageHero.vue'
import AppPagination from '@/components/common/AppPagination.vue'

const page = ref(1)

function setPage(n: number) {
  page.value = n
  window.scrollTo({ top: 0 })
}
const params = computed(() => ({ page: page.value, per_page: 9 }))
const { data, isLoading } = useQuery({
  queryKey: ['posts', params],
  queryFn: () => getPosts(params.value),
})
</script>

<template>
  <div>
    <PageHero title="News & Insights" subtitle="Company announcements, industry news and technical articles." />
    <div class="container-page py-10">
      <div v-if="isLoading" class="py-20 text-center text-slate-400">Loading…</div>
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <RouterLink
          v-for="post in data?.items"
          :key="post.id"
          :to="`/news/${post.slug}`"
          class="group overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:shadow-lg"
        >
          <img v-if="post.image" :src="post.image.url" :alt="post.image.alt" class="aspect-video w-full object-cover" loading="lazy" />
          <div class="p-5">
            <div class="flex items-center gap-2 text-xs text-slate-400">
              <time>{{ new Date(post.date).toLocaleDateString() }}</time>
              <span v-if="post.categories.length">· {{ post.categories[0].name }}</span>
            </div>
            <h2 class="mt-1 font-semibold text-primary group-hover:text-accent">{{ post.title }}</h2>
            <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ post.excerpt }}</p>
          </div>
        </RouterLink>
      </div>
      <AppPagination v-if="data" :page="page" :pages="data.pages" @change="setPage" />
    </div>
  </div>
</template>
