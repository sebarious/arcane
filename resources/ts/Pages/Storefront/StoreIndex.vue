<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Header from '@/Components/Layout/Header.vue';
import Store from '@/Components/Cards/Store.vue';

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
            <Store v-for=" store in stores " :key="store.id" :store="store" />
          </div>
        </section>
      </div>
    </main>
  </div>
</template>