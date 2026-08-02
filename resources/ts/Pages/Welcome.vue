<template>

  <Head title="Live Pull Rates, Real Cards, Zero Guesswork" />

  <div class="bg-background text-foreground min-h-screen relative overflow-x-hidden">
    <!-- film grain overlay -->
    <div class="fixed inset-0 pointer-events-none z-10 opacity-[0.028]" :style="filmGrainStyle" />
    <Orbs />
    <Nav />

    <main class="relative z-20">
      <Hero :total-available-cards="totalAvailableCards" />
      <Ticker />
      <PullsSlider :pulls="recentPulls" />
      <HowItWorks />
      <LivePool :pulls="whatsInThePool" />
      <section class="px-8 lg:px-16 py-[12px] lg:py-[42px]">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

          <!-- Left: title + blurb -->
          <div>
            <h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none"
              :style="{ fontFamily: 'Cinzel, serif', fontWeight: 700 }">
              Why choose <HoloText>Arcane</HoloText>
            </h2>
            <p class="text-base leading-relaxed max-w-md text-white/70">
              Every pack we seal is built on proof, not promises. Authenticated singles,
              a live card pool you can check before you buy, and real-time pricing mean
              you always know exactly what you're paying for — and exactly what's left
              to pull.
            </p>
          </div>

          <!-- Right: 2x2 grid of small boxes, same style as How It Works -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <!-- Box 01 -->
            <div class="p-6 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group rounded">
              <div
                class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500">
              </div>
              <div class="flex items-start justify-between mb-4">
                <span class="text-[10px] text-[#DCC175] tracking-[0.3em]">01</span>
              </div>
              <h3 class="text-xl text-white mb-3 tracking-tight"
                :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
                Batch Merge
              </h3>
              <p class="text-sm text-white/70 leading-relaxed"
                :style="{ fontFamily: 'Jost, sans-serif', fontWeight: 300 }">
                As chase cards are pulled, your batch naturally changes. With Batch Merge, you can combine your
                remaining cards into a brand-new batch, restoring balanced pull rates and giving your inventory a fresh
                opportunity to sell.<br /><br />More momentum. Better visibility. Longer-lasting batches.
              </p>
            </div>

            <!-- Box 02 -->
            <div class="p-6 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group rounded">
              <div
                class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500">
              </div>
              <div class="flex items-start justify-between mb-4">
                <span class="text-[10px] text-[#DCC175] tracking-[0.3em]">02</span>
              </div>
              <h3 class="text-xl text-white mb-3 tracking-tight"
                :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
                Buy-Back Promise
              </h3>
              <p class="text-sm text-white/70 leading-relaxed"
                :style="{ fontFamily: 'Jost, sans-serif', fontWeight: 300 }">
                We believe selling through Arcane should be as flexible as it is transparent.

                If your batch isn’t selling, you decide to take a break, or your circumstances change, we’ll buy back
                your remaining eligible cards for 80% of their live market value.

                It’s our commitment to reducing risk while giving you the confidence to sell on your terms.

                Less risk. More flexibility. Complete peace of mind.
              </p>
            </div>

            <!-- Box 03 -->
            <div
              class="p-6 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group rounded col-span-1 sm:col-span-2">
              <div
                class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500">
              </div>
              <div class="flex items-start justify-between mb-4">
                <span class="text-[10px] text-[#DCC175] tracking-[0.3em]">03</span>
              </div>
              <h3 class="text-xl text-white mb-3 tracking-tight"
                :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
                No Subscription
              </h3>
              <p class="text-sm text-white/70 leading-relaxed"
                :style="{ fontFamily: 'Jost, sans-serif', fontWeight: 300 }">
                Sell on Your Terms.

                Get started without monthly subscriptions, hidden fees or long-term commitments. Create batches when you
                want, pause whenever you like and grow at your own pace.

                <br /><br />Arcane gives you the flexibility to sell your way.
              </p>
            </div>

          </div>
        </div>
      </section>
      <Tiers />
      <CTA />
      <Footer />
    </main>

    <SellCardsPopup />
  </div>
</template>

<script setup lang="ts">
import Orbs from '../Components/Layout/Orbs.vue';
import Nav from '../Components/Layout/Nav.vue';
import Hero from '../Components/Homepage/Hero.vue';
import Ticker from '../Components/Homepage/Ticker.vue';
import PullsSlider from '../Components/PullsSlider.vue';
import HowItWorks from '../Components/Homepage/HowItWorks.vue';
import LivePool from '../Components/LivePool.vue';
import CTA from '../Components/Homepage/CTA.vue';
import Footer from '../Components/Layout/Footer.vue';
import HoloText from '../Components/HoloText.vue';
import Tiers from '../Components/Tiers.vue';
import SellCardsPopup from '../Components/SellCardsPopup.vue';
import type { Pull } from '../types';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
  recentPulls: Pull[];
  whatsInThePool: Pull[];
  totalAvailableCards: number;
}>();

const filmGrainStyle = {
  backgroundImage:
    "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='[w3.org](http://www.w3.org/2000/svg)'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E\")",
  backgroundSize: '200px 200px',
};
</script>