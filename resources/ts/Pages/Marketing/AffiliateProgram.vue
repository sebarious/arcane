<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';

const props = defineProps<{ bonusPercentage: number }>();

const bonusLabel = computed( () => `${ Math.round( props.bonusPercentage * 100 ) }%` );

const steps = [
  {
    title: 'Find a store\'s code',
    body: 'Every store on Arcane has its own affiliate code, shown right on their public profile page. Ask your favourite store for theirs, or grab it yourself if you run a store.',
  },
  {
    title: 'Quote it on Sell to Us',
    body: `Head to the Sell to Us page, pop the code into the affiliate box at the top, and hit apply. We’ll confirm it's valid straight away.`,
  },
  {
    title: 'Get a better offer',
    body: `Every card you add gets a ${ bonusLabel.value } uplift on its offer automatically — you'll see the boosted price right alongside the original.`,
  },
];
</script>

<template>
  <Head title="Affiliate Program" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0">
      <div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full">
        <div class="h-[49px] relative shrink-0 w-full">
          <Nav />
        </div>
      </div>
    </div>

    <div class="px-8 lg:px-[64px] pt-[60px] pb-[40px] max-w-4xl mx-auto">
      <p class="font-['Cinzel',sans-serif] font-bold text-[40px] lg:text-[56px] text-white leading-tight">
        Affiliate <span class="text-[#c9a84c]">Program</span>
      </p>
      <p class="font-['Jost',sans-serif] font-normal text-[#a3a3a3] text-[18px] mt-[16px] max-w-2xl leading-relaxed">
        Every store on Arcane has its own shareable affiliate code. Quote it when you
        sell cards to us — whether you're one of their customers or the store itself —
        and get <span class="text-[#c9a84c] font-semibold">{{ bonusLabel }} more</span> on
        your offer, automatically.
      </p>
    </div>

    <div class="px-8 lg:px-[64px] pb-[60px] max-w-4xl mx-auto">
      <div class="grid sm:grid-cols-3 gap-[20px]">
        <div v-for=" (step, i) in steps " :key=" step.title "
          class="bg-[#13101e] border border-[rgba(201,168,76,0.2)] rounded-[12px] p-[24px] relative">
          <span class="font-['Cinzel',sans-serif] font-bold text-[#c9a84c]/40 text-[32px] leading-none">
            0{{ i + 1 }}
          </span>
          <p class="font-['Cinzel',sans-serif] font-bold text-[17px] text-white mt-[12px] mb-[8px]">
            {{ step.title }}
          </p>
          <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] leading-relaxed">
            {{ step.body }}
          </p>
        </div>
      </div>
    </div>

    <div class="px-8 lg:px-[64px] pb-[120px] max-w-4xl mx-auto">
      <div class="bg-[#1a1628] border border-[rgba(124,58,237,0.35)] rounded-[16px] p-[32px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-[20px]">
        <div>
          <p class="font-['Cinzel',sans-serif] font-bold text-[20px] text-white">
            Got a code? Put it to work.
          </p>
          <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] mt-[6px]">
            Or browse our stores to find one first.
          </p>
        </div>
        <div class="flex gap-[12px] shrink-0">
          <Link href="/stores"
            class="px-6 py-3 rounded-[4px] border border-[#3d2f6e] text-white text-sm font-['Jost',sans-serif] font-semibold uppercase tracking-wide hover:border-[#c9a84c] transition-colors">
            Find a store
          </Link>
          <Link href="/sell"
            class="px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14]"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            Sell to us
          </Link>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>
