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
          <!-- FRONT: pack -->
          <div :style="frontFaceStyle">
            <img :src="arcanePack" alt="Arcane Mystery Pack" class="w-full h-full object-cover select-none"
              draggable="false" />
          </div>

          <!-- BACK: revealed card -->
          <div :style="backFaceStyle">
            <img :src="currentCard" alt="Pokémon card" class="w-full h-full object-cover select-none"
              draggable="false" />
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

const props = defineProps<{
  mouseX: number;
  mouseY: number;
}>();

const PACK_TIERS = [
  {
    name: 'Sapphire',
    qty: 'x100',
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
const rotationY = ref( 0 );
const ringRotation = ref( 0 );

let flipInterval: number | null = null;
let ringInterval: number | null = null;

onMounted( () => {
  flipInterval = window.setInterval( () => {
    rotationY.value += 180;

    // update card just before the next "front" becomes visible
    window.setTimeout( () => {
      cardIndex.value = ( cardIndex.value + 1 ) % CARD_IMAGES.length;
    }, 450 );
  }, 3000 );

  ringInterval = window.setInterval( () => {
    ringRotation.value += 1;
  }, 16 );
} );

onUnmounted( () => {
  if ( flipInterval !== null ) window.clearInterval( flipInterval );
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
  transformStyle: 'preserve-3d',
  transition: 'transform 0.9s cubic-bezier(0.16, 1, 0.3, 1)',
  transform: `rotateY(${rotationY.value}deg)`,
} ) );

const sharedFaceStyle: Record<string, string> = {
  position: 'absolute',
  inset: '0',
  borderRadius: '10px',
  overflow: 'hidden',
  backfaceVisibility: 'hidden',
  boxShadow: '0 0 40px rgba(124,58,237,0.45), 0 30px 60px rgba(0,0,0,0.75)',
};

const frontFaceStyle = {
  ...sharedFaceStyle,
  transform: 'rotateY(0deg)',
};

const backFaceStyle = {
  ...sharedFaceStyle,
  transform: 'rotateY(180deg)',
};

const ringStyle = computed<Record<string, string>>( () => ( {
  transform: `rotate(${ringRotation.value}deg)`,
  transition: 'transform 16ms linear',
} ) );
</script>