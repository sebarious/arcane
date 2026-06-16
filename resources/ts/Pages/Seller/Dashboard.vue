<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SellerHeader from '@/Components/Layout/SellerHeader.vue';

interface Store {
  id: number;
  name: string;
  slug: string;
  city: string;
}

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

interface Progress {
  sold: number;
  total: number;
  percent: number;
}

interface Props {
  stores: Store[];
  batches: Batch[];
  progress: Record<number, Progress>;
}

const props = defineProps<Props>();

</script>

<template>
  <div class="min-h-screen bg-arcane-bg text-arcane-text">
    <SellerHeader />

    <main class="max-w-6xl mx-auto px-6 py-8 space-y-8">
      <section>
        <h1 class="font-display text-3xl mb-2">
          Seller dashboard
        </h1>
        <p class="text-arcane-muted text-sm">
          Overview of your Arcane mystery card inventory.
        </p>
      </section>

      <section class="grid md:grid-cols-3 gap-4">
        <div class="card-panel p-4">
          <h2 class="text-sm text-arcane-muted mb-1">Stores</h2>
          <p class="text-2xl font-semibold">{{ stores.length }}</p>
        </div>
        <div class="card-panel p-4">
          <h2 class="text-sm text-arcane-muted mb-1">Active batches</h2>
          <p class="text-2xl font-semibold">
            {{ batches.length }}
          </p>
        </div>
        <div class="card-panel p-4">
          <h2 class="text-sm text-arcane-muted mb-1">Total packs</h2>
          <p class="text-2xl font-semibold">
            {{ batches.reduce( ( sum, b ) => sum + b.pack_count, 0 ) }}
          </p>
        </div>
      </section>

      <section>
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-semibold">Recent batches</h2>
          <Link href="/seller/batches" class="text-xs text-arcane-muted hover:text-arcane-accent">
          View all
          </Link>
        </div>

        <div v-if=" batches.length === 0 " class="text-arcane-muted text-sm">
          No batches yet. Once your first Arcane shipment is generated it will appear here.
        </div>

        <div v-else class="space-y-2">
          <div v-for=" batch in batches " :key="batch.id"
            class="card-panel p-4 flex items-center justify-between gap-4">
            <div class="flex-1">
              <div class="text-sm font-semibold">
                {{ batch.reference }}
              </div>
              <div class="text-xs text-arcane-muted">
                {{ stores.find( s => s.id === batch.store_id )?.name ?? 'Store' }}
                · {{ ( batch.type ?? '' ).toUpperCase() }} · {{ batch.pack_count }} packs
              </div>
            </div>
            <div class="text-right text-xs text-arcane-muted">
              <div>
                Sold:
                <span class="text-arcane-text font-semibold">
                  {{ progress[batch.id]?.sold ?? 0 }} / {{ progress[batch.id]?.total ?? batch.pack_count }}
                </span>
              </div>
            </div>
            <div>
              <Link :href="`/seller/batches/${batch.id}`" class="btn-ghost text-xs">
              View
              </Link>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>