<script setup lang="ts">
interface Props {
  reference: string;
}
const props = defineProps<Props>();
</script>

<template>
  <div class="min-h-screen bg-arcane-bg text-arcane-text flex flex-col">
    <header class="border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur">
      <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="/" class="font-display text-xl tracking-[0.3em] text-arcane-accent">
          ARCANE
        </a>
      </div>
    </header>

    <main class="flex-1">
      <div class="max-w-4xl mx-auto px-6 py-8">
        <div class="card-panel p-6 text-center space-y-3">
          <h1 class="font-display text-2xl">
            Thanks – we’ve got your submission
          </h1>
          <p class="text-arcane-muted text-sm">
            Your reference is <strong>{{ reference }}</strong>.
          </p>
          <p class="text-arcane-muted text-xs max-w-md mx-auto">
            We’ll review your photos and get back to you by email with an offer.
            You don’t need to send anything yet – we’ll confirm the details first.
          </p>
        </div>
      </div>
    </main>
  </div>
</template>