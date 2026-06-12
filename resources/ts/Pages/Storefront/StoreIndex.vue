<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Store {
  id: number;
  slug: string;
  name: string;
  city: string;
  postcode: string;
}

interface Props {
  stores: Store[];
}

const props = defineProps<Props>();
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <header class="border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur">
      <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <Link href="/" class="font-display text-xl tracking-[0.3em] text-arcane-accent">
        ARCANE
        </Link>
        <nav class="flex items-center gap-4 text-sm text-arcane-muted">
          <span class="text-arcane-muted">Stores</span>
          <Link href="/login" class="hover:text-arcane-accent text-xs">Store login</Link>
        </nav>
      </div>
    </header>

    <main class="flex-1">
      <div class="max-w-6xl mx-auto px-6 py-10 space-y-6">
        <section class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
          <div>
            <h1 class="font-display text-3xl mb-2">
              Arcane partner stores
            </h1>
            <p class="text-arcane-muted text-sm max-w-xl">
              These shops stock Arcane single-card mystery packs. Tap a store
              to view the live card list of hits still in the pool.
            </p>
          </div>
        </section>

        <section>
          <div v-if=" stores.length === 0 " class="text-arcane-muted text-sm">
            No stores are live yet.
          </div>
          <div v-else class="grid gap-4 md:grid-cols-2">
            <Link v-for=" store in stores " :key="store.id" :href="route( 'stores.show', { store: store.slug } )"
              class="card-panel p-4 hover:border-arcane-accent/60 transition flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between gap-3 mb-2">
                <h2 class="font-semibold">
                  {{ store.name }}
                </h2>
                <span class="text-[11px] text-arcane-muted">
                  Live card list →
                </span>
              </div>
            </div>
            <div class="mt-3 text-[11px] text-arcane-muted">
              View remaining mythic, legendary, super, rare, and common hits at this location.
            </div>
            </Link>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>