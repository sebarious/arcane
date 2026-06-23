<template>
  <section class="py-4 border-y border-[#DCC175]/10 overflow-hidden">
    <div v-motion="motionOptions" class="flex gap-12 whitespace-nowrap">
      <span v-for="( item, i) in doubled" :key="i"
        class="text-[10px] tracking-[0.35em] uppercase text-[#DCC175]/35 flex items-center gap-12"
        :style="{ fontFamily: 'Jost, sans-serif' }">
        {{ item }}
        <span :style="{ color: '#DCC175' }">✦</span>
      </span>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { tickerItems } from './data';

const doubled = computed( () => [...tickerItems, ...tickerItems] );

const motionOptions = {
  initial: { x: '0%' },
  enter: {
    x: '-50%',
    transition: {
      duration: 28000,
      repeat: Infinity,
      easing: 'linear',
    },
  },
};
</script>