<template>
  <ClientOnly>
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div v-for=" p in particles " :key="p.id" v-motion="particleMotion( p )" class="absolute"
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
  rotate: p.shape === 'diamond' ? '45deg' : '0deg',
  boxShadow: `0 0 ${p.size * 3}px ${p.color}`,
} );

const particleMotion = ( p: Particle ) => ( {
  initial: { y: 0, opacity: 0, scale: 0 },
  enter: {
    y: -( 300 + Math.random() * 200 ),
    opacity: [0, 0.9, 0.9, 0],
    scale: [0, 1.2, 1, 0],
    transition: {
      duration: p.dur * 1000,
      repeat: Infinity,
      delay: p.delay * 1000,
      easing: 'easeOut',
    },
  },
} );
</script>