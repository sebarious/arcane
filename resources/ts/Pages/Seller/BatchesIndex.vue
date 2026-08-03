<script setup lang="ts">
import { Link, Head, router } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

interface Batch {
  id: number;
  reference: string;
  store_id: number;
  type: string | null;
  pack_count: number;
  status: string;
  is_merged: boolean;
  created_at?: string;
  sold: number;
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
  filters: { status: string | null };
}

defineProps<Props>();

const statusMeta = (status: string): { label: string; color: string } => {
  switch (status) {
    case 'draft': return { label: 'Requested', color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
    case 'committed': return { label: 'Live', color: 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' };
    case 'dispatched': return { label: 'Dispatched', color: 'text-[#3b82f6] bg-[rgba(59,130,246,0.1)]' };
    case 'completed': return { label: 'Completed', color: 'text-[#c9a84c] bg-[rgba(201,168,76,0.1)]' };
    case 'cancelled': return { label: 'Cancelled', color: 'text-red-400 bg-red-400/10' };
    default: return { label: status, color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
  }
};

const STATUS_TABS = [
  { value: null, label: 'All' },
  { value: 'draft', label: 'Requested' },
  { value: 'committed', label: 'Live' },
  { value: 'dispatched', label: 'Dispatched' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
];

function filterBy(status: string | null) {
  router.get('/seller/batches', status ? { status } : {}, { preserveState: true, preserveScroll: true });
}
</script>

<template>
  <Head title="Batches" />

  <SellerLayout title="Batches" subtitle="Every Arcane mystery pack batch allocated to your store(s).">
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
      <button v-for="tab in STATUS_TABS" :key="tab.label" @click="filterBy(tab.value)"
        :class="['shrink-0 px-4 py-2 rounded-[6px] text-xs font-[\'Jost\',sans-serif] font-semibold uppercase tracking-wide transition-colors',
          (filters.status ?? null) === tab.value
            ? 'bg-[rgba(124,58,237,0.2)] text-white border border-[rgba(124,58,237,0.4)]'
            : 'text-[#a3a3a3] border border-[#3d2f6e] hover:border-[#c9a84c]']">
        {{ tab.label }}
      </button>
    </div>

    <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden">
      <div v-if="batches.data.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center">
        No batches here yet.
      </div>

      <table v-else class="min-w-full text-sm">
        <thead class="text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-['Jost',sans-serif] text-xs uppercase tracking-wide">
          <tr class="text-left">
            <th class="py-3 px-5">Reference</th>
            <th class="py-3 px-5">Store</th>
            <th class="py-3 px-5">Product</th>
            <th class="py-3 px-5">Sold</th>
            <th class="py-3 px-5">Status</th>
            <th class="py-3 px-5"></th>
          </tr>
        </thead>
        <tbody class="font-['Jost',sans-serif]">
          <tr v-for="batch in batches.data" :key="batch.id" class="border-b border-[rgba(220,193,117,0.06)] last:border-0">
            <td class="py-3 px-5 text-white font-medium">
              {{ batch.reference }}
              <span v-if="batch.is_merged" class="ml-1 text-[10px] text-[#71717a]">(merged)</span>
            </td>
            <td class="py-3 px-5 text-[#a3a3a3]">{{ storesById[batch.store_id]?.name ?? 'Store' }}</td>
            <td class="py-3 px-5 text-[#a3a3a3] text-xs uppercase">{{ batch.type ?? '' }}</td>
            <td class="py-3 px-5 text-[#a3a3a3]">
              <template v-if="!batch.is_merged">{{ batch.sold }} / {{ batch.pack_count }}</template>
            </td>
            <td class="py-3 px-5">
              <span v-if="!batch.is_merged" :class="['text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]', statusMeta(batch.status).color]">
                {{ statusMeta(batch.status).label }}
              </span>
            </td>
            <td class="py-3 px-5 text-right">
              <Link v-if="!batch.is_merged" :href="`/seller/batches/${batch.id}`" class="text-[#c9a84c] hover:underline text-xs font-semibold">
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="batches.links.length > 3" class="flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]">
        <template v-for="link in batches.links" :key="link.label">
          <Link v-if="link?.url" :href="link.url" preserve-state preserve-scroll
            class="px-2.5 py-1 rounded border font-['Jost',sans-serif]"
            :class="link.active ? 'bg-[#c9a84c] text-[#0d0b14] border-[#c9a84c]' : 'text-[#a3a3a3] border-[#3d2f6e] hover:border-[#c9a84c]'"
            v-html="link.label" />
        </template>
      </div>
    </div>
  </SellerLayout>
</template>
