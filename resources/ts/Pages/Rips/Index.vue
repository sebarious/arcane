<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';
import Orbs from '@/Components/Layout/Orbs.vue';
import packFallback from '@/Assets/Arcane_pack.webp';

interface Pack {
  slug: string;
  name: string;
  description: string | null;
  image_path: string | null;
  price_pence: number;
  games: string[];
  band_odds: Record<string, number>;
  buy_back_percentage: number;
  in_stock: boolean;
}

defineProps<{ packs: Pack[]; walletBalancePence: number | null }>();

const formatMoney = (pence: number) => '£' + (pence / 100).toFixed(2);
const formatPct = (n: number) => (n * 100).toFixed(n < 0.01 ? 2 : 1) + '%';
</script>

<template>
  <Head title="Buy a Pack" />

  <main class="bg-[#06060b] min-h-screen flex flex-col relative overflow-x-hidden">
    <Orbs />

    <div class="relative shrink-0 px-8 lg:px-16 py-5 mb-12">
      <Nav />
    </div>

    <div v-if="walletBalancePence !== null" class="relative px-8 lg:px-16 pt-4 flex justify-center">
      <Link href="/rips/wallet"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[rgba(220,193,117,0.25)] bg-[rgba(220,193,117,0.06)] hover:border-[rgba(220,193,117,0.5)] transition-colors">
        <span class="text-[10px] tracking-[0.2em] uppercase text-white/40" style="font-family: 'Jost', sans-serif;">Wallet</span>
        <span class="text-sm text-[#DCC175] font-semibold" style="font-family: 'Cinzel', serif;">{{ formatMoney(walletBalancePence) }}</span>
      </Link>
    </div>

    <section class="relative px-8 lg:px-16 pt-10 pb-6 text-center max-w-3xl mx-auto">
      <span class="text-[10px] tracking-[0.5em] uppercase text-[#DCC175] block mb-5" style="font-family: 'Jost', sans-serif;">
        Digital Rips
      </span>
      <h1 class="leading-none mb-5" style="font-family: 'Cinzel', serif; font-weight: 900; font-size: clamp(2.5rem,7vw,4.5rem);">
        Buy. Rip. Reveal.
      </h1>
      <p class="text-white/70 text-base leading-relaxed" style="font-family: 'Jost', sans-serif; font-weight: 300;">
        Every pack draws live from our real, in-stock inventory — the same pool our physical packs come from. Keep what
        you pull, or sell it straight back to us.
      </p>
    </section>

    <section class="relative px-8 lg:px-16 pb-24 flex-1">
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
        <Link v-for="pack in packs" :key="pack.slug" :href="`/rips/${pack.slug}`"
          class="group relative bg-[#13101e] border border-[rgba(220,193,117,0.12)] rounded-[14px] overflow-hidden hover:border-[rgba(220,193,117,0.4)] transition-all duration-300 hover:-translate-y-1">
          <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none" :style="{
            background: 'radial-gradient(ellipse at 50% 0%, rgba(220,193,117,0.12) 0%, transparent 65%)',
          }" />

          <div class="relative aspect-[4/3] flex items-center justify-center bg-[#0d0b14] p-8">
            <img :src="pack.image_path ?? packFallback" :alt="pack.name" class="h-full object-contain drop-shadow-[0_10px_40px_rgba(220,193,117,0.25)]" />
            <span v-if="!pack.in_stock"
              class="absolute top-3 right-3 text-[9px] tracking-[0.15em] uppercase font-bold px-2.5 py-1 rounded bg-black/70 text-white/60 border border-white/10">
              Sold out
            </span>
          </div>

          <div class="relative p-6">
            <div class="flex items-start justify-between gap-3 mb-2">
              <h3 class="text-xl text-white" style="font-family: 'Cinzel', serif; font-weight: 700;">{{ pack.name }}</h3>
              <span class="text-lg text-[#DCC175] shrink-0" style="font-family: 'Cinzel', serif; font-weight: 700;">{{ formatMoney(pack.price_pence) }}</span>
            </div>
            <p v-if="pack.description" class="text-white/50 text-sm mb-4 line-clamp-2" style="font-family: 'Jost', sans-serif;">
              {{ pack.description }}
            </p>

            <div class="flex flex-wrap gap-1.5 mb-4">
              <span v-for="game in pack.games" :key="game"
                class="text-[9px] tracking-[0.15em] uppercase font-bold px-2 py-1 rounded bg-[rgba(124,58,237,0.12)] text-[#a78bfa] border border-[rgba(124,58,237,0.25)]">
                {{ game }}
              </span>
            </div>

            <div class="flex items-center justify-between text-xs" style="font-family: 'Jost', sans-serif;">
              <span class="text-white/40">Mythic odds <b class="text-[#c9a84c]">{{ formatPct(pack.band_odds.mythic ?? 0) }}</b></span>
              <span class="text-white/40">Buy-back <b class="text-[#2dd4bf]">{{ formatPct(pack.buy_back_percentage) }}</b></span>
            </div>
          </div>
        </Link>
      </div>

      <div v-if="packs.length === 0" class="text-center text-white/40 py-20" style="font-family: 'Jost', sans-serif;">
        No packs available right now — check back soon.
      </div>

      <div class="max-w-3xl mx-auto mt-16 pt-8 border-t border-[rgba(220,193,117,0.1)] text-center">
        <p class="text-[10px] tracking-[0.3em] uppercase text-[#DCC175]/70 mb-3" style="font-family: 'Jost', sans-serif;">
          18+ Only
        </p>
        <p class="text-white/40 text-xs leading-relaxed" style="font-family: 'Jost', sans-serif;">
          Digital Rips is only available to customers aged 18 or over. Every pack always contains a genuine, real
          trading card of real monetary value — there is no outcome where you pay for a pack and receive nothing.
          Please treat this as a collectibles purchase, not a way to make money, and only spend what you can
          comfortably afford. See our <Link href="/terms" class="underline hover:text-white/60">Terms &amp;
          Conditions</Link> for full details.
        </p>
      </div>
    </section>

    <Footer />
  </main>
</template>
