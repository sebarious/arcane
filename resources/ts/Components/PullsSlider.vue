<template>
  <section id="pulls" class="py-[52px] lg:py-[82px]">
    <!-- Header -->
    <div class="px-8 lg:px-16 flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-6">
      <div>
        <h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none"
          :style="{ fontFamily: 'Cinzel, serif', fontWeight: 700 }">
          Latest <HoloText>Pulls</HoloText>
        </h2>
      </div>
      <div class="flex items-center gap-3">
        <button @click="prev"
          class="w-10 h-10 rounded-full border border-[#DCC175]/25 flex items-center justify-center text-[#DCC175]/60 hover:border-[#DCC175]/70 hover:text-[#DCC175] transition-all duration-200">
          <ChevronLeft :size="18" />
        </button>
        <div class="flex gap-1.5">
          <button v-for="( p, i) in pulls" :key="i" @click="goTo( i )" class="transition-all duration-300"
            :style="dotStyle( i )" />
        </div>
        <button @click="next"
          class="w-10 h-10 rounded-full border border-[#DCC175]/25 flex items-center justify-center text-[#DCC175]/60 hover:border-[#DCC175]/70 hover:text-[#DCC175] transition-all duration-200">
          <ChevronRight :size="18" />
        </button>
      </div>
    </div>

    <!-- Slider track -->
    <div class="overflow-hidden py-10" :style="{ paddingLeft: `calc(50vw - ${CARD_W / 2}px)` }">
      <div ref="trackRef" class="flex cursor-grab active:cursor-grabbing" :style="trackStyle"
        @pointerdown="onPointerDown">
        <PullCard v-for="( pull, i) in INF_PULLS" :key="i" :pull="(pull as Pull)" :active="i === current" />
        <div :style="{ width: 'calc(100vw - 560px)', flexShrink: 0 }" />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import HoloText from './HoloText.vue';
import PullCard from './PullCard.vue';
import type { Pull } from '../types';

const props = defineProps<{
  pulls: Pull[];
}>();

const CARD_W = 280;
const CARD_GAP = 20;
const STEP = CARD_W + CARD_GAP;
const INF_PULLS = [...props.pulls, ...props.pulls, ...props.pulls];
const INF_OFFSET = props.pulls.length;

const current = ref( INF_OFFSET );
const trackRef = ref<HTMLDivElement | null>( null );

const activePullIndex = computed( () => current.value % props.pulls.length );

const trackStyle = computed( () => ( {
  gap: `${CARD_GAP}px`,
  transform: `translateX(${-current.value * STEP}px)`,
  transition: 'transform 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
} ) );

const dotStyle = ( i: number ) => ( {
  width: i === activePullIndex.value ? '20px' : '6px',
  height: '6px',
  borderRadius: '3px',
  background:
    i === activePullIndex.value
      ? '#DCC175'
      : 'rgba(220,193,117,0.25)',
} );

const prev = () => {
  const nextIndex = current.value - 1;
  current.value = nextIndex < 1 ? INF_OFFSET - 1 : nextIndex;
};

const next = () => {
  const nextIndex = current.value + 1;
  current.value = nextIndex > INF_PULLS.length - 2 ? INF_OFFSET + 1 : nextIndex;
};

const goTo = ( i: number ) => {
  current.value = INF_OFFSET + i;
};

// auto-advance
let intervalId: number | null = null;
onMounted( () => {
  intervalId = window.setInterval( () => {
    next();
  }, 4000 );
} );
onUnmounted( () => {
  if ( intervalId !== null ) window.clearInterval( intervalId );
} );

// simple drag: track pointerdown/move/up to detect swipe direction
let startX = 0;
let dragging = false;

const onPointerMove = ( e: PointerEvent ) => {
  if ( !dragging ) return;
  const delta = e.clientX - startX;
  if ( delta < -60 ) {
    dragging = false;
    next();
  } else if ( delta > 60 ) {
    dragging = false;
    prev();
  }
};

const onPointerUp = () => {
  dragging = false;
  window.removeEventListener( 'pointermove', onPointerMove );
  window.removeEventListener( 'pointerup', onPointerUp );
};

const onPointerDown = ( e: PointerEvent ) => {
  dragging = true;
  startX = e.clientX;
  window.addEventListener( 'pointermove', onPointerMove );
  window.addEventListener( 'pointerup', onPointerUp );
};
</script>