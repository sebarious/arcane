<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

interface Item {
  card_name: string;
  set_name: string | null;
  card_number: string | null;
  quantity: number;
  total_offer_pence: number;
  total_offer: string;
}

interface Props {
  reference: string;
  shippingAddress: string;
  estimatedTotal: string;
  items: Item[];
}

const props = defineProps<Props>();
</script>

<template>
  <Head title="Thank You" />

  <main class="bg-[#0d0b14] overflow-x-hidden">
    <div class="relative shrink-0">
      <div
        class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full">
        <div class="h-[49px] relative shrink-0">
          <Nav />
        </div>
      </div>
    </div>

    <div class="relative shrink-0 w-full">
      <div
        class="content-stretch flex flex-col gap-[40px] items-center pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center relative shrink-0 text-center mx-auto">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Thank</span>
            <span class="leading-[normal] text-[#c9a84c]"> you</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px] max-w-xl">
            We've got your submission. Your reference is <strong class="text-white">{{ reference }}</strong>.
            Your indicative offer is <strong class="text-[#c9a84c]">{{ estimatedTotal }}</strong> — we'll
            confirm the final price once your cards arrive and have been checked.</p>
        </div>

        <div v-if=" items.length "
          class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-[32px] relative shrink-0 w-full max-w-xl">
          <p class="font-['Jost',sans-serif] font-semibold text-[13px] text-[rgba(255,255,255,0.35)] uppercase mb-[16px]">
            What you submitted</p>
          <div class="flex flex-col gap-[10px]">
            <div v-for=" item in items " :key=" item.card_name + item.card_number "
              class="flex items-center justify-between gap-[12px]">
              <div class="min-w-0">
                <p class="font-['Jost',sans-serif] text-[14px] text-white truncate">
                  {{ item.quantity }}× {{ item.card_name }}</p>
                <p class="font-['Jost',sans-serif] text-[12px] text-[#a3a3a3] truncate">
                  {{ item.set_name }} · {{ item.card_number }}</p>
              </div>
              <p class="font-['Jost',sans-serif] text-[14px] text-[#c9a84c] shrink-0">{{ item.total_offer }}</p>
            </div>
          </div>
        </div>

        <div
          class="bg-[#1a1628] border border-[#c9a84c]/40 rounded-[16px] p-[32px] relative shrink-0 w-full max-w-xl">
          <p class="font-['Jost',sans-serif] font-semibold text-[16px] text-white mb-[8px]">
            Send your cards to:</p>
          <p class="font-['Jost',sans-serif] font-normal leading-relaxed text-[16px] text-[#c9a84c] whitespace-pre-line">
            {{ shippingAddress }}</p>
          <p class="font-['Jost',sans-serif] font-normal leading-relaxed text-[14px] text-[#a3a3a3] mt-[16px]">
            Please package your cards securely (sleeved and rigid card / toploaders recommended) and include
            your reference <strong class="text-white">{{ reference }}</strong> in the parcel. We recommend
            using a tracked and insured delivery service. Once your cards arrive we'll check condition,
            confirm the final price, and arrange fast, secure payment.</p>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>
