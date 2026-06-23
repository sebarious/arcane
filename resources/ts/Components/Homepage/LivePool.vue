<template>
  <section id="pool" class="py-[52px] lg:py-[82px]">
    <div v-motion="headerMotion"
      class="px-8 lg:px-16 max-w-7xl mx-auto flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14">
      <div>
        <div class="flex items-center gap-2 mb-5">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
          <span class="text-[10px] tracking-[0.45em] uppercase text-emerald-400/70"
            :style="{ fontFamily: 'Jost, sans-serif' }">
            Live — Updated Now
          </span>
        </div>
        <h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none"
          :style="{ fontFamily: 'Cinzel, serif', fontWeight: 700 }">
          {{ "What's in" }} <HoloText>the Pool</HoloText>
        </h2>
      </div>
      <p class="text-sm text-[#DCC175]/60 max-w-xs leading-relaxed"
        :style="{ fontFamily: 'Jost, sans-serif', fontWeight: 300 }">
        Every card listed below is available at your local shop. Full odds
        transparency — always.
      </p>
    </div>

    <div class="overflow-hidden mb-4">
      <div v-motion="row1Motion" class="flex">
        <PoolTile v-for="( card, i) in row1Doubled" :key="i" :card="card" />
      </div>
    </div>

    <div class="overflow-hidden">
      <div v-motion="row2Motion" class="flex">
        <PoolTile v-for="( card, i) in row2Doubled" :key="i" :card="card" />
      </div>
    </div>

    <div v-motion="linkMotion" class="mt-10 text-center">
      <a href="#"
        class="inline-block text-[10px] text-[#DCC175]/70 tracking-[0.3em] uppercase border-b border-[#DCC175]/50 pb-0.5 hover:text-[#DCC175]/80 hover:border-[#DCC175]/70 transition-colors"
        :style="{ fontFamily: 'Jost, sans-serif' }">
        Find Your Nearest Shop →
      </a>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import HoloText from './HoloText.vue';
import PoolTile from './PoolTile.vue';
import { livePool } from './data';

const poolRow1 = livePool.slice( 0, 4 );
const poolRow2 = livePool.slice( 4 );

const row1Doubled = computed( () => [...poolRow1, ...poolRow1] );
const row2Doubled = computed( () => [...poolRow2, ...poolRow2] );

const headerMotion = {
  initial: { opacity: 0, y: 30 },
  enter: {
    opacity: 1,
    y: 0,
    transition: { duration: 800 },
  },
};

const row1Motion = {
  initial: { x: '0%' },
  enter: {
    x: '-50%',
    transition: { duration: 22000, repeat: Infinity, easing: 'linear' },
  },
};

const row2Motion = {
  initial: { x: '-50%' },
  enter: {
    x: '0%',
    transition: { duration: 22000, repeat: Infinity, easing: 'linear' },
  },
};

const linkMotion = {
  initial: { opacity: 0 },
  enter: {
    opacity: 1,
    transition: { delay: 500 },
  },
};
</script>