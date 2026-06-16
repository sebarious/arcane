<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SellerHeader from '@/Components/Layout/SellerHeader.vue';

interface Batch {
  id: number;
  reference: string;
  store_id: number;
  type: string | null;
  pack_count: number;
  sale_price_pence: number | null;
  total_cost_pence: number | null;
  margin_pence: number | null;
  status: string;
  created_at?: string;
}

interface Store {
  id: number;
  name: string;
}

interface Paginated<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean; }[];
}

interface Props {
  batches: Paginated<Batch>;
  storesById: Record<number, Store>;
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
</script>

<template>
  <div class="min-h-screen bg-arcane-bg text-arcane-text">
    <SellerHeader />

    <main class="max-w-6xl mx-auto px-6 py-8 space-y-6">
      <section>
        <h1 class="font-display text-3xl mb-2">Batches</h1>
        <p class="text-arcane-muted text-sm">
          All Arcane mystery pack batches allocated to your store(s).
        </p>
      </section>

      <section class="card-panel p-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-arcane-muted border-b border-arcane-border/60">
            <tr class="text-left">
              <th class="py-2 pr-4">Reference</th>
              <th class="py-2 pr-4">Store</th>
              <th class="py-2 pr-4">Product</th>
              <th class="py-2 pr-4 text-right">Packs</th>
              <th class="py-2 pr-4">Status</th>
              <th class="py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if=" batches.data.length === 0 ">
              <td colspan="9" class="py-4 text-arcane-muted text-sm">
                No batches yet.
              </td>
            </tr>
            <tr v-for=" batch in batches.data " :key="batch.id" class="border-b border-arcane-border/40">
              <td class="py-2 pr-4">
                {{ batch.reference }}
              </td>
              <td class="py-2 pr-4">
                {{ storesById[batch.store_id]?.name ?? 'Store' }}
              </td>
              <td class="py-2 pr-4 uppercase text-xs text-arcane-muted">
                {{ batch.type ?? '' }}
              </td>
              <td class="py-2 pr-4 text-right">
                {{ batch.pack_count }}
              </td>
              <td class="py-2 pr-4 text-xs">
                <span class="rarity-pill bg-arcane-border/40 text-arcane-muted">
                  {{ statusLabel( batch.status ) }}
                </span>
              </td>
              <td class="py-2 text-right">
                <Link :href="`/seller/batches/${batch.id}`" class="btn-ghost text-xs">
                View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="mt-4 flex justify-end gap-1 text-xs">
          <template v-for="  link in batches.links  " :key="link.label">
            <Link v-if=" link?.url " :href="link.url" class="px-2 py-1 rounded border border-arcane-border/60"
              :class="link.active ? 'bg-arcane-accent text-arcane-bg' : 'text-arcane-muted hover:bg-arcane-elevated'"
              v-html="link.label" />
          </template>
        </div>
      </section>
    </main>
  </div>
</template>