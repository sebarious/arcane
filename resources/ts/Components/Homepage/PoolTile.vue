<template>
  <div
    class="flex-shrink-0 mx-2 border border-[#DCC175]/10 bg-[#0e0e1d]/80 group relative overflow-hidden flex p-3 gap-3"
    :style="{ borderRadius: '6px', height: '110px', width: '280px' }">
    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500" :style="{
      background:
        'radial-gradient(circle at 50% 0%, rgba(220,193,117,0.06) 0%, transparent 70%)',
    }" />
    <div class="flex-shrink-0 h-full overflow-hidden relative z-10"
      :style="{ aspectRatio: '63/88', borderRadius: '3px' }">
      <img :src="card.img" :alt="card.name" class="w-full h-full object-cover" />
    </div>
    <div class="flex flex-col justify-between flex-1 min-w-0 relative z-10">
      <div class="flex justify-between items-start">
        <span
          class="text-[9px] tracking-[0.2em] uppercase px-1.5 py-0.5 bg-[#DCC175]/10 text-[#DCC175] border border-[#DCC175]/50"
          :style="{ borderRadius: '2px', fontFamily: 'Jost, sans-serif' }">
          {{ card.grade }}
        </span>
        <span class="text-[9px] text-emerald-400/70 tracking-widest" :style="{ fontFamily: 'Jost, sans-serif' }">
          {{ card.qty }} left
        </span>
      </div>
      <div>
        <h4 class="text-sm text-white leading-snug mb-0.5 truncate"
          :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
          {{ card.name }}
        </h4>
        <p class="text-[10px] text-[#DCC175]/60 truncate mb-1" :style="{ fontFamily: 'Jost, sans-serif' }">
          {{ card.set }}
        </p>
        <p class="text-[10px] text-purple-300/50" :style="{ fontFamily: 'Jost, sans-serif' }">
          {{ card.streamer }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { LiveCard } from '../../types'; // or inline

const props = defineProps<{
  card: LiveCard;
}>();
</script>