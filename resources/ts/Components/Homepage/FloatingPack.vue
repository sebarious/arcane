<template>
  <div class="flex flex-col items-center justify-center h-[520px]">
    <div class="relative flex flex-col items-center justify-center" :style="{ perspective: '1200px' }">
      <!-- ambient glow -->
      <div class="absolute w-[420px] h-[420px] rounded-full blur-3xl" :style="{
        background:
          'radial-gradient(circle, rgba(124,58,237,0.22) 0%, rgba(220,193,117,0.12) 35%, transparent 72%)',
      }" />

      <!-- rotating outer aura -->
      <div class="absolute w-[380px] h-[380px] rounded-full border border-[#DCC175]/10" :style="ringStyle" />

      <!-- floating + tilt wrapper -->
      <div class="relative z-10 transition-transform duration-200" :style="wrapperStyle">
        <!-- flipping object -->
        <div class="relative" :style="flipContainerStyle">
          <!-- BACK: revealed card -->
          <div :style="backCard1">
            <img :src="CARD_IMAGES[0]" alt="Pokémon card" class="w-full h-full object-cover select-none"
              draggable="false" loading="lazy" />

            <div
              class="absolute bottom-0 left-0 right-0 h-[50%] bg-gradient-to-t from-black/100 to-transparent pointer-events-none">
              <div class="absolute bottom-4 left-2.5">
                <span class="text-[9px] px-2 py-0.5 tracking-[0.15em] uppercase font-bold" :style="{
                  borderRadius: '2px',
                  fontFamily: 'Jost, sans-serif',
                  color: RARITY_COLORS['super'].badge,
                  background: `${RARITY_COLORS['super'].badge}18`,
                  border: `1px solid ${RARITY_COLORS['super'].badge}40`,
                }">
                  Super
                </span>
                <p class="text-xs font-medium text-white/80 mt-1">Victini</p>
              </div>
            </div>
          </div>

          <!-- BACK: revealed card -->
          <div :style="backCard2" class="back-card">
            <img :src="CARD_IMAGES[1]" alt="Pokémon card" class="w-full h-full object-cover select-none"
              draggable="false" loading="lazy" />

            <div
              class="absolute bottom-0 left-0 right-0 h-[50%] bg-gradient-to-t from-black/100 to-transparent pointer-events-none">
              <div class="absolute bottom-4 right-2.5 text-right">
                <span class="text-[9px] px-2 py-0.5 tracking-[0.15em] uppercase font-bold" :style="{
                  borderRadius: '2px',
                  fontFamily: 'Jost, sans-serif',
                  color: RARITY_COLORS['mythic'].badge,
                  background: `${RARITY_COLORS['mythic'].badge}18`,
                  border: `1px solid ${RARITY_COLORS['mythic'].badge}40`,
                }">
                  Mythic
                </span>
                <p class="text-xs font-medium text-white/80 mt-1">Mew Ex</p>
              </div>
            </div>
          </div>

          <!-- FRONT: pack -->
          <div :style="sharedFaceStyle">
            <img :src="arcanePack" alt="Arcane Mystery Pack" class="w-full h-full object-cover select-none"
              draggable="false" loading="lazy" />
          </div>
        </div>
      </div>

      <!-- tier boxes restored -->
      <div class="flex gap-3 mt-8">
        <div v-for=" tier in PACK_TIERS " :key="tier.name"
          class="px-4 py-3 backdrop-blur-xl flex flex-col items-center min-w-[96px] transition-transform duration-300 hover:scale-105"
          :style="{
            borderRadius: '6px',
            background: tier.bg,
            border: `1px solid ${tier.border}`,
            boxShadow: `0 4px 24px rgba(0,0,0,0.55), 0 0 18px ${tier.glow}`,
          }">
          <span class="text-[11px] font-bold tracking-[0.12em] uppercase" :style="{
            color: tier.color,
            fontFamily: 'Cinzel, serif',
            textShadow: `0 0 10px ${tier.glow}`,
          }">
            {{ tier.name }}
          </span>
          <span class="text-[9px] tracking-widest mt-1" :style="{
            color: `${tier.color}90`,
            fontFamily: 'Jost, sans-serif',
          }">
            {{ tier.qty }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { CARD_IMAGES, arcanePack } from './data';
import type { Pull } from '../../types';

const props = defineProps<{
  mouseX: number;
  mouseY: number;
}>();

const RARITY_COLORS: Record<
  Pull['card']['band'],
  { glow: string; shimmer: string; badge: string; }
> = {
  common: {
    glow: 'rgba(163,163,163,0.65)',
    shimmer: 'rgba(163,163,163,0.1)',
    badge: '#a3a3a3',
  },
  rare: {
    glow: 'rgba(59,130,246,0.75)',
    shimmer: 'rgba(59,130,246,0.1)',
    badge: '#3b82f6',
  },
  super: {
    glow: 'rgba(45,212,191,0.75)',
    shimmer: 'rgba(45,212,191,0.1)',
    badge: '#2dd4bf',
  },
  legendary: {
    glow: 'rgba(123,79,233,0.65)',
    shimmer: 'rgba(123,79,233,0.1)',
    badge: '#7b4fe9',
  },
  mythic: {
    glow: 'rgba(201,168,76,0.75)',
    shimmer: 'rgba(201, 168, 76, 0.2)',
    badge: '#c9a84c',
  },
};

const PACK_TIERS = [
  {
    name: 'Sapphire',
    qty: 'x125',
    color: '#93c5fd',
    glow: 'rgba(59,130,246,0.7)',
    bg: 'linear-gradient(135deg, #0c1f6e 0%, #1a4db5 100%)',
    border: 'rgba(96,165,250,0.5)',
  },
  {
    name: 'Ruby',
    qty: 'x250',
    color: '#fca5a5',
    glow: 'rgba(220,38,38,0.7)',
    bg: 'linear-gradient(135deg, #6e0c0c 0%, #b91c1c 100%)',
    border: 'rgba(248,113,113,0.5)',
  },
  {
    name: 'Diamond',
    qty: 'x500',
    color: '#e8f0ff',
    glow: 'rgba(200,220,255,0.8)',
    bg: 'rgba(255,255,255,0.18)',
    border: 'rgba(210,230,255,0.55)',
  },
] as const;

const cardIndex = ref( 0 );
const ringRotation = ref( 0 );

let ringInterval: number | null = null;

onMounted( () => {
  ringInterval = window.setInterval( () => {
    ringRotation.value += 1;
  }, 16 );
} );

onUnmounted( () => {
  if ( ringInterval !== null ) window.clearInterval( ringInterval );
} );

const currentCard = computed<string>( () => CARD_IMAGES[cardIndex.value] );

const tiltX = computed<number>( () => props.mouseY * -12 );
const tiltY = computed<number>( () => props.mouseX * 16 );

const wrapperStyle = computed<Record<string, string>>( () => ( {
  transform: `
    translateY(${Math.sin( Date.now() / 700 ) * 6}px)
    rotateX(${tiltX.value}deg)
    rotateY(${tiltY.value}deg)
  `,
  transformStyle: 'preserve-3d',
} ) );

const flipContainerStyle = computed<Record<string, string>>( () => ( {
  width: '330px',
  aspectRatio: '63 / 88',
  position: 'relative',
} ) );

const sharedFaceStyle: Record<string, string> = {
  position: 'absolute',
  inset: '0',
  borderRadius: '10px',
  overflow: 'hidden',
  backfaceVisibility: 'hidden',
};

const backCard1 = computed<Record<string, string>>( () => ( {
  ...sharedFaceStyle,
  left: '-187.5px',
  width: '250px',
  height: '350px',
  top: '50%',
  transform: 'translateY(-50%) rotate(-12deg)',
} ) );

const backCard2 = computed<Record<string, string>>( () => ( {
  ...sharedFaceStyle,
  left: '100%',
  marginLeft: '-62.5px',
  width: '250px',
  height: '350px',
  top: '50%',
  transform: 'translateY(-50%) rotate(12deg)',
} ) );

const ringStyle = computed<Record<string, string>>( () => ( {
  transform: `rotate(${ringRotation.value}deg)`,
  transition: 'transform 16ms linear',
} ) );
</script>