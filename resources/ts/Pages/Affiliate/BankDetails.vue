<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AffiliateLayout from '@/Layouts/AffiliateLayout.vue';

interface Props {
  bankAccountName: string | null;
  bankSortCode: string | null;
  bankAccountNumber: string | null;
}

const props = defineProps<Props>();

const form = useForm({
  bank_account_name: props.bankAccountName ?? '',
  bank_sort_code: props.bankSortCode ?? '',
  bank_account_number: props.bankAccountNumber ?? '',
});

function submit() {
  form.post('/affiliate/bank-details', { preserveScroll: true });
}
</script>

<template>
  <Head title="Bank Details" />

  <AffiliateLayout title="Bank details" subtitle="Used for withdrawal payouts — keep these up to date.">
    <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6 max-w-[520px]">
      <div class="mb-5">
        <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">
          Name on account</label>
        <input v-model="form.bank_account_name" type="text" placeholder="As it appears on your bank statement"
          class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
        <p v-if="form.errors.bank_account_name" class="text-xs text-red-400 mt-1">{{ form.errors.bank_account_name }}</p>
      </div>

      <div class="mb-5">
        <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">
          Sort code</label>
        <input v-model="form.bank_sort_code" type="text" placeholder="12-34-56"
          class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
        <p v-if="form.errors.bank_sort_code" class="text-xs text-red-400 mt-1">{{ form.errors.bank_sort_code }}</p>
      </div>

      <div class="mb-6">
        <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">
          Account number</label>
        <input v-model="form.bank_account_number" type="text" placeholder="12345678"
          class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
        <p v-if="form.errors.bank_account_number" class="text-xs text-red-400 mt-1">{{ form.errors.bank_account_number }}</p>
      </div>

      <button type="button" @click="submit" :disabled="form.processing"
        class="px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14] disabled:opacity-50"
        style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
        {{ form.processing ? 'Saving…' : 'Save details' }}
      </button>
    </div>
  </AffiliateLayout>
</template>
