<script setup lang="ts">
import { computed, ref, onBeforeUnmount } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { loadStripe, type Stripe, type StripeElements } from '@stripe/stripe-js';
import Nav from '@/Components/Layout/Nav.vue';
import { gradedNotice, type GradedPolicy } from '@/gradedPolicy';
import Footer from '@/Components/Layout/Footer.vue';
import Orbs from '@/Components/Layout/Orbs.vue';
import AuthModal from '@/Components/Rips/AuthModal.vue';
import ConfirmModal from '@/Components/Rips/ConfirmModal.vue';
import packFallback from '@/Assets/Arcane_pack.webp';

interface Pack {
  id: number;
  slug: string;
  name: string;
  description: string | null;
  image_path: string | null;
  price_pence: number;
  games: string[];
  band_odds: Record<string, number>;
  buy_back_percentage: number;
  graded_policy: GradedPolicy;
  in_stock: boolean;
}

const props = defineProps<{ pack: Pack; stripeKey: string; walletBalancePence: number | null; hasShippingAddress: boolean | null }>();

const graded = computed(() => gradedNotice(props.pack.graded_policy));

const page = usePage();
const isLoggedIn = computed(() => !!(page.props.auth as any)?.user);

const formatMoney = (pence: number) => '£' + (pence / 100).toFixed(2);
const formatPct = (n: number) => (n * 100).toFixed(n < 0.01 ? 2 : 1) + '%';

const BANDS = ['mythic', 'legendary', 'super', 'rare', 'common'] as const;
const BAND_LABELS: Record<string, string> = {
  mythic: 'Mythic', legendary: 'Legendary', super: 'Super', rare: 'Rare', common: 'Common',
};

const quantity = ref(1);
const showAuthModal = ref(false);

type Stage = 'idle' | 'starting' | 'payment' | 'confirming' | 'error';
const stage = ref<Stage>('idle');
const errorMessage = ref('');

const stripe = ref<Stripe | null>(null);
const elements = ref<StripeElements | null>(null);
const paymentElementMount = ref<HTMLElement | null>(null);
const orderReference = ref<string | null>(null);

const totalPence = computed(() => props.pack.price_pence * quantity.value);
const canPayFromWallet = computed(() => (props.walletBalancePence ?? 0) >= totalPence.value);

// Which button was clicked, so onAuthenticated() (after a logged-out visitor
// creates an account / logs in via the modal) resumes the right one rather
// than always assuming card.
const pendingAction = ref<'card' | 'wallet' | null>(null);
const walletProcessing = ref(false);

// Once logged in, a missing shipping address blocks the buy buttons entirely
// (see the template) — this is just a second guard against the handlers
// somehow firing anyway (e.g. a stale reference held from before reload).
const needsShippingAddress = computed(() => isLoggedIn.value && props.hasShippingAddress === false);

// Shown before either purchase path actually runs — confirms what's about
// to be charged and how, rather than firing on the first click.
const showConfirm = ref<'card' | 'wallet' | null>(null);

function beginPurchase() {
  pendingAction.value = 'card';

  if (!isLoggedIn.value) {
    showAuthModal.value = true;
    return;
  }
  if (needsShippingAddress.value) return;
  showConfirm.value = 'card';
}

function beginWalletPurchase() {
  pendingAction.value = 'wallet';

  if (!isLoggedIn.value) {
    showAuthModal.value = true;
    return;
  }
  if (needsShippingAddress.value) return;
  showConfirm.value = 'wallet';
}

function confirmPurchase() {
  showConfirm.value = null;

  if (pendingAction.value === 'wallet') {
    payFromWallet();
  } else {
    startCheckout();
  }
}

function onAuthenticated() {
  showAuthModal.value = false;
  router.reload({
    only: ['auth', 'walletBalancePence', 'hasShippingAddress'],
    onSuccess: () => {
      // Freshly logged in and missing a shipping address — the banner
      // below now shows instead; don't attempt checkout, it would just
      // fail server-side.
      if (needsShippingAddress.value) return;

      showConfirm.value = pendingAction.value;
    },
  });
}

async function payFromWallet() {
  walletProcessing.value = true;
  errorMessage.value = '';

  try {
    const { data } = await axios.post('/rips/checkout/wallet', {
      items: [{ rip_pack_id: props.pack.id, quantity: quantity.value }],
    });

    const ripIds: number[] = data.data.rip_ids;
    goToOpenedPack(data.data.order_reference, ripIds);
  } catch (e: any) {
    walletProcessing.value = false;
    errorMessage.value = e?.response?.data?.message ?? 'Something went wrong paying from your wallet.';
  }
}

// A single pack goes straight to its own immersive open page; more than one
// lands on the multi-pack order page instead, where each can be opened and
// decided on in turn.
function goToOpenedPack(reference: string, ripIds: number[]) {
  if (ripIds.length > 1) {
    router.visit(`/rips/orders/${reference}`);
  } else {
    router.visit(`/rips/my/${ripIds[0]}`);
  }
}

async function startCheckout() {
  stage.value = 'starting';
  errorMessage.value = '';

  try {
    const { data } = await axios.post('/rips/checkout', {
      items: [{ rip_pack_id: props.pack.id, quantity: quantity.value }],
    });

    orderReference.value = data.data.order_reference;

    stripe.value = await loadStripe(props.stripeKey);
    if (!stripe.value) {
      throw new Error('Payment couldn\'t load — please refresh and try again.');
    }

    elements.value = stripe.value.elements({ clientSecret: data.data.client_secret });
    stage.value = 'payment';

    // Payment Element needs its container in the DOM first — mount() below
    // runs on the next tick via a watcher-free requestAnimationFrame since
    // v-if just made #payment-element exist.
    requestAnimationFrame(() => {
      const el = elements.value!.create('payment');
      el.mount('#rip-payment-element');
    });
  } catch (e: any) {
    stage.value = 'error';
    errorMessage.value = e?.response?.data?.message ?? e?.message ?? 'Something went wrong starting checkout.';
  }
}

async function confirmPayment() {
  if (!stripe.value || !elements.value) return;

  stage.value = 'confirming';
  errorMessage.value = '';

  const { error } = await stripe.value.confirmPayment({
    elements: elements.value,
    redirect: 'if_required',
  });

  if (error) {
    stage.value = 'payment';
    errorMessage.value = error.message ?? 'Payment failed — please try again.';
    return;
  }

  await pollForCompletion();
}

async function pollForCompletion(attempt = 0) {
  if (!orderReference.value) return;

  const { data } = await axios.get(`/rips/checkout/${orderReference.value}/status`);

  if (data.data.status === 'paid') {
    const ripIds: number[] = data.data.rip_ids;
    goToOpenedPack(orderReference.value, ripIds);
    return;
  }

  if (attempt > 15) {
    stage.value = 'error';
    errorMessage.value = 'Payment is taking longer than expected — check "My rips" in a moment.';
    return;
  }

  setTimeout(() => pollForCompletion(attempt + 1), 1000);
}

onBeforeUnmount(() => {
  elements.value = null;
});
</script>

<template>
  <Head :title="pack.name" />

  <main class="bg-[#06060b] min-h-screen flex flex-col relative overflow-x-hidden">
    <Orbs />

    <div class="relative shrink-0 px-8 lg:px-16 py-5 mb-12">
      <Nav />
    </div>

    <section class="relative px-8 lg:px-16 py-10 flex-1 mb-12">
      <div class="max-w-5xl mx-auto grid lg:grid-cols-2 gap-12">
        <!-- self-start: the grid would otherwise stretch this column to match the
             taller buy column, stranding the pack in a tall empty panel. -->
        <div class="relative lg:self-start flex items-center justify-center bg-[#13101e] border border-[rgba(220,193,117,0.12)] rounded-[16px] p-10 overflow-hidden min-h-[440px]">
          <div class="absolute inset-0 pointer-events-none" :style="{
            background: 'radial-gradient(ellipse at 50% 50%, rgba(124,58,237,0.24) 0%, rgba(220,193,117,0.10) 44%, transparent 72%)',
          }" />
          <!-- max-w-full matters as much as max-h: without it a wide upload
               ran straight out of the padded panel. -->
          <img :src="pack.image_path ?? packFallback" :alt="pack.name"
            class="relative max-h-[440px] max-w-full object-contain drop-shadow-[0_24px_70px_rgba(0,0,0,0.8)]" />
        </div>

        <div>
          <h1 class="text-4xl text-white mb-3" style="font-family: 'Cinzel', serif; font-weight: 800;">{{ pack.name }}</h1>
          <p v-if="pack.description" class="text-white/60 text-base leading-relaxed mb-6" style="font-family: 'Jost', sans-serif;">
            {{ pack.description }}
          </p>

          <div class="flex flex-wrap gap-1.5 mb-6">
            <span v-for="game in pack.games" :key="game"
              class="text-[10px] tracking-[0.15em] uppercase font-bold px-2.5 py-1 rounded bg-[rgba(124,58,237,0.12)] text-[#a78bfa] border border-[rgba(124,58,237,0.25)]">
              {{ game }}
            </span>
            <span v-if="graded" class="text-[10px] tracking-[0.15em] uppercase font-bold px-2.5 py-1 rounded"
              :style="{ color: graded.accent, background: graded.accent + '1f', border: `1px solid ${graded.accent}40` }">
              {{ graded.badge }}
            </span>
          </div>

          <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5 mb-6">
            <h2 class="text-xs tracking-[0.2em] uppercase text-white/40 mb-3" style="font-family: 'Jost', sans-serif;">Rarity odds</h2>
            <div class="flex flex-col gap-2">
              <div v-for="band in BANDS" :key="band" class="flex items-center justify-between text-sm" style="font-family: 'Jost', sans-serif;">
                <span class="text-white/70">{{ BAND_LABELS[band] }}</span>
                <span class="text-[#DCC175] font-semibold">{{ formatPct(pack.band_odds[band] ?? 0) }}</span>
              </div>
            </div>
            <p v-if="graded" class="text-xs mt-3" :style="{ color: graded.accent, fontFamily: 'Jost, sans-serif' }">
              {{ graded.detail }}
            </p>
            <p class="text-white/40 text-xs mt-3" style="font-family: 'Jost', sans-serif;">
              Sell back for <b class="text-[#2dd4bf]">{{ formatPct(pack.buy_back_percentage) }}</b> of market value any time after opening.
            </p>
          </div>

          <template v-if="stage === 'idle' || stage === 'starting' || stage === 'error'">
            <div class="flex items-center gap-4 mb-5">
              <span class="text-3xl text-white" style="font-family: 'Cinzel', serif; font-weight: 800;">{{ formatMoney(pack.price_pence * quantity) }}</span>
              <div class="flex items-center border border-[rgba(220,193,117,0.2)] rounded-[6px] overflow-hidden">
                <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="px-3 py-2 text-white/60 hover:text-white">−</button>
                <span class="px-4 text-white font-['Jost',sans-serif]">{{ quantity }}</span>
                <button type="button" @click="quantity = Math.min(10, quantity + 1)" class="px-3 py-2 text-white/60 hover:text-white">+</button>
              </div>
            </div>

            <p v-if="!pack.in_stock" class="text-red-400 text-sm mb-4 font-['Jost',sans-serif]">
              This pack can't be fulfilled from live stock right now — please check back later.
            </p>
            <p v-if="errorMessage" class="text-red-400 text-sm mb-4 font-['Jost',sans-serif]">{{ errorMessage }}</p>

            <div v-if="isLoggedIn && walletBalancePence !== null" class="flex items-center justify-between text-xs mb-3 font-['Jost',sans-serif]">
              <span class="text-white/40">Wallet balance</span>
              <span :class="canPayFromWallet ? 'text-[#2dd4bf]' : 'text-white/40'">{{ formatMoney(walletBalancePence) }}</span>
            </div>

            <div v-if="needsShippingAddress" class="bg-[rgba(220,193,117,0.08)] border border-[rgba(220,193,117,0.3)] rounded-[8px] p-4 mb-4">
              <p class="text-[#DCC175] text-sm font-['Jost',sans-serif] mb-2">
                Add your shipping address before buying a pack — it's how we know where to post a card you decide to keep.
              </p>
              <Link href="/rips/profile" class="inline-block text-xs tracking-[0.15em] uppercase font-bold text-[#DCC175] underline hover:no-underline">
                Add shipping address
              </Link>
            </div>

            <div v-else class="flex flex-col sm:flex-row gap-3">
              <button type="button" @click="beginPurchase" :disabled="!pack.in_stock || stage === 'starting' || walletProcessing"
                class="flex-1 py-4 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.3em] uppercase font-bold rounded-[4px] disabled:opacity-40 transition-opacity">
                {{ stage === 'starting' ? 'Starting checkout…' : 'Buy with card' }}
              </button>
              <button v-if="!isLoggedIn || canPayFromWallet" type="button" @click="beginWalletPurchase"
                :disabled="!pack.in_stock || walletProcessing || stage === 'starting'"
                class="flex-1 py-4 border border-[#DCC175]/50 text-[#DCC175] text-xs tracking-[0.3em] uppercase font-bold rounded-[4px] hover:bg-[#DCC175]/10 transition-colors disabled:opacity-40">
                {{ walletProcessing ? 'Paying…' : 'Pay with wallet' }}
              </button>
            </div>

            <p class="text-white/30 text-[11px] mt-3 font-['Jost',sans-serif] leading-relaxed">
              Once opened, decide within 24 hours — a pack left undecided is automatically kept and sent to your set shipping address.
            </p>
          </template>

          <template v-else>
            <div class="bg-[#13101e] border border-[rgba(220,193,117,0.15)] rounded-[12px] p-6">
              <h2 class="text-lg text-white mb-4" style="font-family: 'Cinzel', serif; font-weight: 700;">Payment</h2>
              <div id="rip-payment-element" ref="paymentElementMount" class="mb-5" />
              <p v-if="errorMessage" class="text-red-400 text-sm mb-4 font-['Jost',sans-serif]">{{ errorMessage }}</p>
              <button type="button" @click="confirmPayment" :disabled="stage === 'confirming'"
                class="w-full py-4 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.3em] uppercase font-bold rounded-[4px] disabled:opacity-40 transition-opacity">
                {{ stage === 'confirming' ? 'Confirming…' : `Pay ${formatMoney(pack.price_pence * quantity)}` }}
              </button>
            </div>
          </template>

          <p class="text-white/30 text-xs mt-6 font-['Jost',sans-serif]">
            Every draw is provably fair — a cryptographic hash is committed the moment you pay, before the card is ever
            chosen. <Link :href="`/rips`" class="underline hover:text-white/60">Learn more</Link>
          </p>

          <div class="mt-8 pt-6 border-t border-[rgba(220,193,117,0.1)]">
            <p class="text-[10px] tracking-[0.3em] uppercase text-[#DCC175]/70 mb-2" style="font-family: 'Jost', sans-serif;">
              18+ Only
            </p>
            <p class="text-white/40 text-xs leading-relaxed" style="font-family: 'Jost', sans-serif;">
              Digital Rips is only available to customers aged 18 or over. Every pack always contains a genuine, real
              trading card of real monetary value — there is no outcome where you pay for a pack and receive nothing.
              Please treat this as a collectibles purchase, not a way to make money, and only spend what you can
              comfortably afford. See our <Link href="/terms" class="underline hover:text-white/60">Terms &amp;
              Conditions</Link> for full details.
            </p>
          </div>
        </div>
      </div>
    </section>

    <Footer />

    <AuthModal v-if="showAuthModal" @close="showAuthModal = false" @authenticated="onAuthenticated" />

    <ConfirmModal v-if="showConfirm === 'card'" title="Confirm purchase"
      :message="`Buy ${pack.name} for ${formatMoney(totalPence)} with card?`"
      confirm-label="Buy now" @confirm="confirmPurchase" @cancel="showConfirm = null" />
    <ConfirmModal v-if="showConfirm === 'wallet'" title="Confirm purchase"
      :message="`Pay ${formatMoney(totalPence)} from your wallet balance for ${pack.name}?`"
      confirm-label="Pay now" @confirm="confirmPurchase" @cancel="showConfirm = null" />
  </main>
</template>
