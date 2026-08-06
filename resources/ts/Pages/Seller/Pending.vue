<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

const props = defineProps<{
  affiliateCode: string | null;
  bonusPercentage: number;
}>();

const bonusLabel = computed( () => `${ Math.round( props.bonusPercentage * 100 ) }%` );

const affiliateCopied = ref( false );
const copyAffiliateCode = () => {
  if ( ! props.affiliateCode ) return;
  navigator.clipboard.writeText( props.affiliateCode );
  affiliateCopied.value = true;
  setTimeout( () => { affiliateCopied.value = false; }, 2000 );
};
</script>

<template>
  <Head title="Store setup in progress" />

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
        class="content-stretch flex flex-col gap-[40px] items-center justify-center pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center relative shrink-0 text-center mx-auto max-w-[560px]">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Almost</span>
            <span class="leading-[normal] text-[#c9a84c]"> there</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]">
            Your dashboard unlocks as soon as our team finishes setting up your store.
            If you haven't already, check your email for the onboarding form we sent —
            completing it helps us get you live faster. Questions in the meantime?
            Reach us at <a href="mailto:support@arcanepacks.com" class="text-[#c9a84c] underline">support@arcanepacks.com</a>.
          </p>
        </div>

        <div v-if="affiliateCode" class="mx-auto max-w-[560px] w-full flex flex-col items-center gap-[12px]">
          <div
            class="bg-[#13101e] border border-[rgba(124,58,237,0.35)] rounded-[10px] p-[16px] flex items-center gap-[16px] w-full">
            <div class="flex-1 min-w-0">
              <p class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide">
                Your affiliate code
              </p>
              <p class="font-['Cinzel',sans-serif] font-bold text-[20px] text-[#c9a84c] tracking-wide">
                {{ affiliateCode }}
              </p>
            </div>
            <button type="button" @click="copyAffiliateCode"
              class="shrink-0 text-xs font-['Jost',sans-serif] font-semibold uppercase tracking-wide px-4 py-2 rounded-[4px] border border-[#3d2f6e] text-white hover:border-[#c9a84c] transition-colors">
              {{ affiliateCopied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
          <p class="font-['Jost',sans-serif] font-normal leading-relaxed text-[#a3a3a3] text-[14px] text-center">
            You don't have to wait to start using it. Share this code with your customers —
            when they quote it on our <span class="text-white">Sell to Us</span> flow, they get
            <span class="text-[#c9a84c] font-semibold">{{ bonusLabel }} more</span> on their offer,
            and your store earns that same {{ bonusLabel }} back as store credit — automatically
            applied to your invoice(s) once you're live.
          </p>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>
