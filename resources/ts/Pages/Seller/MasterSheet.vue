<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SellerLayout from '@/Layouts/SellerLayout.vue';
import QrCodeCanvas from '@/Components/QrCodeCanvas.vue';

type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic';

interface Card {
  sequence: number;
  status: string;
  name: string;
  set: string | null;
  number: string | null;
  image: string | null;
  band: Rarity | null;
  scan_url: string | null;
}

interface Batch {
  id: number;
  reference: string;
  type: string | null;
  store: { name: string };
  pack_count: number;
}

const props = defineProps<{
  batch: Batch;
  cards: Card[];
}>();

const search = ref('');

const bandOrder: { key: Rarity; label: string; text: string }[] = [
  { key: 'mythic', label: 'Mythic', text: '#c9a84c' },
  { key: 'legendary', label: 'Legendary', text: '#7b4fe9' },
  { key: 'super', label: 'Super', text: '#2dd4bf' },
  { key: 'rare', label: 'Rare', text: '#3b82f6' },
  { key: 'common', label: 'Common', text: '#a3a3a3' },
];

const filteredCards = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!q) return props.cards;
  return props.cards.filter((c) =>
    c.name.toLowerCase().includes(q)
    || (c.set ?? '').toLowerCase().includes(q)
    || (c.number ?? '').toLowerCase().includes(q)
    || String(c.sequence).includes(q));
});

const bands = computed(() => {
  const grouped: Record<string, Card[]> = {};
  for (const card of filteredCards.value) {
    const key = card.band ?? 'unknown';
    (grouped[key] ??= []).push(card);
  }
  return grouped;
});
</script>

<template>
  <Head :title="`${batch.reference} — Master Sheet`" />

  <SellerLayout :title="`${batch.reference} — Master Sheet`"
    :subtitle="`${batch.store.name} · ${(batch.type ?? '').toUpperCase()} · ${batch.pack_count} packs · every card, including sold`">
    <div class="mb-8 max-w-md">
      <input v-model="search" type="text" placeholder="Search by card name, set, number, or pack #…"
        class="w-full bg-[#1a1628] border-2 border-[#3d2f6e] rounded-[8px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
      <p class="font-['Jost',sans-serif] text-xs text-[#71717a] mt-2">
        {{ filteredCards.length }} of {{ cards.length }} cards shown.
      </p>
    </div>

    <div class="flex flex-col gap-10">
      <div v-for="band in bandOrder" :key="band.key">
        <template v-if="(bands[band.key] ?? []).length">
          <div class="flex items-center gap-3 mb-4">
            <h2 class="font-['Cinzel',sans-serif] font-bold text-lg" :style="{ color: band.text }">{{ band.label }}</h2>
            <span class="font-['Jost',sans-serif] text-xs text-[#71717a]">{{ bands[band.key].length }} cards</span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div v-for="card in bands[band.key]" :key="card.sequence"
              class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[8px] p-3"
              :class="{ 'opacity-40': card.status === 'sold' }">
              <div class="aspect-[2.5/3.5] rounded-[6px] overflow-hidden bg-[#0d0b14] mb-2 relative">
                <img v-if="card.image" :src="card.image" :alt="card.name" class="w-full h-full object-cover" loading="lazy" />

                <div v-if="card.scan_url" class="absolute bottom-1 left-1 bg-white rounded-[3px] p-0.5 shadow-lg">
                  <QrCodeCanvas :value="card.scan_url" :size="100" />
                </div>

                <span v-if="card.status === 'sold'"
                  class="absolute top-2 right-2 text-[9px] font-['Jost',sans-serif] font-semibold uppercase bg-[#0d0b14]/90 text-[#71717a] px-1.5 py-0.5 rounded">
                  Sold
                </span>
              </div>
              <p class="font-['Jost',sans-serif] text-xs text-white truncate">{{ card.name }}</p>
              <p class="font-['Jost',sans-serif] text-[10px] text-[#71717a] truncate">
                #{{ card.sequence }} · {{ card.set }} <template v-if="card.number">· #{{ card.number }}</template>
              </p>
            </div>
          </div>
        </template>
      </div>

      <p v-if="filteredCards.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center">
        No cards match "{{ search }}".
      </p>
    </div>
  </SellerLayout>
</template>
