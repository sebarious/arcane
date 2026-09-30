<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import RipAccountLayout from '@/Layouts/RipAccountLayout.vue';

interface MyRip {
  id: number;
  pack_name: string;
  price_pence: number;
  opened: boolean;
  decision: 'kept' | 'sold_back' | null;
  sold_back_pence: number | null;
  card: { name: string; image: string; band: string } | null;
  created_at: string;
}

const props = defineProps<{ rips: MyRip[] }>();

// Unopened packs are the ones still asking for a decision — surfaced first
// and in their own section, rather than mixed in date-order with everything
// already dealt with.
const unopened = computed(() => props.rips.filter((r) => !r.opened));
const opened = computed(() => props.rips.filter((r) => r.opened));

// With more than one unopened pack, clicking any of them opens the stacked
// "go through everything at once" popup instead of a single pack on its own.
const unopenedHref = computed(() => (unopened.value.length > 1 ? '/rips/my/unopened' : `/rips/my/${unopened.value[0]?.id}`));

const formatMoney = (pence: number) => '£' + (pence / 100).toFixed(2);
const formatDate = (iso: string) => new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
  <Head title="My Rips" />

  <RipAccountLayout title="My rips" subtitle="Every pack you've bought — tap an unopened one to reveal it.">
    <div v-if="rips.length === 0" class="text-center text-white/40 py-20 font-['Jost',sans-serif]">
      You haven't bought a pack yet.
      <Link href="/rips" class="text-[#DCC175] hover:underline block mt-2">Browse packs</Link>
    </div>

    <template v-else>
      <section v-if="unopened.length > 0" class="mb-10">
        <h2 class="text-xs tracking-[0.2em] uppercase text-[#DCC175] mb-4 font-['Jost',sans-serif]">
          Unopened ({{ unopened.length }})
        </h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <Link v-for="rip in unopened" :key="rip.id" :href="unopenedHref"
            class="bg-[#13101e] border border-[rgba(220,193,117,0.3)] rounded-[12px] p-5 hover:border-[rgba(220,193,117,0.55)] transition-colors flex gap-4">
            <div class="w-16 shrink-0 rounded-[6px] overflow-hidden bg-[#0d0b14]" style="aspect-ratio: 63/88;">
              <div class="w-full h-full flex items-center justify-center text-white/20 text-[10px] font-['Jost',sans-serif]">Sealed</div>
            </div>
            <div class="min-w-0">
              <p class="text-white text-sm font-semibold truncate" style="font-family: 'Cinzel', serif;">{{ rip.pack_name }}</p>
              <p class="text-white/40 text-xs font-['Jost',sans-serif] mb-2">{{ formatDate(rip.created_at) }} · {{ formatMoney(rip.price_pence) }}</p>
              <span class="text-[9px] tracking-[0.15em] uppercase font-bold px-2 py-1 rounded bg-[rgba(220,193,117,0.15)] text-[#DCC175]">
                Unopened
              </span>
            </div>
          </Link>
        </div>
      </section>

      <section v-if="opened.length > 0">
        <h2 class="text-xs tracking-[0.2em] uppercase text-white/40 mb-4 font-['Jost',sans-serif]">
          Opened
        </h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <Link v-for="rip in opened" :key="rip.id" :href="`/rips/my/${rip.id}`"
            class="bg-[#13101e] border border-[rgba(220,193,117,0.12)] rounded-[12px] p-5 hover:border-[rgba(220,193,117,0.35)] transition-colors flex gap-4">
            <div class="w-16 shrink-0 rounded-[6px] overflow-hidden bg-[#0d0b14]" style="aspect-ratio: 63/88;">
              <img v-if="rip.card" :src="rip.card.image" :alt="rip.card.name" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-white/20 text-[10px] font-['Jost',sans-serif]">Sealed</div>
            </div>
            <div class="min-w-0">
              <p class="text-white text-sm font-semibold truncate" style="font-family: 'Cinzel', serif;">{{ rip.card?.name ?? rip.pack_name }}</p>
              <p class="text-white/40 text-xs font-['Jost',sans-serif] mb-2">{{ formatDate(rip.created_at) }} · {{ formatMoney(rip.price_pence) }}</p>
              <span v-if="rip.decision === 'kept'" class="text-[9px] tracking-[0.15em] uppercase font-bold px-2 py-1 rounded bg-[rgba(220,193,117,0.1)] text-[#DCC175]/80">
                Kept
              </span>
              <span v-else-if="rip.decision === 'sold_back'" class="text-[9px] tracking-[0.15em] uppercase font-bold px-2 py-1 rounded bg-[rgba(45,212,191,0.1)] text-[#2dd4bf]">
                Sold back {{ formatMoney(rip.sold_back_pence ?? 0) }}
              </span>
              <span v-else class="text-[9px] tracking-[0.15em] uppercase font-bold px-2 py-1 rounded bg-white/5 text-white/40">
                Awaiting decision
              </span>
            </div>
          </Link>
        </div>
      </section>
    </template>
  </RipAccountLayout>
</template>
