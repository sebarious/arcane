<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Header from '@/Components/Layout/Header.vue';

</script>

<template>
  <div class="min-h-screen bg-arcane-bg text-arcane-text flex flex-col">
    <Header />

    <main class="flex-1">
      <div class="max-w-3xl mx-auto px-6 py-8">
        <div class="card-panel p-6 text-center space-y-3">
          <h1 class="font-display text-2xl">
            Application received
          </h1>
          <p class="text-arcane-muted text-sm">
            Thanks for applying to become an Arcane seller. We’ll review your details and get back to you.
          </p>
          <Link href="/" class="btn-primary inline-flex justify-center">
          Return home
          </Link>
        </div>
      </div>
    </main>
  </div>
</template>