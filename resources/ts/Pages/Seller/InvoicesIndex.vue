<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import SellerLayout from '@/Layouts/SellerLayout.vue';

interface Invoice {
  id: number;
  number: string;
  batch_reference: string | null;
  total_pence: number;
  credit_applied_pence: number;
  amount_due_pence: number;
  status: string;
  issued_on: string | null;
  due_on: string | null;
}

interface Paginated<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
  invoices: Paginated<Invoice>;
}

defineProps<Props>();

const formatMoney = (pence: number | null | undefined): string => {
  if (!pence) return '£0.00';
  return '£' + (pence / 100).toFixed(2);
};

const statusMeta = (status: string): { label: string; color: string } => {
  switch (status) {
    case 'draft': return { label: 'Draft', color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
    case 'sent': return { label: 'Sent', color: 'text-[#3b82f6] bg-[rgba(59,130,246,0.1)]' };
    case 'paid': return { label: 'Paid', color: 'text-[#2dd4bf] bg-[rgba(45,212,191,0.1)]' };
    case 'overdue': return { label: 'Overdue', color: 'text-red-400 bg-red-400/10' };
    case 'cancelled': return { label: 'Cancelled', color: 'text-[#71717a] bg-[rgba(163,163,163,0.1)]' };
    default: return { label: status, color: 'text-[#a3a3a3] bg-[rgba(163,163,163,0.1)]' };
  }
};
</script>

<template>
  <Head title="Invoices" />

  <SellerLayout title="Invoices" subtitle="Every invoice raised for your store(s) — download a PDF copy any time.">
    <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] overflow-hidden">
      <div v-if="invoices.data.length === 0" class="text-[#a3a3a3] text-sm font-['Jost',sans-serif] py-12 text-center">
        No invoices yet.
      </div>

      <table v-else class="min-w-full text-sm">
        <thead class="text-[rgba(255,255,255,0.35)] border-b border-[rgba(220,193,117,0.08)] font-['Jost',sans-serif] text-xs uppercase tracking-wide">
          <tr class="text-left">
            <th class="py-3 px-5">Number</th>
            <th class="py-3 px-5">Batch</th>
            <th class="py-3 px-5">Issued</th>
            <th class="py-3 px-5">Due</th>
            <th class="py-3 px-5 text-right">Credit applied</th>
            <th class="py-3 px-5 text-right">Amount due</th>
            <th class="py-3 px-5">Status</th>
            <th class="py-3 px-5"></th>
          </tr>
        </thead>
        <tbody class="font-['Jost',sans-serif]">
          <tr v-for="invoice in invoices.data" :key="invoice.id" class="border-b border-[rgba(220,193,117,0.06)] last:border-0">
            <td class="py-3 px-5 text-white font-medium">{{ invoice.number }}</td>
            <td class="py-3 px-5 text-[#a3a3a3]">{{ invoice.batch_reference ?? '—' }}</td>
            <td class="py-3 px-5 text-[#a3a3a3]">{{ invoice.issued_on }}</td>
            <td class="py-3 px-5 text-[#a3a3a3]">{{ invoice.due_on }}</td>
            <td class="py-3 px-5 text-right text-[#2dd4bf]">
              {{ invoice.credit_applied_pence > 0 ? formatMoney(invoice.credit_applied_pence) : '—' }}
            </td>
            <td class="py-3 px-5 text-right text-[#c9a84c] font-semibold">{{ formatMoney(invoice.amount_due_pence) }}</td>
            <td class="py-3 px-5">
              <span :class="['text-[10px] font-semibold uppercase px-2 py-1 rounded-[4px]', statusMeta(invoice.status).color]">
                {{ statusMeta(invoice.status).label }}
              </span>
            </td>
            <td class="py-3 px-5 text-right">
              <a :href="`/admin/invoices/${invoice.id}/pdf`" target="_blank"
                class="inline-flex items-center gap-1 text-[#c9a84c] hover:underline text-xs font-semibold">
                <Download class="size-3.5" /> PDF
              </a>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="invoices.links.length > 3" class="flex justify-end gap-1 text-xs p-4 border-t border-[rgba(220,193,117,0.08)]">
        <template v-for="link in invoices.links" :key="link.label">
          <Link v-if="link?.url" :href="link.url" preserve-state preserve-scroll
            class="px-2.5 py-1 rounded border font-['Jost',sans-serif]"
            :class="link.active ? 'bg-[#c9a84c] text-[#0d0b14] border-[#c9a84c]' : 'text-[#a3a3a3] border-[#3d2f6e] hover:border-[#c9a84c]'"
            v-html="link.label" />
        </template>
      </div>
    </div>
  </SellerLayout>
</template>
