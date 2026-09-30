<script setup lang="ts">
import { computed, nextTick, reactive, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import PopupShell from '@/Components/Rips/PopupShell.vue';
import TearOpenAnimation from '@/Components/Rips/TearOpenAnimation.vue';
import ConfirmModal from '@/Components/Rips/ConfirmModal.vue';
import { toast } from '@/toast';
import { RARITY_LABELS, RARITY_COLORS } from '@/rarity';
import packFallback from '@/Assets/Arcane_pack.webp';
import type { Card } from '../../types';

interface RipCard extends Card {
  market_value_pence: number;
}

interface OrderRip {
  id: number;
  pack_name: string;
  price_pence: number;
  buy_back_percentage: number;
  ready: boolean;
  opened: boolean;
  decision: 'kept' | 'sold_back' | null;
  sold_back_pence: number | null;
  card: RipCard | null;
}

// Shown by both a fresh multi-pack order (title: "Order RIP-2026-0001") and
// the "open everything you haven't got to yet" view from /rips/my (title:
// "Unopened packs") — same stacked-popup experience either way, just a
// different heading and a different set of rips behind it.
const props = defineProps<{ title: string; rips: OrderRip[] }>();

const formatMoney = (pence: number) => '£' + (pence / 100).toFixed(2);

interface RipState {
  card: RipCard | null;
  buyBackPence: number | null;
  revealed: boolean;
  opening: boolean;
  decision: 'kept' | 'sold_back' | null;
  soldBackPence: number | null;
  deciding: boolean;
  error: string;
}

const state = reactive<Record<number, RipState>>(
  Object.fromEntries(props.rips.map((rip) => [
    rip.id,
    {
      card: rip.card,
      buyBackPence: rip.card ? Math.round(rip.card.market_value_pence * rip.buy_back_percentage) : null,
      revealed: rip.opened,
      opening: false,
      decision: rip.decision,
      soldBackPence: rip.sold_back_pence,
      deciding: false,
      error: '',
    },
  ])),
);

// Only ever the front (current) pack is a live, interactive TearOpenAnimation
// — everything still waiting behind it is just a static pack-back image, so
// there's only ever one ref to manage rather than one per rip.
const animationRef = ref<InstanceType<typeof TearOpenAnimation> | null>(null);

// How far into the order we are — packs before this index are fully decided
// and dropped from the visible stack (see `stack` below).
const currentIndex = ref(props.rips.findIndex((rip) => rip.decision === null));

// The still-undecided packs, front first — what's actually rendered as the
// stack. A pack that already had a decision (e.g. revisiting this order
// after leaving mid-way) never appears here.
const stack = computed(() => props.rips.slice(currentIndex.value === -1 ? props.rips.length : currentIndex.value));
const current = computed<OrderRip | null>(() => stack.value[0] ?? null);
const currentState = computed<RipState | null>(() => (current.value ? state[current.value.id] : null));

const allDone = computed(() => stack.value.length === 0);

async function handleTap() {
  const rip = current.value;
  if (!rip) return;

  const s = state[rip.id];
  if (s.opening) return;
  s.opening = true;

  try {
    const { data } = await axios.post(`/rips/my/${rip.id}/open`);
    s.card = data.data.card;
    s.buyBackPence = data.data.buy_back_pence;

    await nextTick();
    animationRef.value?.play();
  } catch (e: any) {
    s.error = e?.response?.data?.message ?? 'Couldn\'t open this pack — please try again.';
    s.opening = false;
  }
}

function onRevealed() {
  if (current.value) state[current.value.id].revealed = true;
}

const showConfirm = ref<'kept' | 'sold_back' | null>(null);

function askDecide(choice: 'kept' | 'sold_back') {
  showConfirm.value = choice;
}

async function confirmDecide() {
  const choice = showConfirm.value;
  showConfirm.value = null;
  if (choice) await decide(choice);
}

async function decide(choice: 'kept' | 'sold_back') {
  const rip = current.value;
  if (!rip) return;

  const s = state[rip.id];
  s.deciding = true;
  s.error = '';

  try {
    const { data } = await axios.post(`/rips/my/${rip.id}/decide`, { decision: choice });
    s.decision = data.data.decision;
    s.soldBackPence = data.data.sold_back_pence;
    toast(choice === 'kept' ? 'Kept — it\'s yours.' : `Sold back for ${formatMoney(data.data.sold_back_pence ?? 0)}.`);
  } catch (e: any) {
    s.error = e?.response?.data?.message ?? 'Something went wrong — please try again.';
  } finally {
    s.deciding = false;
  }
}

// Moves the just-decided pack out of the stack and resets the animation
// component (a new TearOpenAnimation instance mounts for the next rip, since
// its :card differs) so the next pack in line becomes interactive. Skips
// past any rip already decided (e.g. this order was opened partway via
// /rips/my/{rip} directly, out of sequence) rather than assuming everything
// before currentIndex is decided and everything after isn't.
function nextPack() {
  do {
    currentIndex.value++;
  } while (
    currentIndex.value < props.rips.length
    && state[props.rips[currentIndex.value].id].decision !== null
  );
}

// Cascading offset for each pack still waiting behind the front one — index
// 0 is the live front pack (no offset, handled separately in the template).
// Capped at MAX_FANNED_DEPTH so a big order (five, ten packs) doesn't fan
// out indefinitely — beyond that depth, extra packs just sit at the same
// back-most position (still visible via the "Pack X of N" counter above).
const MAX_FANNED_DEPTH = 3;

function stackStyle(i: number) {
  const depth = Math.min(i, MAX_FANNED_DEPTH);

  return {
    transform: `translate(${depth * 16}px, ${depth * 12}px) rotate(${depth * 5}deg) scale(${1 - depth * 0.05})`,
    opacity: 1 - depth * 0.18,
    zIndex: 50 - depth,
  };
}
</script>

<template>
  <Head :title="title" />

  <PopupShell close-href="/rips/my">
    <div class="text-center py-6">
      <p v-if="!allDone" class="text-white/40 text-xs tracking-[0.2em] uppercase mb-6 font-['Jost',sans-serif]">
        Pack {{ rips.length - stack.length + 1 }} of {{ rips.length }}
      </p>

      <template v-if="!allDone && current && currentState">
        <div v-if="!current.ready" class="max-w-md mx-auto py-16">
          <p class="text-white/60 font-['Jost',sans-serif]">
            Your payment is still being confirmed — this will update automatically in a moment.
          </p>
        </div>

        <template v-else>
          <!-- The stack: front pack is live and interactive; anything still
          waiting behind it is a static, offset pack-back peeking out so you
          can see how many are left. The outer wrapper reserves room for the
          fanned-out offset (up to MAX_FANNED_DEPTH * 16/12px) in normal
          layout flow, so those absolutely-positioned children never exceed
          it and never force the popup's own scroll area to grow. -->
          <div class="mx-auto" style="width: calc(min(320px, 80vw) + 48px); padding-bottom: 36px;">
            <div class="relative" style="width: min(320px, 80vw); aspect-ratio: 397/472;">
              <img v-for="(rip, i) in stack.slice(1).reverse()" :key="rip.id" :src="packFallback" alt=""
                class="absolute inset-0 w-full h-full object-cover rounded-lg shadow-2xl pointer-events-none transition-transform duration-300"
                :style="stackStyle(stack.length - 1 - i)" />

              <div class="relative" :style="{ zIndex: 50 }">
                <TearOpenAnimation :key="current.id" ref="animationRef" :card="currentState.card" :initially-revealed="current.opened"
                  @tap="handleTap" @revealed="onRevealed" />
              </div>
            </div>
          </div>

          <p v-if="currentState.error" class="text-red-400 text-sm mt-6 font-['Jost',sans-serif]">{{ currentState.error }}</p>

          <div v-if="currentState.revealed && currentState.card" class="mt-8">
            <h2 class="text-2xl text-white mb-1" style="font-family: 'Cinzel', serif; font-weight: 800;">{{ currentState.card.name }}</h2>
            <p class="text-white/50 text-sm mb-1 font-['Jost',sans-serif]">{{ currentState.card.set }}</p>
            <p class="text-xs font-bold tracking-[0.15em] uppercase mb-6 font-['Jost',sans-serif]" :style="{ color: RARITY_COLORS[currentState.card.band] }">
              {{ RARITY_LABELS[currentState.card.band] ?? currentState.card.band }}
            </p>

            <template v-if="!currentState.decision">
              <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button type="button" @click="askDecide('kept')" :disabled="currentState.deciding"
                  class="flex-1 sm:flex-none px-8 py-4 border border-[#DCC175]/50 text-[#DCC175] text-xs tracking-[0.2em] uppercase font-bold rounded-[4px] hover:bg-[#DCC175]/10 transition-colors disabled:opacity-40">
                  Keep it
                </button>
                <button type="button" @click="askDecide('sold_back')" :disabled="currentState.deciding"
                  class="flex-1 sm:flex-none px-8 py-4 bg-gradient-to-r from-[#2dd4bf] to-[#5eead4] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[4px] transition-opacity disabled:opacity-40">
                  Sell back for {{ formatMoney(currentState.buyBackPence ?? 0) }}
                </button>
              </div>
              <p class="text-white/30 text-[11px] mt-4 font-['Jost',sans-serif]">
                Left undecided for 24 hours, this pack is automatically kept.
              </p>
            </template>

            <template v-else>
              <p :class="currentState.decision === 'kept' ? 'text-[#DCC175]' : 'text-[#2dd4bf]'" class="font-['Jost',sans-serif] mb-6">
                <template v-if="currentState.decision === 'kept'">You kept this card — it's yours.</template>
                <template v-else>Sold back for {{ formatMoney(currentState.soldBackPence ?? currentState.buyBackPence ?? 0) }} — added to your wallet.</template>
              </p>
              <button type="button" @click="nextPack"
                class="inline-block px-8 py-3 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[4px]">
                {{ stack.length > 1 ? 'Open next pack' : 'Done' }}
              </button>
            </template>
          </div>
        </template>
      </template>

      <div v-else class="py-10">
        <p class="text-white text-2xl mb-2" style="font-family: 'Cinzel', serif; font-weight: 800;">All done!</p>
        <p class="text-white/50 text-sm mb-8 font-['Jost',sans-serif]">Every pack in this order has been opened and decided.</p>
        <Link href="/rips/my" class="inline-block px-8 py-3 border border-white/20 text-white text-xs tracking-[0.2em] uppercase rounded-[4px] hover:border-white/40 transition-colors">
          Back to my rips
        </Link>
      </div>
    </div>

    <ConfirmModal v-if="showConfirm === 'kept'" title="Keep this card?"
      message="It's yours to keep — we'll post it to your saved shipping address. This can't be undone."
      confirm-label="Keep it" :confirming="currentState?.deciding" @confirm="confirmDecide" @cancel="showConfirm = null" />
    <ConfirmModal v-if="showConfirm === 'sold_back'" title="Sell back this card?"
      :message="`Sell it back for ${formatMoney(currentState?.buyBackPence ?? 0)}, added to your wallet balance? This can't be undone.`"
      confirm-label="Sell back" :confirming="currentState?.deciding" @confirm="confirmDecide" @cancel="showConfirm = null" />
  </PopupShell>
</template>
