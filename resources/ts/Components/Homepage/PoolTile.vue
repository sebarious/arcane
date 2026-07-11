<template>
  <div
    class="flex-shrink-0 mx-2 border border-[#DCC175]/10 bg-[#0e0e1d]/80 group relative overflow-hidden flex p-3 gap-5"
    :style="{ borderRadius: '6px', height: '110px', width: '280px' }">
    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500" :style="{
      background:
        'radial-gradient(circle at 50% 0%, rgba(220,193,117,0.06) 0%, transparent 70%)',
    }" />
    <div class="flex-shrink-0 h-full overflow-hidden relative z-10"
      :style="{ aspectRatio: '63/88', borderRadius: '3px' }">
      <img :src="pull.card.image" :alt="pull.card.name" class="w-full h-full object-cover" loading="lazy" />
    </div>
    <div class="flex flex-col justify-center flex-1 min-w-0 relative z-10">
      <div>
        <h4 class="text-sm text-white leading-snug mb-0.5 truncate"
          :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
          {{ pull.card.name}}
        </h4>
        <p class="text-[10px] text-[#DCC175]/60 truncate mb-1" :style="{ fontFamily: 'Jost, sans-serif' }">
          {{ pull.card.set }}
        </p>
        <p class="text-[10px] text-purple-300/50" :style="{ fontFamily: 'Jost, sans-serif' }">
          {{ pull.store.name }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Pull } from '../../types';

const props = defineProps<{
  pull: Pull;
}>();
</script>