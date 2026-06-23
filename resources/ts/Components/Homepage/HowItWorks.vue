<template>
  <section id="how-it-works" class="px-8 lg:px-16 py-[12px] lg:py-[42px] max-w-7xl mx-auto">
    <div v-motion="headerMotion" class="mb-16">
      <h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none"
        :style="{ fontFamily: 'Cinzel, serif', fontWeight: 700 }">
        How It <HoloText>Works</HoloText>
      </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="( step, i) in steps" :key="step.num" v-motion="cardMotion( i )"
        class="p-8 border border-[#DCC175]/10 bg-[#0e0e1d]/60 relative overflow-hidden group"
        :style="{ borderRadius: '4px' }">
        <div
          class="absolute inset-0 bg-gradient-to-br from-amber-900/0 to-amber-900/0 group-hover:from-amber-900/8 group-hover:to-transparent transition-all duration-500" />
        <div class="flex items-start justify-between mb-6">
          <span class="text-[10px] text-[#DCC175]/60 tracking-[0.3em]" :style="{ fontFamily: 'Jost, sans-serif' }">
            {{ step.num }}
          </span>
          <div class="w-9 h-9 rounded-full bg-[#DCC175]/10 border border-[#DCC175]/50 flex items-center justify-center">
            <component :is="step.icon" :size="16" class="text-[#DCC175]" />
          </div>
        </div>
        <h3 class="text-xl text-white mb-3 tracking-tight" :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
          {{ step.title }}
        </h3>
        <p class="text-sm text-[#DCC175]/60 leading-relaxed"
          :style="{ fontFamily: 'Jost, sans-serif', fontWeight: 300 }">
          {{ step.desc }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import HoloText from './HoloText.vue';
import { steps } from './data';

const headerMotion = {
  initial: { opacity: 0, y: 30 },
  enter: {
    opacity: 1,
    y: 0,
    transition: { duration: 800 },
  },
};

const cardMotion = ( i: number ) => ( {
  initial: { opacity: 0, y: 50, borderColor: 'rgba(220,193,117,0.1)' },
  enter: {
    opacity: 1,
    y: 0,
    borderColor: 'rgba(220,193,117,0.1)',
    transition: {
      duration: 700,
      delay: i * 120,
      easing: [0.16, 1, 0.3, 1],
    },
  },
} );
</script>