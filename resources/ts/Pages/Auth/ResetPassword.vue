<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

interface Props {
  token: string;
  email: string | null;
}

const props = defineProps<Props>();

const form = useForm( {
  token: props.token,
  email: props.email ?? '',
  password: '',
  password_confirmation: '',
} );

const submit = () => {
  form.post( '/reset-password' );
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-arcane-bg text-arcane-text">
    <div class="card-panel w-full max-w-md p-6">
      <div class="mb-6 text-center">
        <div class="font-display text-2xl tracking-[0.3em] text-arcane-accent mb-2">
          <img src="/images/logo.png" alt="Arcane" class="h-20 mx-auto" />
        </div>
        <p class="text-arcane-muted text-sm">
          Set your new password.
        </p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1" for="email">Email</label>
          <input id="email" v-model="form.email" type="email" required autocomplete="email"
            class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arcane-accent" />
          <div v-if=" form.errors.email " class="text-xs text-red-400 mt-1">
            {{ form.errors.email }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1" for="password">New password</label>
          <input id="password" v-model="form.password" type="password" required autocomplete="new-password"
            class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arcane-accent" />
          <div v-if=" form.errors.password " class="text-xs text-red-400 mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1" for="password_confirmation">Confirm password</label>
          <input id="password_confirmation" v-model="form.password_confirmation" type="password" required
            autocomplete="new-password"
            class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arcane-accent" />
        </div>

        <button type="submit" class="btn-primary w-full justify-center" :disabled="form.processing">
          <span v-if=" form.processing ">Resetting…</span>
          <span v-else>Reset password</span>
        </button>

        <div class="text-center text-xs text-arcane-muted">
          <Link href="/login" class="hover:text-arcane-accent">
          Back to sign in
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>