<template>
  <ClientOnly>
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div v-for=" p in particles " :key="p.id" class="absolute sparkle-particle"
        :style="particleStyle( p )" />
    </div>
  </ClientOnly>
</template>

<script setup lang="ts">
import { computed } from 'vue';

// same color palette as React
const COLORS = ['#d4a017', '#b68d0e', '#7c3aed', '#9d5cf5', '#c4a800', '#6d28d9', '#e8c130'];

type Particle = {
  id: number;
  left: number;
  startY: number;
  size: number;
  dur: number;
  delay: number;
  color: string;
  shape: 'diamond' | 'circle';
};

const particles = computed<Particle[]>( () =>
  Array.from( { length: 48 }, ( _, i ) => {
    const left = Math.random() * 100;
    const startY = 80 + Math.random() * 20;
    const size = Math.random() * 4 + 1;
    const dur = Math.random() * 5 + 4;
    const delay = Math.random() * 8;
    const color = COLORS[Math.floor( Math.random() * COLORS.length )];
    const shape: Particle['shape'] = Math.random() > 0.6 ? 'diamond' : 'circle';

    return {
      id: i,
      left,
      startY,
      size,
      dur,
      delay,
      color,
      shape,
    };
  } )
);

const particleStyle = ( p: Particle ) => ( {
  left: `${p.left}%`,
  top: `${p.startY}%`,
  width: `${p.size}px`,
  height: `${p.size}px`,
  background: p.color,
  borderRadius: p.shape === 'circle' ? '50%' : '2px',
  '--sparkle-rotate': p.shape === 'diamond' ? '45deg' : '0deg',
  boxShadow: `0 0 ${p.size * 3}px ${p.color}`,
  '--sparkle-rise': `-${300 + Math.random() * 200}px`,
  animationDuration: p.dur * 1000 + 'ms',
  animationDelay: p.delay * 1000 + 'ms',
} );
</script>

<style scoped>
/* 48 of these render per hero — each used to be an independent @vueuse/motion
   per-frame JS tween (repeat: Infinity), which is a lot of main-thread work
   to keep paying forever just for ambient decoration. CSS keyframes let the
   compositor thread handle all 48 instead. Only the rise distance/duration/
   delay vary per particle, so those are passed in as custom properties/
   animation-* rather than needing a unique keyframe per particle. */
.sparkle-particle {
  transform: translateY( 0 ) scale( 0 ) rotate( var( --sparkle-rotate ) );
  opacity: 0;
  animation-name: sparkle-rise;
  animation-timing-function: ease-out;
  animation-iteration-count: infinite;
}

@keyframes sparkle-rise {
  0% { transform: translateY( 0 ) scale( 0 ) rotate( var( --sparkle-rotate ) ); opacity: 0; }
  33.333% { transform: translateY( calc( var( --sparkle-rise ) * 0.333 ) ) scale( 1.2 ) rotate( var( --sparkle-rotate ) ); opacity: 0.9; }
  66.667% { transform: translateY( calc( var( --sparkle-rise ) * 0.667 ) ) scale( 1 ) rotate( var( --sparkle-rotate ) ); opacity: 0.9; }
  100% { transform: translateY( var( --sparkle-rise ) ) scale( 0 ) rotate( var( --sparkle-rotate ) ); opacity: 0; }
}
</style>