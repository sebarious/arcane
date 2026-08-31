<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { loadStripe, type Stripe, type StripeElements } from '@stripe/stripe-js';
import RipAccountLayout from '@/Layouts/RipAccountLayout.vue';
import { toast } from '@/toast';

interface Transaction {
  id: number;
  created_at: string;
  type: 'credit' | 'redemption';
  amount_pence: number;
  balance_after_pence: number;
  reason: string | null;
  pack_name: string | null;
}

interface Paginated<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
  stripeKey: string;
  balance_pence: number;
  has_bank_details: boolean;
  minimum_withdrawal_pence: number;
  transactions: Paginated<Transaction>;
}>();

const formatMoney = (pence: number) => '£' + (pence / 100).toFixed(2);
const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

// --- Add funds -------------------------------------------------------------

const showTopup = ref(false);
const amountPounds = ref('10.00');

type Stage = 'idle' | 'starting' | 'payment' | 'confirming' | 'error';
const stage = ref<Stage>('idle');
const errorMessage = ref('');

const stripe = ref<Stripe | null>(null);
const elements = ref<StripeElements | null>(null);
const topupId = ref<number | null>(null);

function openTopup() {
  showTopup.value = true;
  stage.value = 'idle';
  errorMessage.value = '';
}

async function startTopup() {
  const amountPence = Math.round(parseFloat(amountPounds.value) * 100);

  if (!amountPence || amountPence < 100) {
    errorMessage.value = 'The minimum top-up is £1.';
    return;
  }

  stage.value = 'starting';
  errorMessage.value = '';

  try {
    const { data } = await axios.post('/rips/wallet/topup', { amount_pence: amountPence });
    topupId.value = data.data.id;

    stripe.value = await loadStripe(props.stripeKey);
    if (!stripe.value) {
      throw new Error('Payment couldn\'t load — please refresh and try again.');
    }

    elements.value = stripe.value.elements({ clientSecret: data.data.client_secret });
    stage.value = 'payment';

    requestAnimationFrame(() => {
      const el = elements.value!.create('payment');
      el.mount('#topup-payment-element');
    });
  } catch (e: any) {
    stage.value = 'error';
    errorMessage.value = e?.response?.data?.message ?? e?.message ?? 'Something went wrong starting that.';
  }
}

async function confirmTopup() {
  if (!stripe.value || !elements.value) return;

  stage.value = 'confirming';
  errorMessage.value = '';

  const { error } = await stripe.value.confirmPayment({ elements: elements.value, redirect: 'if_required' });

  if (error) {
    stage.value = 'payment';
    errorMessage.value = error.message ?? 'Payment failed — please try again.';
    return;
  }

  await pollTopup();
}

async function pollTopup(attempt = 0) {
  if (!topupId.value) return;

  const { data } = await axios.get(`/rips/wallet/topup/${topupId.value}/status`);

  if (data.data.status === 'paid') {
    // Close the panel and reset it back to idle immediately — router.reload()
    // refreshes this page's *props* (balance, ledger) but does NOT remount
    // the component, so leaving stage/showTopup untouched here was the bug:
    // the "Confirming…" panel just sat there forever even though the top-up
    // had actually gone through server-side.
    showTopup.value = false;
    stage.value = 'idle';
    topupId.value = null;

    // Fresh balance + ledger from the server — simplest way to reflect the
    // credit everywhere on this page without hand-patching local state.
    router.reload({ onSuccess: () => toast('Wallet topped up.') });
    return;
  }

  if (attempt > 15) {
    stage.value = 'error';
    errorMessage.value = 'That\'s taking longer than expected — refresh in a moment to check your balance.';
    return;
  }

  setTimeout(() => pollTopup(attempt + 1), 1000);
}
</script>

<template>
  <Head title="Wallet" />

  <RipAccountLayout title="Wallet" subtitle="Add funds, or cash out from cards you've sold back to us.">
    <div class="grid sm:grid-cols-3 gap-4 mb-8">
      <div class="bg-gradient-to-br from-[rgba(201,168,76,0.15)] to-[rgba(201,168,76,0.03)] border border-[rgba(201,168,76,0.3)] rounded-[12px] p-6 sm:col-span-1">
        <p class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[#c9a84c]/80 mb-2">Balance</p>
        <p class="font-['Cinzel',sans-serif] font-bold text-3xl text-[#c9a84c]">{{ formatMoney(balance_pence) }}</p>
      </div>

      <button type="button" @click="openTopup"
        class="flex flex-col items-center justify-center bg-[#13101e] border border-[rgba(220,193,117,0.15)] rounded-[12px] p-6 hover:border-[rgba(220,193,117,0.4)] transition-colors text-center">
        <span class="font-['Jost',sans-serif] text-xs uppercase tracking-wide text-white/60">Add funds</span>
      </button>

      <Link href="/rips/wallet/withdrawals"
        class="flex flex-col items-center justify-center bg-[#13101e] border border-[rgba(220,193,117,0.15)] rounded-[12px] p-6 hover:border-[rgba(220,193,117,0.4)] transition-colors text-center">
        <span class="font-['Jost',sans-serif] text-xs uppercase tracking-wide text-white/60">
          {{ balance_pence >= minimum_withdrawal_pence ? 'Withdraw funds' : `Withdraw from ${formatMoney(minimum_withdrawal_pence)}` }}
        </span>
      </Link>
    </div>

    <div v-if="showTopup" class="bg-[#13101e] border border-[rgba(220,193,117,0.15)] rounded-[12px] p-6 mb-8 max-w-md">
      <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white mb-4">Add funds</h2>

      <template v-if="stage === 'idle' || stage === 'starting' || stage === 'error'">
        <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Amount (£)</label>
        <input v-model="amountPounds" type="number" step="0.01" min="1"
          class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] mb-4 focus:outline-none focus:border-[#DCC175]/50" />
        <p v-if="errorMessage" class="text-red-400 text-sm mb-4 font-['Jost',sans-serif]">{{ errorMessage }}</p>
        <button type="button" @click="startTopup" :disabled="stage === 'starting'"
          class="w-full py-3 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[4px] disabled:opacity-40 transition-opacity">
          {{ stage === 'starting' ? 'Starting…' : 'Continue to payment' }}
        </button>
      </template>

      <template v-else>
        <div id="topup-payment-element" class="mb-5" />
        <p v-if="errorMessage" class="text-red-400 text-sm mb-4 font-['Jost',sans-serif]">{{ errorMessage }}</p>
        <button type="button" @click="confirmTopup" :disabled="stage === 'confirming'"
          class="w-full py-3 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[4px] disabled:opacity-40 transition-opacity">
          {{ stage === 'confirming' ? 'Confirming…' : `Pay ${formatMoney(Math.round(parseFloat(amountPounds || '0') * 100))}` }}
        </button>
      </template>
    </div>

    <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden">
      <div class="px-6 py-4 border-b border-[rgba(220,193,117,0.08)]">
        <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white">Ledger</h2>
      </div>

      <div v-if="transactions.data.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center">
        No wallet activity yet.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-['Jost',sans-serif] text-xs uppercase tracking-wide">
            <tr class="text-left">
              <th class="py-3 px-5">Date</th>
              <th class="py-3 px-5">Type</th>
              <th class="py-3 px-5">Reason</th>
              <th class="py-3 px-5 text-right">Amount</th>
              <th class="py-3 px-5 text-right">Balance after</th>
            </tr>
          </thead>
          <tbody class="font-['Jost',sans-serif]">
            <tr v-for="tx in transactions.data" :key="tx.id" class="border-b border-[rgba(220,193,117,0.06)] last:border-0">
              <td class="py-3 px-5 text-[#a3a3a3] whitespace-nowrap">{{ formatDate(tx.created_at) }}</td>
              <td class="py-3 px-5">
                <span :class="['text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]',
                  tx.type === 'credit' ? 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' : 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]']">
                  {{ tx.type === 'credit' ? 'Credit' : 'Debit' }}
                </span>
              </td>
              <td class="py-3 px-5 text-[#d8d3e0] max-w-xs">
                {{ tx.reason ?? '—' }}
              </td>
              <td :class="['py-3 px-5 text-right whitespace-nowrap', tx.amount_pence >= 0 ? 'text-[#2dd4bf]' : 'text-white/60']">
                {{ tx.amount_pence >= 0 ? '+' : '' }}{{ formatMoney(tx.amount_pence) }}
              </td>
              <td class="py-3 px-5 text-right text-white/50 whitespace-nowrap">{{ formatMoney(tx.balance_after_pence) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="transactions.links.length > 3" class="flex flex-wrap gap-1 px-6 py-4 border-t border-[rgba(220,193,117,0.08)]">
        <Link v-for="(link, i) in transactions.links" :key="i" :href="link.url ?? ''"
          v-html="link.label"
          :class="['px-3 py-1.5 rounded-[4px] text-xs font-[\'Jost\',sans-serif]',
            link.active ? 'bg-[rgba(220,193,117,0.15)] text-[#DCC175]' : 'text-white/40 hover:text-white',
            !link.url ? 'pointer-events-none opacity-30' : '']" />
      </div>
    </div>
  </RipAccountLayout>
</template>
