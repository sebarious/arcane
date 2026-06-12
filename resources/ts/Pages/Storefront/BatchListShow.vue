<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, onBeforeUnmount, ref, computed } from 'vue';

type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic';

interface Store {
  id: number;
  slug: string;
  name: string;
  city: string;
  postcode: string;
}

interface BandCard {
  name: string | null;
  set: string | null;
  number: string | null;
  image: string | null;
  band: Rarity | null;
  sold?: boolean;
}

interface BandInfo {
  count: number;
  cards: BandCard[];
}

interface Props {
  store: Store;
  bands: Record<Rarity, BandInfo>;
}

const props = defineProps<Props>();

const bands = ref<Record<Rarity, BandInfo>>( props.bands );

const odds = {
  mythic: 1,
  legendary: 4,
  super: 10,
  rare: 25,
  common: 60,
} as Record<Rarity, number>;

const totalOdds = computed( () =>
  ( Object.values( odds ) as number[] ).reduce( ( sum, x ) => sum + x, 0 )
);

const bandOrder: { key: Rarity; label: string; }[] = [
  { key: 'mythic', label: 'Mythic' },
  { key: 'legendary', label: 'Legendary' },
  { key: 'super', label: 'Super' },
  { key: 'rare', label: 'Rare' },
  { key: 'common', label: 'Common' },
];

const pillClass = ( band: Rarity | null ): string => {
  if ( !band ) return 'bg-arcane-border text-arcane-muted';
  return {
    common: 'bg-arcane-common/20 text-arcane-common',
    rare: 'bg-arcane-rare/20 text-arcane-rare',
    super: 'bg-arcane-super/20 text-arcane-super',
    legendary: 'bg-arcane-legendary/20 text-arcane-legendary',
    mythic: 'bg-arcane-mythic/20 text-arcane-mythic',
  }[band];
};

const oddsBarClass = ( band: Rarity ): string => {
  return {
    common: 'bg-arcane-common/40',
    rare: 'bg-arcane-rare/40',
    super: 'bg-arcane-super/40',
    legendary: 'bg-arcane-legendary/40',
    mythic: 'bg-arcane-mythic/40',
  }[band];
};

const imageLoading = ( band: Rarity ): 'lazy' | 'eager' => {
  // Always load big chase bands immediately; lazy-load the rest
  return band === 'mythic' || band === 'legendary' ? 'eager' : 'lazy';
};

let channel: any = null;

onMounted( () => {
  if ( !window.Echo ) return;

  channel = window.Echo.channel( `store.${props.store.id}` )
    .listen( '.PackSold', ( payload: { band: Rarity | null; } ) => {
      const band = payload.band;
      if ( !band ) return;

      const current = bands.value[band];
      if ( !current ) return;

      const newCount = Math.max( 0, current.count - 1 );

      bands.value = {
        ...bands.value,
        [band]: {
          ...current,
          count: newCount,
        },
      };
    } );
} );

onBeforeUnmount( () => {
  if ( channel && window.Echo ) {
    window.Echo.leaveChannel( `store.${props.store.id}` );
  }
} );
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur">
      <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <Link href="/" class="font-display text-xl tracking-[0.3em] text-arcane-accent">
        ARCANE
        </Link>
        <nav class="flex items-center gap-3 text-sm text-arcane-muted">
          <Link href="/stores" class="hover:text-arcane-accent">Stores</Link>
          <span>/</span>
          <Link :href="route( 'stores.show', { store: props.store.slug } )" class="hover:text-arcane-accent">{{ props.store.name }}</Link>
          <span>/</span>
          <span>Card List</span>
        </nav>
      </div>
    </header>

    <!-- Main -->
    <main class="flex-1 bg-arcane-bg">
      <div class="max-w-6xl mx-auto px-6 py-8 space-y-8">
        <!-- Store hero and odds -->
        <section class="card-panel p-4 md:p-5 flex flex-col gap-4">
          <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div>
              <h1 class="font-display text-3xl mb-1">
                {{ store.name }}
              </h1>
              <p class="text-arcane-muted text-sm">
                {{ store.city }}, {{ store.postcode }}
              </p>
              <p class="text-arcane-muted text-xs mt-2 max-w-xl">
                Live view of cards still in sealed Arcane packs. When a pack is sold
                and scanned, the card disappears from this list in real time.
              </p>
            </div>
            <div class="text-xs text-arcane-muted md:text-right">
              <p>Odds are approximate based on this store’s batch design.</p>
            </div>
          </div>

          <!-- Odds bar -->
          <div class="mt-3 space-y-2">
            <div class="flex items-center justify-between text-[11px] text-arcane-muted">
              <span>Hit odds by rarity (approx.)</span>
            </div>
            <div class="flex h-2 rounded-full overflow-hidden border border-arcane-border/60 bg-arcane-surface/70">
              <div v-for="  band in bandOrder  " :key="band.key" :class="oddsBarClass( band.key )"
                :style="{ width: ( ( odds[band.key] / totalOdds ) * 100 ).toFixed( 1 ) + '%' }" />
            </div>
            <div class="flex flex-wrap gap-2 mt-1 text-[10px] text-arcane-muted">
              <div v-for="  band in bandOrder  " :key="band.key" class="flex items-center gap-1">
                <span class="inline-flex w-3 h-1 rounded-full" :class="oddsBarClass( band.key )" />
                <span>{{ band.label }}: ~{{ ( ( odds[band.key] / totalOdds ) * 100 ).toFixed( 1 ) }}%</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Rarity sections -->
        <section class="space-y-6">
          <div v-for="  band in bandOrder  " :key="band.key" class="card-panel p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <span class="rarity-pill" :class="pillClass( band.key )">
                  {{ band.label }}
                </span>
              </div>
              <span class="text-sm text-arcane-muted">
                {{ bands[band.key]?.count ?? 0 }} remaining
              </span>
            </div>

            <!-- No cards left in this band -->
            <div v-if=" ( bands[band.key]?.count ?? 0 ) === 0 " class="text-xs text-arcane-muted">
              No cards of this band are currently left in sealed Arcane packs.
            </div>

            <!-- Image grid for ALL bands (including common) -->
            <div v-else>
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <div v-for="  card in bands[band.key].cards  "
                  :key="( card.name || '' ) + ( card.number || '' ) + ( card.set || '' )"
                  class="flex flex-col items-center text-center gap-1">
                  <div
                    class="w-full max-w-[130px] aspect-[245/342] rounded-lg overflow-hidden border border-arcane-border/60 bg-arcane-surface/80 flex items-center justify-center"
                    :class="card.sold ? 'opacity-40 grayscale' : ''">
                    <template v-if=" card.image ">
                      <img :src="card.image" alt="" class="w-full h-full object-cover transition"
                        :loading="imageLoading( band.key )" />
                    </template>
                    <template v-else>
                      <div class="text-[10px] text-arcane-muted px-2">
                        Image not available
                      </div>
                    </template>
                  </div>
                  <div class="w-full max-w-[130px]">
                    <div class="text-[11px] font-semibold truncate">
                      {{ card.name }}
                    </div>
                    <div class="text-[10px] text-arcane-muted truncate">
                      {{ card.set }} · {{ card.number }}
                    </div>
                  </div>
                </div>
              </div>
              <p class="text-[11px] text-arcane-muted mt-2"
                v-if=" ( bands[band.key]?.count ?? 0 ) > bands[band.key].cards.length ">
                Showing {{ bands[band.key].cards.length }} of {{ bands[band.key].count }}
                remaining {{ band.label.toLowerCase() }}.
              </p>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>