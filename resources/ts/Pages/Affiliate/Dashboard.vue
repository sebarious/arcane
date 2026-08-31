<script setup lang="ts">
import { computed } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import AffiliateLayout from '@/Layouts/AffiliateLayout.vue';

interface Transaction {
  id: number;
  created_at: string;
  type: 'credit' | 'redemption';
  amount_pence: number;
  balance_after_pence: number;
  reason: string | null;
  submission_reference: string | null;
}

interface Paginated<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
  status: 'pending' | 'active' | 'suspended' | null;
  affiliateCode: string | null;
  balancePence: number;
  minimumWithdrawalBalancePence: number;
  transactions: Paginated<Transaction> | null;
}

const props = defineProps<Props>();

const formatMoney = (pence: number): string => '£' + (pence / 100).toFixed(2);

const formatDate = (iso: string): string =>
  new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const typeMeta = (type: string): { label: string; color: string } => {
  return type === 'credit'
    ? { label: 'Credit', color: 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' }
    : { label: 'Withdrawal', color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
};

const canWithdraw = computed(() => props.balancePence > props.minimumWithdrawalBalancePence);
</script>

<template>
  <Head title="Affiliate Dashboard" />

  <!-- Pending / suspended: no sidebar chrome, matches Seller/Onboarding.vue's holding state -->
  <main v-if="status !== 'active'" class="bg-[#0d0b14] overflow-x-hidden min-h-screen flex items-center justify-center px-8">
    <div class="max-w-[560px] mx-auto text-center">
      <p class="font-['Cinzel',sans-serif] font-bold text-[40px] text-white">
        <template v-if="status === 'suspended'">
          Account <span class="text-red-400">suspended</span>
        </template>
        <template v-else>
          Almost <span class="text-[#c9a84c]">there</span>
        </template>
      </p>
      <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[18px] mt-4 leading-relaxed">
        <template v-if="status === 'suspended'">
          Your affiliate account has been suspended. If you think this is a mistake, get in touch at
          <a href="mailto:support@arcanepacks.com" class="text-[#c9a84c] underline">support@arcanepacks.com</a>.
        </template>
        <template v-else>
          Your account is pending approval — we'll email you as soon as it's ready. Your affiliate code will
          start working once you're approved.
        </template>
      </p>
      <Link href="/logout" method="get" as="button"
        class="inline-block mt-8 px-6 py-3 rounded-[4px] border border-[#3d2f6e] text-white text-sm font-['Jost',sans-serif] font-semibold uppercase tracking-wide hover:border-[#c9a84c] transition-colors">
        Log out
      </Link>
    </div>
  </main>

  <AffiliateLayout v-else title="Dashboard" subtitle="Your affiliate wallet and activity.">
    <div class="grid sm:grid-cols-2 gap-4 mb-8">
      <div class="bg-gradient-to-br from-[rgba(201,168,76,0.15)] to-[rgba(201,168,76,0.03)] border border-[rgba(201,168,76,0.3)] rounded-[12px] p-6">
        <p class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[#c9a84c]/80 mb-2">Wallet balance</p>
        <p class="font-['Cinzel',sans-serif] font-bold text-3xl text-[#c9a84c]">{{ formatMoney(balancePence) }}</p>
        <p class="font-['Jost',sans-serif] text-xs text-[#a3a3a3] mt-3">
          <template v-if="canWithdraw">
            You can request a withdrawal any time.
          </template>
          <template v-else>
            You can only request a withdrawal once your balance exceeds {{ formatMoney(minimumWithdrawalBalancePence) }}.
          </template>
        </p>
      </div>

      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6 flex flex-col justify-between">
        <div>
          <p class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Withdrawals</p>
          <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3]">
            Request a payout of any amount up to your balance — takes up to 5 working days to process.
          </p>
        </div>
        <Link href="/affiliate/withdrawals"
          class="mt-4 inline-block text-center px-4 py-2.5 rounded-[6px] border border-[#3d2f6e] text-white text-sm font-['Jost',sans-serif] font-semibold uppercase tracking-wide hover:border-[#c9a84c] transition-colors">
          {{ canWithdraw ? 'Request a withdrawal' : 'View withdrawals' }}
        </Link>
      </div>
    </div>

    <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden">
      <div class="px-6 py-4 border-b border-[rgba(220,193,117,0.08)]">
        <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white">Ledger</h2>
      </div>

      <div v-if="!transactions || transactions.data.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center">
        No activity yet.
      </div>

      <table v-else class="min-w-full text-sm">
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
              <span :class="['text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]', typeMeta(tx.type).color]">
                {{ typeMeta(tx.type).label }}
              </span>
            </td>
            <td class="py-3 px-5 text-[#d8d3e0] max-w-xs">
              {{ tx.reason ?? '—' }}
              <span v-if="tx.submission_reference" class="text-[#71717a]">· {{ tx.submission_reference }}</span>
            </td>
            <td :class="['py-3 px-5 text-right font-semibold', tx.amount_pence > 0 ? 'text-[#2dd4bf]' : 'text-red-400']">
              {{ tx.amount_pence > 0 ? '+' : '-' }}{{ formatMoney(Math.abs(tx.amount_pence)) }}
            </td>
            <td class="py-3 px-5 text-right text-white">{{ formatMoney(tx.balance_after_pence) }}</td>
          </tr>
        </tbody>
      </table>

      <div v-if="transactions && transactions.links.length > 3" class="flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]">
        <template v-for="link in transactions.links" :key="link.label">
          <Link v-if="link?.url" :href="link.url" preserve-state preserve-scroll
            class="px-2.5 py-1 rounded border font-['Jost',sans-serif]"
            :class="link.active ? 'bg-[#c9a84c] text-[#0d0b14] border-[#c9a84c]' : 'text-[#a3a3a3] border-[#3d2f6e] hover:border-[#c9a84c]'"
            v-html="link.label" />
        </template>
      </div>
    </div>
  </AffiliateLayout>
</template>
