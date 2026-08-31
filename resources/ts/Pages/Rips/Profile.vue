<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import RipAccountLayout from '@/Layouts/RipAccountLayout.vue';

interface Shipping {
  shipping_name: string | null;
  shipping_address_line_1: string | null;
  shipping_address_line_2: string | null;
  shipping_city: string | null;
  shipping_postcode: string | null;
  shipping_country: string;
}

const props = defineProps<{ name: string; email: string; shipping: Shipping }>();

const form = useForm({
  shipping_name: props.shipping.shipping_name ?? props.name,
  shipping_address_line_1: props.shipping.shipping_address_line_1 ?? '',
  shipping_address_line_2: props.shipping.shipping_address_line_2 ?? '',
  shipping_city: props.shipping.shipping_city ?? '',
  shipping_postcode: props.shipping.shipping_postcode ?? '',
  shipping_country: props.shipping.shipping_country ?? 'GB',
});

function submit() {
  form.post('/rips/profile', { preserveScroll: true });
}
</script>

<template>
  <Head title="Profile" />

  <RipAccountLayout title="Profile" subtitle="Your shipping address — required before buying a pack, so a card you keep can be posted out.">
    <div class="max-w-lg">
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6 mb-6">
        <p class="text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1">Account</p>
        <p class="text-white text-sm font-['Jost',sans-serif]">{{ name }} · {{ email }}</p>
      </div>

      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.15)] rounded-[12px] p-6">
        <h2 class="font-['Cinzel',sans-serif] font-bold text-lg text-white mb-4">Shipping address</h2>

        <form @submit.prevent="submit" class="flex flex-col gap-4">
          <div>
            <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Full name</label>
            <input v-model="form.shipping_name" type="text" required
              class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
            <p v-if="form.errors.shipping_name" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ form.errors.shipping_name }}</p>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Address line 1</label>
            <input v-model="form.shipping_address_line_1" type="text" required
              class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
            <p v-if="form.errors.shipping_address_line_1" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ form.errors.shipping_address_line_1 }}</p>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Address line 2 (optional)</label>
            <input v-model="form.shipping_address_line_2" type="text"
              class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">City</label>
              <input v-model="form.shipping_city" type="text" required
                class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
              <p v-if="form.errors.shipping_city" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ form.errors.shipping_city }}</p>
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Postcode</label>
              <input v-model="form.shipping_postcode" type="text" required
                class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
              <p v-if="form.errors.shipping_postcode" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ form.errors.shipping_postcode }}</p>
            </div>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wide text-white/40 font-['Jost',sans-serif] mb-1.5">Country</label>
            <select v-model="form.shipping_country"
              class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-2.5 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50">
              <option value="GB">United Kingdom</option>
              <option value="IE">Ireland</option>
              <option value="US">United States</option>
              <option value="FR">France</option>
              <option value="DE">Germany</option>
            </select>
          </div>

          <button type="submit" :disabled="form.processing"
            class="self-start px-8 py-3 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[4px] disabled:opacity-40 transition-opacity">
            {{ form.processing ? 'Saving…' : form.recentlySuccessful ? 'Saved' : 'Save address' }}
          </button>
        </form>
      </div>
    </div>
  </RipAccountLayout>
</template>
