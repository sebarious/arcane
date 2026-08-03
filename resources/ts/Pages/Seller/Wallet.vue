<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

interface Store {
  id: number;
  name: string;
  credit_balance_pence: number;
}

interface Transaction {
  id: number;
  created_at: string;
  type: 'credit' | 'redemption' | 'adjustment';
  amount_pence: number;
  balance_after_pence: number;
  reason: string | null;
  store_name: string | null;
  invoice_number: string | null;
  submission_reference: string | null;
}

interface Paginated<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
  stores: Store[];
  totalBalance: number;
  transactions: Paginated<Transaction>;
}

defineProps<Props>();

const formatMoney = (pence: number): string => '£' + (pence / 100).toFixed(2);

const formatDate = (iso: string): string =>
  new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const typeMeta = (type: string): { label: string; color: string } => {
  switch (type) {
    case 'credit': return { label: 'Credit', color: 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' };
    case 'redemption': return { label: 'Applied to invoice', color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
    default: return { label: 'Adjustment', color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
  }
};
</script>

<template>
  <Head title="Wallet" />

  <SellerLayout title="Wallet" subtitle="Credit earned from appraised affiliate sell submissions — automatically applied to your next invoice(s).">
    <div class="grid sm:grid-cols-3 gap-4 mb-8">
      <div class="bg-gradient-to-br from-[rgba(201,168,76,0.15)] to-[rgba(201,168,76,0.03)] border border-[rgba(201,168,76,0.3)] rounded-[12px] p-6 sm:col-span-1">
        <p class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[#c9a84c]/80 mb-2">Total balance</p>
        <p class="font-['Cinzel',sans-serif] font-bold text-3xl text-[#c9a84c]">{{ formatMoney(totalBalance) }}</p>
      </div>

      <div v-if="stores.length > 1" class="sm:col-span-2 grid grid-cols-2 gap-4">
        <div v-for="store in stores" :key="store.id" class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5">
          <p class="font-['Jost',sans-serif] text-xs text-[#a3a3a3] mb-1 truncate">{{ store.name }}</p>
          <p class="font-['Cinzel',sans-serif] font-bold text-xl text-white">{{ formatMoney(store.credit_balance_pence) }}</p>
        </div>
      </div>
    </div>

    <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden">
      <div class="px-6 py-4 border-b border-[rgba(220,193,117,0.08)]">
        <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white">Credit ledger</h2>
      </div>

      <div v-if="transactions.data.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center">
        No credit activity yet.
      </div>

      <table v-else class="min-w-full text-sm">
        <thead class="text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-['Jost',sans-serif] text-xs uppercase tracking-wide">
          <tr class="text-left">
            <th class="py-3 px-5">Date</th>
            <th class="py-3 px-5" v-if="stores.length > 1">Store</th>
            <th class="py-3 px-5">Type</th>
            <th class="py-3 px-5">Reason</th>
            <th class="py-3 px-5 text-right">Amount</th>
            <th class="py-3 px-5 text-right">Balance after</th>
          </tr>
        </thead>
        <tbody class="font-['Jost',sans-serif]">
          <tr v-for="tx in transactions.data" :key="tx.id" class="border-b border-[rgba(220,193,117,0.06)] last:border-0">
            <td class="py-3 px-5 text-[#a3a3a3] whitespace-nowrap">{{ formatDate(tx.created_at) }}</td>
            <td class="py-3 px-5 text-[#a3a3a3]" v-if="stores.length > 1">{{ tx.store_name }}</td>
            <td class="py-3 px-5">
              <span :class="['text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]', typeMeta(tx.type).color]">
                {{ typeMeta(tx.type).label }}
              </span>
            </td>
            <td class="py-3 px-5 text-[#d8d3e0] max-w-xs">
              {{ tx.reason ?? '—' }}
              <span v-if="tx.invoice_number" class="text-[#71717a]">· {{ tx.invoice_number }}</span>
              <span v-if="tx.submission_reference" class="text-[#71717a]">· {{ tx.submission_reference }}</span>
            </td>
            <td :class="['py-3 px-5 text-right font-semibold', tx.amount_pence > 0 ? 'text-[#2dd4bf]' : 'text-red-400']">
              {{ tx.amount_pence > 0 ? '+' : '-' }}{{ formatMoney(Math.abs(tx.amount_pence)) }}
            </td>
            <td class="py-3 px-5 text-right text-white">{{ formatMoney(tx.balance_after_pence) }}</td>
          </tr>
        </tbody>
      </table>

      <div v-if="transactions.links.length > 3" class="flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]">
        <template v-for="link in transactions.links" :key="link.label">
          <Link v-if="link?.url" :href="link.url" preserve-state preserve-scroll
            class="px-2.5 py-1 rounded border font-['Jost',sans-serif]"
            :class="link.active ? 'bg-[#c9a84c] text-[#0d0b14] border-[#c9a84c]' : 'text-[#a3a3a3] border-[#3d2f6e] hover:border-[#c9a84c]'"
            v-html="link.label" />
        </template>
      </div>
    </div>
  </SellerLayout>
</template>
