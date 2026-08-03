<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic';

interface BandCard {
  sequence: number;
  status: string;
  name: string;
  set: string | null;
  number: string | null;
  image: string | null;
}

interface Batch {
  id: number;
  reference: string;
  type: string | null;
  pack_count: number;
  status: string;
  created_at: string | null;
  sold: number;
  store: { id: number; name: string };
  invoice: { id: number; number: string } | null;
  merged_into: { id: number; reference: string } | null;
  merge_request_batch: { id: number; reference: string } | null;
}

interface Props {
  batch: Batch;
  bands: Record<Rarity, BandCard[]>;
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

const bandOrder: { key: Rarity; label: string; text: string }[] = [
  { key: 'mythic', label: 'Mythic', text: '#c9a84c' },
  { key: 'legendary', label: 'Legendary', text: '#7b4fe9' },
  { key: 'super', label: 'Super', text: '#2dd4bf' },
  { key: 'rare', label: 'Rare', text: '#3b82f6' },
  { key: 'common', label: 'Common', text: '#a3a3a3' },
];
</script>

<template>
  <Head :title="batch.reference" />

  <SellerLayout :title="batch.reference" :subtitle="`${batch.store.name} · ${(batch.type ?? '').toUpperCase()} · ${batch.pack_count} packs`">
    <div class="flex items-center gap-3 mb-6">
      <span :class="['text-xs font-[\'Jost\',sans-serif] font-semibold uppercase px-3 py-1.5 rounded-[4px]', statusMeta(batch.status).color]">
        {{ statusMeta(batch.status).label }}
      </span>
      <a v-if="batch.invoice" :href="`/admin/invoices/${batch.invoice.id}/pdf`" target="_blank"
        class="text-xs font-['Jost',sans-serif] text-[#c9a84c] hover:underline">
        Invoice {{ batch.invoice.number }}
      </a>
    </div>

    <div v-if="batch.merged_into || batch.merge_request_batch"
      class="mb-6 bg-[rgba(124,58,237,0.08)] border border-[rgba(124,58,237,0.3)] rounded-[10px] p-4 text-sm font-['Jost',sans-serif] text-[#d8d3e0]">
      <p v-if="batch.merged_into">
        This batch's remaining packs were merged into
        <Link :href="`/seller/batches/${batch.merged_into.id}`" class="text-[#c9a84c] hover:underline">{{ batch.merged_into.reference }}</Link>.
      </p>
      <p v-if="batch.merge_request_batch">
        You requested batch {{ batch.merge_request_batch.reference }} be merged into this one once generated.
      </p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4 mb-8 max-w-xl">
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5">
        <p class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Packs sold</p>
        <p class="font-['Cinzel',sans-serif] font-bold text-2xl text-white">{{ batch.sold }} / {{ batch.pack_count }}</p>
      </div>
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5">
        <p class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Created</p>
        <p class="font-['Cinzel',sans-serif] font-bold text-2xl text-white">{{ batch.created_at ?? '—' }}</p>
      </div>
    </div>

    <!-- Card pool by band -->
    <div class="flex flex-col gap-10">
      <div v-for="band in bandOrder" :key="band.key">
        <div class="flex items-center gap-3 mb-4">
          <h2 class="font-['Cinzel',sans-serif] font-bold text-lg" :style="{ color: band.text }">{{ band.label }}</h2>
          <span class="font-['Jost',sans-serif] text-xs text-[#71717a]">{{ (bands[band.key] ?? []).length }} cards</span>
        </div>

        <div v-if="(bands[band.key] ?? []).length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
          <div v-for="card in bands[band.key]" :key="card.sequence"
            class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[8px] p-3 relative"
            :class="{ 'opacity-40': card.status === 'sold' }">
            <div class="aspect-[2.5/3.5] rounded-[6px] overflow-hidden bg-[#0d0b14] mb-2">
              <img v-if="card.image" :src="card.image" :alt="card.name" class="w-full h-full object-cover" loading="lazy" />
            </div>
            <p class="font-['Jost',sans-serif] text-xs text-white truncate">{{ card.name }}</p>
            <p class="font-['Jost',sans-serif] text-[10px] text-[#71717a] truncate">{{ card.set }} · #{{ card.number }}</p>
            <span v-if="card.status === 'sold'"
              class="absolute top-2 right-2 text-[9px] font-['Jost',sans-serif] font-semibold uppercase bg-[#0d0b14]/90 text-[#71717a] px-1.5 py-0.5 rounded">
              Sold
            </span>
          </div>
        </div>
      </div>
    </div>
  </SellerLayout>
</template>
