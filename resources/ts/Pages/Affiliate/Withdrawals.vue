<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AffiliateLayout from '@/Layouts/AffiliateLayout.vue';

interface Withdrawal {
  id: number;
  amount_pence: number;
  status: 'pending' | 'paid' | 'rejected';
  requested_at: string;
  paid_at: string | null;
}

interface Props {
  balancePence: number;
  minimumWithdrawalBalancePence: number;
  hasBankDetails: boolean;
  withdrawals: Withdrawal[];
}

const props = defineProps<Props>();

const formatMoney = (pence: number): string => '£' + (pence / 100).toFixed(2);

const canWithdraw = computed(() => props.balancePence > props.minimumWithdrawalBalancePence);

const form = useForm({ amount: '' });

function submit() {
  form.post('/affiliate/withdrawals', {
    preserveScroll: true,
    onSuccess: () => { form.reset(); },
  });
}

const statusMeta = (status: string): { label: string; color: string } => {
  switch (status) {
    case 'paid': return { label: 'Paid', color: 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' };
    case 'rejected': return { label: 'Rejected', color: 'text-red-400 bg-[rgba(239,68,68,0.1)]' };
    default: return { label: 'Pending', color: 'text-[#c9a84c] bg-[rgba(201,168,76,0.1)]' };
  }
};
</script>

<template>
  <Head title="Withdrawals" />

  <AffiliateLayout title="Withdrawals" subtitle="Request a payout and track its status.">
    <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6 max-w-[520px] mb-8">
      <p class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Wallet balance</p>
      <p class="font-['Cinzel',sans-serif] font-bold text-2xl text-[#c9a84c] mb-4">{{ formatMoney(balancePence) }}</p>

      <p v-if="!hasBankDetails" class="font-['Jost',sans-serif] text-sm text-[#a3a3a3]">
        Add your <a href="/affiliate/bank-details" class="text-[#c9a84c] underline">bank details</a> before requesting a withdrawal.
      </p>
      <p v-else-if="!canWithdraw" class="font-['Jost',sans-serif] text-sm text-[#a3a3a3]">
        You can only request a withdrawal once your balance exceeds {{ formatMoney(minimumWithdrawalBalancePence) }}.
      </p>
      <template v-else>
        <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">
          Amount to withdraw (£)</label>
        <input v-model="form.amount" type="number" step="0.01" min="0.01" :max="balancePence / 100"
          class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
        <p v-if="form.errors.amount" class="text-xs text-red-400 mt-1">{{ form.errors.amount }}</p>
        <p class="font-['Jost',sans-serif] text-xs text-[#71717a] mt-2">
          Withdrawals take up to 5 working days to process.
        </p>
        <button type="button" @click="submit" :disabled="form.processing"
          class="mt-4 px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14] disabled:opacity-50"
          style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
          {{ form.processing ? 'Requesting…' : 'Request withdrawal' }}
        </button>
      </template>
    </div>

    <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden">
      <div class="px-6 py-4 border-b border-[rgba(220,193,117,0.08)]">
        <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white">Your withdrawals</h2>
      </div>

      <div v-if="withdrawals.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center">
        No withdrawals requested yet.
      </div>

      <table v-else class="min-w-full text-sm">
        <thead class="text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-['Jost',sans-serif] text-xs uppercase tracking-wide">
          <tr class="text-left">
            <th class="py-3 px-5">Requested</th>
            <th class="py-3 px-5">Status</th>
            <th class="py-3 px-5">Paid</th>
            <th class="py-3 px-5 text-right">Amount</th>
          </tr>
        </thead>
        <tbody class="font-['Jost',sans-serif]">
          <tr v-for="w in withdrawals" :key="w.id" class="border-b border-[rgba(220,193,117,0.06)] last:border-0">
            <td class="py-3 px-5 text-[#a3a3a3] whitespace-nowrap">{{ w.requested_at }}</td>
            <td class="py-3 px-5">
              <span :class="['text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]', statusMeta(w.status).color]">
                {{ statusMeta(w.status).label }}
              </span>
            </td>
            <td class="py-3 px-5 text-[#a3a3a3]">{{ w.paid_at ?? '—' }}</td>
            <td class="py-3 px-5 text-right text-white font-semibold">{{ formatMoney(w.amount_pence) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AffiliateLayout>
</template>
