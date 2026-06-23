<template>
  <section class="px-8 lg:px-16 py-[50px] lg:py-[102px] relative overflow-hidden max-h-[80vh] flex items-center">
    <div class="absolute inset-0 pointer-events-none">
      <div v-motion="bgMotion" class="absolute inset-0" :style="{
        background:
          'radial-gradient(ellipse at 50% 50%, rgba(124,58,237,0.18) 0%, transparent 58%)',
      }" />
    </div>

    <div class="max-w-3xl mx-auto text-center relative z-10">
      <div v-motion="contentMotion">
        <span class="text-[10px] tracking-[0.5em] uppercase text-[#DCC175]/60 block mb-7"
          :style="{ fontFamily: 'Jost, sans-serif' }">
          Ready?
        </span>
        <h2 class="leading-none mb-9" :style="{
          fontFamily: 'Cinzel, serif',
          fontWeight: 900,
          fontSize: 'clamp(3rem,9vw,7.5rem)',
        }">
          <HoloText>Open Your Pack</HoloText>
        </h2>
        <p class="text-[#DCC175]/60 mb-14 max-w-sm mx-auto text-base leading-relaxed"
          :style="{ fontFamily: 'Jost, sans-serif', fontWeight: 300 }">
          Check the live pool at your nearest participating shop and grab
          your mystery pack today.
        </p>

        <a href="#pool"
          class="inline-block px-14 py-4 text-black text-xs tracking-[0.3em] uppercase font-bold relative overflow-hidden group"
          :style="{
            background: 'linear-gradient(135deg, #DCC175, #e8d49a)',
            borderRadius: '3px',
            fontFamily: 'Jost, sans-serif',
          }">
          <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300" :style="{
            background: 'linear-gradient(135deg, #7c3aed, #9d5cf5)',
          }" />
          <span class="relative">Find a Shop</span>
        </a>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import HoloText from './HoloText.vue';

const bgMotion = {
  initial: { scale: 1, opacity: 0.7 },
  enter: {
    scale: [1, 1.15, 1],
    opacity: [0.7, 1, 0.7],
    transition: { duration: 6000, repeat: Infinity, easing: 'easeInOut' },
  },
};

const contentMotion = {
  initial: { opacity: 0, y: 50 },
  enter: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 900,
      easing: [0.16, 1, 0.3, 1],
    },
  },
};
</script>