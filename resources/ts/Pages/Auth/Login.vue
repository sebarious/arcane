<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm( {
  email: '',
  password: '',
  remember: false,
} );

const submit = () => {
  form.post( '/login' );
};
</script>

<template>
  <div class="futuristic-grid">
    <div class="min-h-screen flex items-center justify-center bg-arcane-bg/80 text-arcane-text">
      <div class="card-panel w-full max-w-md p-6">
        <div class="mb-6 text-center">
          <div class="font-display text-2xl tracking-[0.3em] text-arcane-accent mb-2">
            <img src="/images/logo.png" alt="Arcane" class="h-20 mx-auto" />
          </div>
          <p class="text-arcane-muted text-sm">
            Sign in to access your dashboard.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1" for="email">Email</label>
            <input id="email" v-model="form.email" type="email" autocomplete="email" required
              class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white" />
            <div v-if=" form.errors.email " class="text-xs text-red-400 mt-1">
              {{ form.errors.email }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" for="password">Password</label>
            <input id="password" v-model="form.password" type="password" autocomplete="current-password" required
              class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white" />
            <div v-if=" form.errors.password " class="text-xs text-red-400 mt-1">
              {{ form.errors.password }}
            </div>
          </div>

          <div class="flex items-center justify-between text-xs text-arcane-muted">
            <label class="inline-flex items-center gap-2">
              <input v-model="form.remember" type="checkbox" class="rounded border-arcane-border bg-arcane-surface" />
              <span>Remember me</span>
            </label>
          </div>

          <div class="outline-root w-full">
            <button type="submit" class="btn-primary w-full justify-center outline-inner" :disabled="form.processing">
              <span v-if=" form.processing ">Signing in…</span>
              <span v-else>Sign in</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>