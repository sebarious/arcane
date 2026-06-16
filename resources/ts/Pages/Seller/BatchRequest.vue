<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import SellerHeader from '@/Components/Layout/SellerHeader.vue';

interface Store {
  id: number;
  name: string;
}

interface Product {
  game: string;
  game_label: string;
  type: string;
  type_label: string;
  packs: number;
  price_pounds: number;
}

interface Props {
  stores: Store[];
  products: Product[];
}

const props = defineProps<Props>();

const form = useForm( {
  store_id: props.stores[0]?.id ?? null,
  game: 'pokemon',
  type: 'ruby',
  notes: '',
} );

const games = computed( () => {
  const seen = new Set<string>();
  return props.products
    .filter( p => {
      // TODO: remove as supported.
      const banned = ['mtg', 'lorcana', 'onepiece'];
      if ( banned.includes( p.game ) ) return false;

      if ( seen.has( p.game ) ) return false;
      seen.add( p.game );
      return true;
    } )
    .map( p => ( { value: p.game, label: p.game_label } ) );
} );

const typesForGame = computed( () =>
  props.products.filter( p => p.game === form.game )
);

const selectedProduct = computed( () =>
  props.products.find( p => p.game === form.game && p.type === form.type )
);

const submit = () => {
  form.post( '/seller/request-batch' );
};
</script>

<template>
  <div class="min-h-screen bg-arcane-bg text-arcane-text">
    <SellerHeader />

    <main class="max-w-3xl mx-auto px-6 py-8">
      <div class="card-panel p-6">
        <h1 class="font-display text-2xl mb-2">Request a new batch</h1>
        <p class="text-arcane-muted text-sm mb-6">
          Choose your store, game, and product. We'll review and dispatch.
        </p>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-medium mb-1">Store</label>
            <select v-model="form.store_id"
              class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white"
              required>
              <option v-for=" store in stores " :key="store.id" :value="store.id">
                {{ store.name }}
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium mb-1">Game</label>
              <select v-model="form.game"
                class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white"
                required>
                <option v-for=" g in games " :key="g.value" :value="g.value">
                  {{ g.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium mb-1">Product</label>
              <select v-model="form.type"
                class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white"
                required>
                <option v-for=" p in typesForGame " :key="p.type" :value="p.type">
                  {{ p.type_label }} — {{ p.packs }} packs, £{{ p.price_pounds.toFixed( 2 ) }}
                </option>
              </select>
            </div>
          </div>

          <div v-if=" selectedProduct " class="card-panel p-3 bg-arcane-elevated text-xs text-arcane-muted">
            <strong class="text-arcane-text">{{ selectedProduct.type_label }}</strong>
            — {{ selectedProduct.packs }} sealed mystery packs,
            invoiced at <strong class="text-arcane-text">£{{ selectedProduct.price_pounds.toFixed( 2 ) }}</strong> ex
            VAT.
          </div>

          <div>
            <label class="block text-xs font-medium mb-1">Notes (optional)</label>
            <textarea v-model="form.notes" rows="3"
              class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-white"
              placeholder="Anything we should know about delivery, timing, etc." />
          </div>

          <div class="outline-root w-full">
            <button type="submit" class="btn-primary w-full justify-center outline-inner" :disabled="form.processing">
              <span v-if=" form.processing ">Submitting…</span>
              <span v-else>Submit</span>
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>