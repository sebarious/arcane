<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';

const form = useForm( {
  email: '',
} );

const page = usePage<{ props: { flash?: { status?: string; }; }; }>();

const submit = () => {
  form.post( '/forgot-password' );
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
          Enter your email and we’ll send you a password reset link.
        </p>
      </div>

      <div v-if=" page.props.flash?.status "
        class="mb-4 rounded border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-300">
        {{ page.props.flash.status }}
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

        <button type="submit" class="btn-primary w-full justify-center" :disabled="form.processing">
          <span v-if=" form.processing ">Sending…</span>
          <span v-else>Send reset link</span>
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