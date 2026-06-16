<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SellerHeader from '@/Components/Layout/SellerHeader.vue';

interface CardInfo {
  name: string | null;
  set: string | null;
  number: string | null;
  image: string | null;
  band: string | null;
}

interface Pack {
  id: number;
  sequence: number;
  status: string;
  card: CardInfo | null;
}

interface Batch {
  id: number;
  reference: string;
  type: string | null;
  pack_count: number;
  sale_price_pence: number | null;
  total_cost_pence: number | null;
  total_market_value_pence: number | null;
  margin_pence: number | null;
  status: string;
  store: { id: number; name: string; };
}

interface Props {
  batch: Batch;
  packs: Pack[];
}

const props = defineProps<Props>();

const formatMoney = ( pence: number | null | undefined ): string => {
  if ( !pence ) return '£0.00';
  return '£' + ( pence / 100 ).toFixed( 2 );
};

const statusLabel = ( status: string ): string => {
  switch ( status ) {
    case 'draft': return 'Draft';
    case 'committed': return 'Live';
    case 'dispatched': return 'Dispatched';
    case 'completed': return 'Completed';
    case 'cancelled': return 'Cancelled';
    default: return status;
  }
};

const bandLabel = ( band: string | null ): string => {
  switch ( band ) {
    case 'common': return 'Common';
    case 'rare': return 'Rare';
    case 'super': return 'Super';
    case 'legendary': return 'Legendary';
    case 'mythic': return 'Mythic';
    default: return band ?? '—';
  }
};
</script>

<template>
  <div class="min-h-screen bg-arcane-bg text-arcane-text">
    <SellerHeader />

    <main class="max-w-6xl mx-auto px-6 py-8 space-y-6">
      <section class="card-panel p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="font-display text-2xl mb-1">
            {{ batch.reference }}
          </h1>
          <p class="text-arcane-muted text-sm">
            {{ batch.store.name }} · {{ ( batch.type ?? '' ).toUpperCase() }} · {{ batch.pack_count }} packs
          </p>
          <p class="text-arcane-muted text-xs mt-1">
            Status: {{ statusLabel( batch.status ) }}
          </p>
        </div>
      </section>

      <section class="card-panel p-4 overflow-x-auto">
        <h2 class="text-lg font-semibold mb-3">Packs</h2>
        <table class="min-w-full text-sm">
          <thead class="text-arcane-muted border-b border-arcane-border/60">
            <tr class="text-left">
              <th class="py-2 pr-4">#</th>
              <th class="py-2 pr-4">Card</th>
              <th class="py-2 pr-4">Set</th>
              <th class="py-2 pr-4">Band</th>
              <th class="py-2 pr-4">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if=" packs.length === 0 ">
              <td colspan="5" class="py-4 text-arcane-muted text-sm">
                No packs found for this batch.
              </td>
            </tr>
            <tr v-for=" pack in packs " :key="pack.id" class="border-b border-arcane-border/40">
              <td class="py-2 pr-4">
                #{{ pack.sequence }}
              </td>
              <td class="py-2 pr-4">
                {{ pack.card?.name ?? '—' }}
              </td>
              <td class="py-2 pr-4 text-arcane-muted text-xs">
                {{ pack.card?.set ?? '' }} <span v-if=" pack.card?.number ">· {{ pack.card.number }}</span>
              </td>
              <td class="py-2 pr-4 text-xs">
                <span class="rarity-pill bg-arcane-border/40 text-arcane-muted">
                  {{ bandLabel( pack.card?.band ?? null ) }}
                </span>
              </td>
              <td class="py-2 pr-4 text-xs">
                <span class="rarity-pill bg-arcane-border/40 text-arcane-muted">
                  {{ statusLabel( pack.status ) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</template>