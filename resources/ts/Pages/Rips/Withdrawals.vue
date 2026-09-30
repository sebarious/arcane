<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import RipAccountLayout from '@/Layouts/RipAccountLayout.vue';
import { toast } from '@/toast';

interface Withdrawal {
  id: number;
  amount_pence: number;
  fee_pence: number;
  status: 'pending' | 'paid' | 'rejected';
  created_at: string;
  paid_at: string | null;
}

const props = defineProps<{
  balance_pence: number;
  has_bank_details: boolean;
  minimum_withdrawal_pence: number;
  withdrawal_fee_rate: number;
  bank_account_name: string | null;
  bank_sort_code: string | null;
  bank_account_number: string | null;
  withdrawals: Withdrawal[];
}>();

const formatMoney = (pence: number) => '£' + (pence / 100).toFixed(2);
const formatDate = (iso: string) => new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

const hasBankDetails = ref(props.has_bank_details);
const bankForm = ref({
  bank_account_name: props.bank_account_name ?? '',
  bank_sort_code: props.bank_sort_code ?? '',
  bank_account_number: props.bank_account_number ?? '',
});
const savingBank = ref(false);
const bankErrors = ref<Record<string, string[]>>({});
const bankSaved = ref(false);

async function saveBankDetails() {
  savingBank.value = true;
  bankErrors.value = {};
  bankSaved.value = false;

  try {
    await axios.post('/rips/wallet/bank-details', bankForm.value);
    hasBankDetails.value = true;
    bankSaved.value = true;
    toast('Bank details saved.');
  } catch (e: any) {
    bankErrors.value = e?.response?.data?.errors ?? {};
  } finally {
    savingBank.value = false;
  }
}

const canWithdraw = computed(() => hasBankDetails.value && props.balance_pence >= props.minimum_withdrawal_pence);

// The most a customer can actually request and receive — the balance has to
// cover the payout AND the fee on top of it, so it's balance / (1 + rate),
// not the raw balance.
const maxAmountPounds = computed(() => (props.balance_pence / (1 + props.withdrawal_fee_rate) / 100).toFixed(2));

const amountPounds = ref(maxAmountPounds.value);
const requesting = ref(false);
const requestError = ref('');
const requestedList = ref<Withdrawal[]>(props.withdrawals);

const feePreviewPence = computed(() => {
  const amountPence = Math.round((parseFloat(amountPounds.value) || 0) * 100);

  return Math.round(amountPence * props.withdrawal_fee_rate);
});
const totalDebitPreviewPence = computed(() => Math.round((parseFloat(amountPounds.value) || 0) * 100) + feePreviewPence.value);
const feePercentLabel = computed(() => {
  const pct = props.withdrawal_fee_rate * 100;

  return Number.isInteger(pct) ? String(pct) : pct.toFixed(1);
});

async function requestWithdrawal() {
  requesting.value = true;
  requestError.value = '';

  const amountPence = Math.round(parseFloat(amountPounds.value) * 100);

  try {
    const { data } = await axios.post('/rips/wallet/withdrawals', { amount_pence: amountPence });
    requestedList.value = [
      {
        id: data.data.id, amount_pence: data.data.amount_pence, fee_pence: data.data.fee_pence,
        status: data.data.status, created_at: new Date().toISOString(), paid_at: null,
      },
      ...requestedList.value,
    ];
    toast(`Withdrawal requested — ${formatMoney(data.data.amount_pence + data.data.fee_pence)} will be deducted from your balance.`);
  } catch (e: any) {
    requestError.value = e?.response?.data?.message ?? 'Couldn\'t submit that request.';
  } finally {
    requesting.value = false;
  }
}

function statusMeta(status: string) {
  switch (status) {
    case 'paid': return { label: 'Paid', color: 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' };
    case 'rejected': return { label: 'Rejected', color: 'text-red-400 bg-red-500/10' };
    default: return { label: 'Pending', color: 'text-[#DCC175] bg-[rgba(220,193,117,0.1)]' };
  }
}
</script>

<template>
  <Head title="Withdraw funds" />

  <RipAccountLayout title="Withdraw funds" subtitle="Withdrawals go to your bank account — allow up to 5 working days.">
    <div class="max-w-lg flex flex-col gap-6">
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
        <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white mb-4">Bank details</h2>
        <form @submit.prevent="saveBankDetails" class="flex flex-col gap-4">
          <div>
            <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Account name</label>
            <input v-model="bankForm.bank_account_name" type="text" required
              class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
            <p v-if="bankErrors.bank_account_name" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ bankErrors.bank_account_name[0] }}</p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Sort code</label>
              <input v-model="bankForm.bank_sort_code" type="text" required placeholder="00-00-00"
                class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Account number</label>
              <input v-model="bankForm.bank_account_number" type="text" required
                class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
            </div>
          </div>
          <button type="submit" :disabled="savingBank"
            class="self-start px-6 py-2.5 border border-[#DCC175]/50 text-[#DCC175] text-xs tracking-[0.15em] uppercase font-bold rounded-[4px] hover:bg-[#DCC175]/10 transition-colors disabled:opacity-40">
            {{ savingBank ? 'Saving…' : bankSaved ? 'Saved' : 'Save bank details' }}
          </button>
        </form>
      </div>

      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
        <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white mb-1">Request a withdrawal</h2>
        <p class="text-white/40 text-xs font-['Jost',sans-serif] mb-4">
          Balance: {{ formatMoney(balance_pence) }} · Minimum {{ formatMoney(minimum_withdrawal_pence) }}
        </p>

        <template v-if="!canWithdraw">
          <p class="text-white/50 text-sm font-['Jost',sans-serif]">
            {{ !hasBankDetails ? 'Add your bank details above first.' : `You need at least ${formatMoney(minimum_withdrawal_pence)} to withdraw.` }}
          </p>
        </template>
        <template v-else>
          <form @submit.prevent="requestWithdrawal" class="flex items-end gap-4">
            <div class="flex-1">
              <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Amount you'll receive (£)</label>
              <input v-model="amountPounds" type="number" step="0.01" min="0.01" :max="maxAmountPounds" required
                class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
            </div>
            <button type="submit" :disabled="requesting"
              class="px-6 py-2.5 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.15em] uppercase font-bold rounded-[4px] disabled:opacity-40 transition-opacity">
              {{ requesting ? 'Requesting…' : 'Request' }}
            </button>
          </form>

          <!-- The disclaimer the user explicitly asked for — a £100 request
          takes £103 from the balance, not £97 out of the £100 itself. -->
          <p class="text-[#DCC175]/80 text-xs mt-3 font-['Jost',sans-serif]">
            A {{ feePercentLabel }}% fee applies on top — {{ formatMoney(totalDebitPreviewPence) }} will be deducted from
            your balance to pay out {{ formatMoney(Math.round((parseFloat(amountPounds) || 0) * 100)) }}
            (fee: {{ formatMoney(feePreviewPence) }}).
          </p>
        </template>
        <p v-if="requestError" class="text-red-400 text-sm mt-3 font-['Jost',sans-serif]">{{ requestError }}</p>
      </div>

      <div v-if="requestedList.length > 0" class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden">
        <div class="px-6 py-4 border-b border-[rgba(220,193,117,0.08)]">
          <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white">Withdrawal history</h2>
        </div>
        <div class="divide-y divide-[rgba(220,193,117,0.06)]">
          <div v-for="w in requestedList" :key="w.id" class="flex items-center justify-between px-6 py-3">
            <div>
              <p class="text-white text-sm font-['Jost',sans-serif]">{{ formatMoney(w.amount_pence) }}</p>
              <p class="text-white/40 text-xs font-['Jost',sans-serif]">
                {{ formatDate(w.created_at) }} · {{ formatMoney(w.amount_pence + w.fee_pence) }} deducted (incl. {{ formatMoney(w.fee_pence) }} fee)
              </p>
            </div>
            <span :class="['text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]', statusMeta(w.status).color]">
              {{ statusMeta(w.status).label }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </RipAccountLayout>
</template>
