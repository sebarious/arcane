<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Header from '@/Components/Layout/Header.vue';

interface StoreGame {
  value: string;
  label: string;
}

interface Store {
  id: number;
  slug: string;
  name: string;
  city: string;
  postcode: string;
  games: StoreGame[];
}

interface Props {
  stores: Store[];
}

const props = defineProps<Props>();
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Header />
    <main class="flex-1">
      <div class="max-w-6xl mx-auto px-6 py-10 space-y-6">
        <section class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
          <div>
            <h1 class="font-display text-3xl mb-2">
              Arcane partner stores
            </h1>
            <p class="text-arcane-muted text-sm max-w-xl">
              These shops stock Arcane single-card mystery packs. Tap a store
              to view the live card lists and recent pulls.
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
                  View store →
                </span>
              </div>
              <div v-if=" store.games.length " class="flex flex-wrap gap-1 mt-1">
                <span v-for=" game in store.games " :key="game.value"
                  class="px-2 py-[2px] rounded-full text-[10px] bg-arcane-elevated text-arcane-muted border border-arcane-border/70">
                  {{ game.label }}
                </span>
              </div>
            </div>
            <div class="mt-3 text-[11px] text-arcane-muted">
              View recent pulls and active card lists for this store.
            </div>
            </Link>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>