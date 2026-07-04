<script setup lang="ts">
import { ref } from 'vue'
import { useMutation } from '@tanstack/vue-query'
import { register } from '@/api/wp'
import { ApiError } from '@/api/client'

const form = ref({ name: '', email: '', password: '', organization: '', country: '' })

const submit = useMutation({ mutationFn: () => register(form.value) })
</script>

<template>
  <div class="flex min-h-[70vh] items-center justify-center py-16">
    <div class="w-full max-w-lg rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
      <h1 class="text-2xl font-bold text-primary">Request Portal Access</h1>
      <p class="mt-1 text-sm text-slate-500">
        Create an account to access downloads, support tickets and repair tracking.
        Accounts are reviewed and approved by our team before activation.
      </p>

      <div v-if="submit.isSuccess.value" class="mt-6 rounded-md bg-green-50 p-6 text-center">
        <p class="font-semibold text-green-700">Registration received</p>
        <p class="mt-1 text-sm text-green-600">
          Your account is pending approval. We will notify you by email once access is granted.
        </p>
        <RouterLink to="/" class="btn-primary mt-5">Back to Home</RouterLink>
      </div>

      <form v-else class="mt-6 space-y-4" @submit.prevent="submit.mutate()">
        <div class="grid gap-4 sm:grid-cols-2">
          <input v-model="form.name" required placeholder="Full name" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
          <input v-model="form.email" required type="email" placeholder="Work email" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <input v-model="form.organization" required placeholder="Organization" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
          <input v-model="form.country" required placeholder="Country" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm" />
        </div>
        <input
          v-model="form.password"
          required
          type="password"
          minlength="8"
          placeholder="Password (min. 8 characters)"
          autocomplete="new-password"
          class="w-full rounded-md border border-slate-300 px-4 py-2.5 text-sm"
        />
        <button type="submit" class="btn-primary w-full justify-center" :disabled="submit.isPending.value">
          {{ submit.isPending.value ? 'Submitting…' : 'Create Account' }}
        </button>
        <p v-if="submit.isError.value" class="text-sm text-red-600">
          {{ submit.error.value instanceof ApiError ? submit.error.value.message : 'Registration failed. Please try again.' }}
        </p>
      </form>

      <p class="mt-6 text-center text-xs text-slate-400">
        Already have an account? <RouterLink to="/login" class="text-accent hover:underline">Sign in</RouterLink>
      </p>
    </div>
  </div>
</template>
