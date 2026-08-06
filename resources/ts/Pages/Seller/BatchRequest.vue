<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SellerLayout from '@/Layouts/SellerLayout.vue';

const page = usePage();
const vatRegistered = computed(() => (page.props.vatRegistered as boolean | undefined) ?? false);

function formatPrice(pounds: number): string {
  return '£' + pounds.toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

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

interface MergeableBatch {
  id: number;
  reference: string;
  store_id: number;
  type: string;
  pack_count: number;
}

interface Props {
  stores: Store[];
  products: Product[];
  mergeableBatches: MergeableBatch[];
}

const props = defineProps<Props>();

const form = useForm({
  store_id: props.stores[0]?.id ?? null,
  game: 'pokemon',
  type: 'ruby',
  notes: '',
  wants_merge: false,
  merge_request_batch_id: null as number | null,
});

const games = computed(() => {
  const seen = new Set<string>();
  return props.products
    .filter(p => {
      const banned = ['mtg', 'lorcana', 'onepiece'];
      if (banned.includes(p.game)) return false;
      if (seen.has(p.game)) return false;
      seen.add(p.game);
      return true;
    })
    .map(p => ({ value: p.game, label: p.game_label }));
});

const typesForGame = computed(() =>
  props.products.filter(p => p.game === form.game)
);

const selectedProduct = computed(() =>
  props.products.find(p => p.game === form.game && p.type === form.type)
);

const mergeableForStore = computed(() =>
  props.mergeableBatches.filter(b => b.store_id === form.store_id)
);

const submit = () => {
  form.transform((data) => ({
    ...data,
    merge_request_batch_id: data.wants_merge ? data.merge_request_batch_id : null,
  })).post('/seller/request-batch');
};
</script>

<template>
  <Head title="Request a batch" />

  <SellerLayout title="Request a batch" subtitle="Choose your store, game, and product — we'll review and generate it shortly.">
    <div class="max-w-2xl bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-8">
      <form @submit.prevent="submit" class="flex flex-col gap-5">
        <div v-if="stores.length > 1">
          <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Store</label>
          <select v-model="form.store_id"
            class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]"
            required>
            <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Game</label>
            <select v-model="form.game"
              class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]"
              required>
              <option v-for="g in games" :key="g.value" :value="g.value">{{ g.label }}</option>
            </select>
          </div>
          <div>
            <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Product</label>
            <select v-model="form.type"
              class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]"
              required>
              <option v-for="p in typesForGame" :key="p.type" :value="p.type">
                {{ p.type_label }} — {{ p.packs }} packs, {{ formatPrice(p.price_pounds) }}
              </option>
            </select>
          </div>
        </div>

        <div v-if="selectedProduct" class="bg-[#1a1628] border border-[rgba(124,58,237,0.25)] rounded-[8px] p-4 text-xs text-[#a3a3a3] font-['Jost',sans-serif]">
          <strong class="text-white">{{ selectedProduct.type_label }}</strong>
          — {{ selectedProduct.packs }} sealed mystery packs,
          invoiced at <strong class="text-[#c9a84c]">{{ formatPrice(selectedProduct.price_pounds) }}</strong><template v-if="vatRegistered"> ex VAT</template>,
          due 48 hours after generation.
        </div>

        <!-- Merge option -->
        <div class="border border-[#3d2f6e] rounded-[8px] p-4">
          <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" v-model="form.wants_merge" class="mt-1 accent-[#c9a84c]" />
            <span class="font-['Jost',sans-serif] text-sm text-white">
              Merge one of my existing batches into this new one
              <span class="block text-xs text-[#71717a] mt-0.5 font-normal">
                If one of your batches is running low, we can move its remaining packs into this
                fresh one once it's generated — restores balanced pull odds instead of running two thin pools.
              </span>
            </span>
          </label>

          <div v-if="form.wants_merge" class="mt-4">
            <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">
              Batch to merge in
            </label>
            <select v-model="form.merge_request_batch_id"
              class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]">
              <option :value="null">Select a batch…</option>
              <option v-for="b in mergeableForStore" :key="b.id" :value="b.id">
                {{ b.reference }} — {{ (b.type ?? '').toUpperCase() }}, {{ b.pack_count }} packs
              </option>
            </select>
            <p v-if="mergeableForStore.length === 0" class="text-xs text-[#71717a] mt-2">
              No eligible batches for this store yet.
            </p>
          </div>
        </div>

        <div>
          <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">
            Notes <span class="normal-case font-normal text-[#71717a]">(optional)</span>
          </label>
          <textarea v-model="form.notes" rows="3"
            placeholder="Anything we should know about delivery, timing, etc."
            class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
        </div>

        <button type="submit" :disabled="form.processing"
          class="w-full px-6 py-3.5 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14] disabled:opacity-50"
          style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
          {{ form.processing ? 'Submitting…' : 'Submit request' }}
        </button>
      </form>
    </div>
  </SellerLayout>
</template>
