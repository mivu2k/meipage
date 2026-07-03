<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-primary">Profile</h1>
    <div class="mt-6 max-w-lg rounded-lg border border-slate-200 bg-white p-6">
      <dl class="space-y-4 text-sm">
        <div>
          <dt class="font-medium text-slate-500">Name</dt>
          <dd class="mt-0.5 text-slate-800">{{ auth.user?.name }}</dd>
        </div>
        <div>
          <dt class="font-medium text-slate-500">Email</dt>
          <dd class="mt-0.5 text-slate-800">{{ auth.user?.email }}</dd>
        </div>
        <div>
          <dt class="font-medium text-slate-500">Account Type</dt>
          <dd class="mt-0.5 text-slate-800 capitalize">{{ auth.user?.roles.join(', ') }}</dd>
        </div>
      </dl>
      <p class="mt-6 text-xs text-slate-400">
        To update your details or change your password, please contact support.
      </p>
    </div>
  </div>
</template>
