<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Header from '@/Components/Layout/Header.vue';

type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic';

interface Store {
  id: number;
  slug: string;
  name: string;
  city: string;
  postcode: string;
}

interface Batch {
  id: number;
  reference: string;
  type: string | null;
  created_at: string | null;
  pack_count: number;
  game: string | null;
  game_label: string | null;
}

interface PullCard {
  name: string | null;
  set: string | null;
  number: string | null;
  image: string | null;
  band: Rarity | null;
}

interface RecentPull {
  id: number;
  sequence: number;
  sold_at: string | null;
  batch: { id: number; reference: string | null; };
  card: PullCard | null;
}

interface Props {
  store: Store;
  batches: Batch[];
  recentPulls: RecentPull[];
}

const props = defineProps<Props>();

const bandPillClass = ( band: Rarity | null ): string => {
  if ( !band ) return 'bg-arcane-border text-arcane-muted';
  return {
    common: 'bg-arcane-common/20 text-arcane-common',
    rare: 'bg-arcane-rare/20 text-arcane-rare',
    super: 'bg-arcane-super/20 text-arcane-super',
    legendary: 'bg-arcane-legendary/20 text-arcane-legendary',
    mythic: 'bg-arcane-mythic/20 text-arcane-mythic',
  }[band];
};
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Header />

    <main class="flex-1 bg-arcane-bg">
      <div class="max-w-6xl mx-auto px-6 py-8 space-y-8">
        <!-- Store hero (same as before, maybe without odds now) -->

        <!-- Recent pulls -->
        <section class="card-panel p-4 md:p-5">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold">Recent pulls</h2>
            <span class="text-[11px] text-arcane-muted">
              Latest cards pulled from Arcane packs at this store.
            </span>
          </div>

          <div v-if=" recentPulls.length === 0 " class="text-xs text-arcane-muted">
            No recent pulls yet. Come back after a few packs have been opened.
          </div>

          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <div v-for=" pull in recentPulls " :key="pull.id" class="flex flex-col items-center text-center gap-1">
              <div
                class="w-full max-w-[130px] aspect-[245/342] rounded-lg overflow-hidden border border-arcane-border/60 bg-arcane-surface/80 flex items-center justify-center opacity-60">
                <template v-if=" pull.card?.image ">
                  <img :src="pull.card.image" alt="" class="w-full h-full object-cover" loading="lazy" />
                </template>
                <template v-else>
                  <div class="text-[10px] text-arcane-muted px-2">
                    Image not available
                  </div>
                </template>
              </div>
              <div class="w-full max-w-[130px]">
                <div class="text-[11px] font-semibold truncate">
                  {{ pull.card?.name ?? 'Unknown' }}
                </div>
                <div class="text-[10px] text-arcane-muted truncate">
                  {{ pull.card?.set }} · {{ pull.card?.number }}
                </div>
                <div class="mt-1 flex items-center justify-center gap-1 text-[10px] text-arcane-muted">
                  <span class="rarity-pill" :class="bandPillClass( pull.card?.band ?? null )">
                    {{ pull.card?.band ?? '' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Card lists (batches) -->
        <section class="card-panel p-4 md:p-5">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold">Card lists</h2>
            <span class="text-[11px] text-arcane-muted">
              Tap a list to view remaining hits for that batch.
            </span>
          </div>

          <div v-if=" batches.length === 0 " class="text-xs text-arcane-muted">
            No Arcane batches are currently live at this store.
          </div>

          <div v-else class="space-y-2">
            <Link v-for=" batch in batches " :key="batch.id"
              :href="route( 'store.lists.show', { store: store.slug, batch: batch.id } )"
              class="flex items-center justify-between gap-3 border border-arcane-border/60 rounded-lg px-3 py-2.5 bg-arcane-surface/80 hover:border-arcane-accent/60 transition">
            <div class="space-y-2">
              <div class="text-sm font-semibold">
                {{ batch.reference }}
              </div>
              <div class="flex items-center gap-2">
                <div v-if=" batch.game_label ">
                  <span
                    class="px-2 py-[2px] rounded-full text-[10px] bg-arcane-elevated text-arcane-muted border border-arcane-border/70">
                    {{ batch.game_label }}
                  </span>
                </div>
                <div class="text-xs text-arcane-muted">
                  {{ ( batch.type ?? '' ).toUpperCase() }} · {{ batch.pack_count }} packs
                </div>
              </div>
            </div>
            <div class="text-[11px] text-arcane-muted">
              View card list →
            </div>
            </Link>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>