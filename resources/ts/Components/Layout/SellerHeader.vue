<template>
  <!-- Top nav -->
  <header class="sticky top-0 border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur z-10">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
      <Link href="/" class="flex items-center gap-2">
      <img src="/images/logo.png" alt="Arcane" class="h-10" />
      </Link>
      <nav class="flex items-center gap-4 text-sm text-white/70">
        <Link href="/seller" class="hover:text-white">Dashboard</Link>
        <Link href="/seller/batches" class="hover:text-white">Batches</Link>
        <div class="outline-root">
          <Link href="/seller/request-batch" class="btn-primary text-xs outline-inner">
          <span>Buy more</span>
          </Link>
        </div>
      </nav>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>