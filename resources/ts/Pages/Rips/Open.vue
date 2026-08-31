<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import PopupShell from '@/Components/Rips/PopupShell.vue';
import TearOpenAnimation from '@/Components/Rips/TearOpenAnimation.vue';
import ConfirmModal from '@/Components/Rips/ConfirmModal.vue';
import { toast } from '@/toast';
import { RARITY_LABELS, RARITY_COLORS } from '@/rarity';
import type { Card } from '../../types';

interface RipCard extends Card {
  market_value_pence: number;
}

interface Rip {
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

const props = defineProps<{ rip: Rip }>();

const formatMoney = (pence: number) => '£' + (pence / 100).toFixed(2);

const animationRef = ref<InstanceType<typeof TearOpenAnimation> | null>(null);
const card = ref<RipCard | null>(props.rip.card);
const buyBackPence = ref<number | null>(
  props.rip.card ? Math.round(props.rip.card.market_value_pence * props.rip.buy_back_percentage) : null,
);
const revealed = ref(props.rip.opened);
const opening = ref(false);
const decision = ref(props.rip.decision);
const deciding = ref(false);
const errorMessage = ref('');

async function handleTap() {
  if (opening.value) return;
  opening.value = true;

  try {
    const { data } = await axios.post(`/rips/my/${props.rip.id}/open`);
    card.value = data.data.card;
    buyBackPence.value = data.data.buy_back_pence;

    await nextTick();
    animationRef.value?.play();
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message ?? 'Couldn\'t open this pack — please try again.';
    opening.value = false;
  }
}

function onRevealed() {
  revealed.value = true;
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
  deciding.value = true;
  errorMessage.value = '';

  try {
    const { data } = await axios.post(`/rips/my/${props.rip.id}/decide`, { decision: choice });
    decision.value = data.data.decision;
    toast(choice === 'kept' ? 'Kept — it\'s yours.' : `Sold back for ${formatMoney(data.data.sold_back_pence ?? 0)}.`);
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message ?? 'Something went wrong — please try again.';
  } finally {
    deciding.value = false;
  }
}
</script>

<template>
  <Head :title="rip.pack_name" />

  <PopupShell close-href="/rips/my">
    <div class="text-center py-6">
      <div v-if="!rip.ready" class="max-w-md mx-auto py-16">
        <p class="text-white/60 font-['Jost',sans-serif]">
          Your payment is still being confirmed — this page will update automatically in a moment. Refresh if it takes longer than a minute.
        </p>
      </div>

      <template v-else>
        <TearOpenAnimation ref="animationRef" :card="card" :initially-revealed="rip.opened" @tap="handleTap" @revealed="onRevealed" />

        <p v-if="errorMessage" class="text-red-400 text-sm mt-6 font-['Jost',sans-serif]">{{ errorMessage }}</p>

        <div v-if="revealed && card" class="mt-8">
          <h2 class="text-2xl text-white mb-1" style="font-family: 'Cinzel', serif; font-weight: 800;">{{ card.name }}</h2>
          <p class="text-white/50 text-sm mb-1 font-['Jost',sans-serif]">{{ card.set }} · {{ card.number }}</p>
          <p class="text-xs font-bold tracking-[0.15em] uppercase mb-6 font-['Jost',sans-serif]" :style="{ color: RARITY_COLORS[card.band] }">
            {{ RARITY_LABELS[card.band] ?? card.band }}
          </p>

          <template v-if="!decision">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <button type="button" @click="askDecide('kept')" :disabled="deciding"
                class="flex-1 sm:flex-none px-8 py-4 border border-[#DCC175]/50 text-[#DCC175] text-xs tracking-[0.2em] uppercase font-bold rounded-[4px] hover:bg-[#DCC175]/10 transition-colors disabled:opacity-40">
                Keep it
              </button>
              <button type="button" @click="askDecide('sold_back')" :disabled="deciding"
                class="flex-1 sm:flex-none px-8 py-4 bg-gradient-to-r from-[#2dd4bf] to-[#5eead4] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[4px] transition-opacity disabled:opacity-40">
                Sell back for {{ formatMoney(buyBackPence ?? 0) }}
              </button>
            </div>
            <p class="text-white/30 text-[11px] mt-4 font-['Jost',sans-serif]">
              Left undecided for 24 hours, this pack is automatically kept.
            </p>
          </template>

          <template v-else-if="decision === 'kept'">
            <p class="text-[#DCC175] font-['Jost',sans-serif] mb-6">You kept this card — it's yours.</p>
            <Link href="/rips/my" class="inline-block px-8 py-3 border border-white/20 text-white text-xs tracking-[0.2em] uppercase rounded-[4px] hover:border-white/40 transition-colors">
              Back to my rips
            </Link>
          </template>

          <template v-else>
            <p class="text-[#2dd4bf] font-['Jost',sans-serif] mb-6">
              Sold back for {{ formatMoney(rip.sold_back_pence ?? buyBackPence ?? 0) }} — added to your wallet.
            </p>
            <Link href="/rips/wallet" class="inline-block px-8 py-3 bg-gradient-to-r from-[#2dd4bf] to-[#5eead4] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[4px]">
              View wallet
            </Link>
          </template>
        </div>
      </template>
    </div>

    <ConfirmModal v-if="showConfirm === 'kept'" title="Keep this card?"
      message="It's yours to keep — we'll post it to your saved shipping address. This can't be undone."
      confirm-label="Keep it" :confirming="deciding" @confirm="confirmDecide" @cancel="showConfirm = null" />
    <ConfirmModal v-if="showConfirm === 'sold_back'" title="Sell back this card?"
      :message="`Sell it back for ${formatMoney(buyBackPence ?? 0)}, added to your wallet balance? This can't be undone.`"
      confirm-label="Sell back" :confirming="deciding" @confirm="confirmDecide" @cancel="showConfirm = null" />
  </PopupShell>
</template>
