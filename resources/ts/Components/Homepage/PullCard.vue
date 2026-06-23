<template>
  <div class="flex-shrink-0 flex flex-col" :style="{ width: CARD_W + 'px' }">
    <!-- Card + glow -->
    <div class="relative" :style="{ padding: '28px 12px 12px' }">
      <!-- Rarity glow -->
      <div class="absolute inset-0 rounded-2xl transition-all duration-300" :style="glowStyle" />

      <!-- Card image -->
      <div class="relative overflow-hidden transition-all duration-300" :class="[
        'will-change-transform',
        active ? 'scale-105 opacity-100 shadow-xl' : 'scale-90 opacity-50'
      ]" :style="{ aspectRatio: '63/88', borderRadius: '10px' }">
        <img :src="pull.img" :alt="pull.name" class="w-full h-full object-cover" draggable="false" />
      </div>
    </div>

    <!-- Info below card -->
    <div class="px-3 mt-2 transition-opacity duration-300" :class="active ? 'opacity-100' : 'opacity-50'">
      <h4 class="text-base text-white leading-snug mb-0.5" :style="{ fontFamily: 'Cinzel, serif', fontWeight: 700 }">
        {{ pull.name }}
      </h4>
      <p class="text-[10px] tracking-[0.18em] uppercase mb-3" :style="{
        fontFamily: 'Jost, sans-serif',
        color: 'rgba(255,255,255,0.35)',
      }">
        {{ pull.set }}
      </p>
      <div class="flex items-center justify-between">
        <div class="flex gap-1.5">
          <span class="text-[9px] px-2 py-0.5 tracking-[0.2em] uppercase font-bold" :style="{
            borderRadius: '2px',
            fontFamily: 'Jost, sans-serif',
            background: 'rgba(220,193,117,0.1)',
            color: '#DCC175',
            border: '1px solid rgba(220,193,117,0.25)',
          }">
            {{ pull.grade }}
          </span>
          <span class="text-[9px] px-2 py-0.5 tracking-[0.15em] uppercase font-bold" :style="{
            borderRadius: '2px',
            fontFamily: 'Jost, sans-serif',
            color: colors.badge,
            background: `${colors.badge}18`,
            border: `1px solid ${colors.badge}40`,
          }">
            {{ pull.rarity }}
          </span>
        </div>
        <span class="text-sm font-bold" :style="{ fontFamily: 'Jost, sans-serif', color: '#DCC175' }">
          {{ pull.value }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Pull } from '../types';

const props = defineProps<{
  pull: Pull;
  active: boolean;
}>();

const CARD_W = 280;

const RARITY_COLORS: Record<
  Pull['rarity'],
  { glow: string; shimmer: string; badge: string; }
> = {
  Common: {
    glow: 'rgba(244,114,182,0.65)',
    shimmer: 'rgba(244,114,182,0.18)',
    badge: '#f472b4',
  },
  Rare: {
    glow: 'rgba(74,222,128,0.65)',
    shimmer: 'rgba(74,222,128,0.18)',
    badge: '#4ade80',
  },
  Ultra: {
    glow: 'rgba(251,146,60,0.65)',
    shimmer: 'rgba(251,146,60,0.18)',
    badge: '#fb923c',
  },
  Legendary: {
    glow: 'rgba(96,165,250,0.65)',
    shimmer: 'rgba(96,165,250,0.18)',
    badge: '#60a5fa',
  },
  God: {
    glow: 'rgba(220,193,117,0.75)',
    shimmer: 'rgba(220,193,117,0.22)',
    badge: '#DCC175',
  },
};

const colors = computed( () => RARITY_COLORS[props.pull.rarity] );

const glowStyle = computed( () => ( {
  background: `radial-gradient(ellipse at 50% 60%, ${colors.value.glow} 0%, transparent 70%)`,
  filter: 'blur(22px)',
  opacity: props.active ? 0.9 : 0.15,
  transform: props.active ? 'scale(1.05)' : 'scale(0.9)',
} ) );
</script>