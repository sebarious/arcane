<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import { PackageSearch, FileClock, Coins, Wallet, ArrowRight } from 'lucide-vue-next';
import SellerLayout from '@/Layouts/SellerLayout.vue';

interface Store {
  id: number;
  name: string;
  slug: string;
  city: string;
  credit_balance_pence: number;
}

interface Batch {
  id: number;
  reference: string;
  store_id: number;
  type: string | null;
  pack_count: number;
  status: string;
  created_at?: string;
}

interface Progress {
  sold: number;
  total: number;
  percent: number;
}

interface Invoice {
  id: number;
  number: string;
  total_pence: number;
  amount_due_pence: number;
  status: string;
  issued_on: string | null;
  due_on: string | null;
}

interface Stats {
  active_batches: number;
  draft_requests: number;
  lifetime_packs: number;
  lifetime_revenue_pence: number;
  wallet_balance_pence: number;
  unpaid_invoices_count: number;
  unpaid_invoices_pence: number;
}

interface Props {
  stores: Store[];
  batches: Batch[];
  progress: Record<number, Progress>;
  stats: Stats;
  invoices: Invoice[];
}

const props = defineProps<Props>();

const formatMoney = (pence: number | null | undefined): string => {
  if (!pence) return '£0.00';
  return '£' + (pence / 100).toFixed(2);
};

const statusMeta = (status: string): { label: string; color: string } => {
  switch (status) {
    case 'draft': return { label: 'Requested', color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
    case 'committed': return { label: 'Live', color: 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' };
    case 'dispatched': return { label: 'Dispatched', color: 'text-[#3b82f6] bg-[rgba(59,130,246,0.1)]' };
    case 'completed': return { label: 'Completed', color: 'text-[#c9a84c] bg-[rgba(201,168,76,0.1)]' };
    case 'cancelled': return { label: 'Cancelled', color: 'text-red-400 bg-red-400/10' };
    case 'sent': return { label: 'Sent', color: 'text-[#3b82f6] bg-[rgba(59,130,246,0.1)]' };
    case 'paid': return { label: 'Paid', color: 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' };
    case 'overdue': return { label: 'Overdue', color: 'text-red-400 bg-red-400/10' };
    default: return { label: status, color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
  }
};

const storeName = (storeId: number) => props.stores.find(s => s.id === storeId)?.name ?? 'Store';
</script>

<template>
  <Head title="Seller Dashboard" />

  <SellerLayout title="Dashboard" :subtitle="`Welcome back — here's how ${stores[0]?.name ?? 'your store'} is doing.`">
    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5">
        <div class="flex items-center gap-2 text-[#7b4fe9] mb-2">
          <PackageSearch class="size-4" />
          <span class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]">Active batches</span>
        </div>
        <p class="font-['Cinzel',sans-serif] font-bold text-[28px] text-white">{{ stats.active_batches }}</p>
      </div>
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5">
        <div class="flex items-center gap-2 text-[#2dd4bf] mb-2">
          <FileClock class="size-4" />
          <span class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]">Requests pending</span>
        </div>
        <p class="font-['Cinzel',sans-serif] font-bold text-[28px] text-white">{{ stats.draft_requests }}</p>
      </div>
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5">
        <div class="flex items-center gap-2 text-[#c9a84c] mb-2">
          <Coins class="size-4" />
          <span class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]">Lifetime revenue</span>
        </div>
        <p class="font-['Cinzel',sans-serif] font-bold text-[28px] text-white">{{ formatMoney(stats.lifetime_revenue_pence) }}</p>
      </div>
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-5">
        <div class="flex items-center gap-2 text-[#DCC175] mb-2">
          <Wallet class="size-4" />
          <span class="font-['Jost',sans-serif] text-[11px] uppercase tracking-wide text-[rgba(255,255,255,0.35)]">Wallet balance</span>
        </div>
        <p class="font-['Cinzel',sans-serif] font-bold text-[28px] text-white">{{ formatMoney(stats.wallet_balance_pence) }}</p>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Recent batches -->
      <div class="lg:col-span-2 bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white">Recent batches</h2>
          <Link href="/seller/batches" class="flex items-center gap-1 text-xs text-[#a3a3a3] hover:text-[#c9a84c] font-['Jost',sans-serif]">
            View all <ArrowRight class="size-3" />
          </Link>
        </div>

        <div v-if="batches.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-6 text-center">
          No live batches yet. Once one is generated it'll show up here.
        </div>

        <div v-else class="flex flex-col gap-3">
          <Link v-for="batch in batches" :key="batch.id" :href="`/seller/batches/${batch.id}`"
            class="block bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] p-4 hover:border-[#c9a84c] transition-colors">
            <div class="flex items-center justify-between gap-4 mb-2">
              <div>
                <p class="font-['Jost',sans-serif] font-semibold text-sm text-white">{{ batch.reference }}</p>
                <p class="font-['Jost',sans-serif] text-xs text-[#a3a3a3]">
                  {{ storeName(batch.store_id) }} · {{ (batch.type ?? '').toUpperCase() }} · {{ batch.pack_count }} packs
                </p>
              </div>
              <span :class="['text-[10px] font-[\'Jost\',sans-serif] font-semibold uppercase px-2 py-1 rounded-[4px]', statusMeta(batch.status).color]">
                {{ statusMeta(batch.status).label }}
              </span>
            </div>
            <div class="w-full h-1.5 bg-[#0d0b14] rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-[#7b4fe9] to-[#c9a84c]"
                :style="{ width: `${progress[batch.id]?.percent ?? 0}%` }" />
            </div>
            <p class="font-['Jost',sans-serif] text-[11px] text-[#71717a] mt-1.5">
              {{ progress[batch.id]?.sold ?? 0 }} / {{ progress[batch.id]?.total ?? batch.pack_count }} packs sold
            </p>
          </Link>
        </div>
      </div>

      <!-- Recent invoices -->
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white">Invoices</h2>
          <Link href="/seller/invoices" class="flex items-center gap-1 text-xs text-[#a3a3a3] hover:text-[#c9a84c] font-['Jost',sans-serif]">
            View all <ArrowRight class="size-3" />
          </Link>
        </div>

        <div v-if="invoices.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-6 text-center">
          No invoices yet.
        </div>

        <div v-else class="flex flex-col gap-3">
          <div v-for="invoice in invoices" :key="invoice.id" class="flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="font-['Jost',sans-serif] font-medium text-sm text-white truncate">{{ invoice.number }}</p>
              <p class="font-['Jost',sans-serif] text-[11px] text-[#71717a]">Due {{ invoice.due_on }}</p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-['Jost',sans-serif] text-sm text-[#c9a84c] font-semibold">{{ formatMoney(invoice.amount_due_pence) }}</p>
              <span :class="['text-[9px] font-[\'Jost\',sans-serif] uppercase px-1.5 py-0.5 rounded', statusMeta(invoice.status).color]">
                {{ statusMeta(invoice.status).label }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="stats.unpaid_invoices_count > 0" class="mt-4 pt-4 border-t border-[rgba(220,193,117,0.08)]">
          <p class="font-['Jost',sans-serif] text-xs text-[#a3a3a3]">
            <span class="text-white font-semibold">{{ formatMoney(stats.unpaid_invoices_pence) }}</span>
            outstanding across {{ stats.unpaid_invoices_count }} invoice(s)
          </p>
        </div>
      </div>
    </div>
  </SellerLayout>
</template>
