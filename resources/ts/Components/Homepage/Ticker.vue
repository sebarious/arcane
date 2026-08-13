<template>
  <section class="py-4 border-y border-[#DCC175]/10 overflow-hidden">
    <div class="flex gap-12 whitespace-nowrap ticker-track">
      <span v-for="( item, i) in doubled" :key="i"
        class="text-[10px] tracking-[0.35em] uppercase text-[#DCC175] flex items-center gap-12"
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
</script>

<style scoped>
/* Was a @vueuse/motion per-frame JS tween — see FloatingRings.vue for why
   that's worth avoiding for anything that runs forever. */
.ticker-track {
  animation: ticker-scroll 20s linear infinite;
}

@keyframes ticker-scroll {
  from { transform: translateX( 0 ); }
  to { transform: translateX( -50% ); }
}
</style>