<template>
  <ClientOnly>
    <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center">
      <div v-for="( size, i) in sizes" :key="size" v-motion="ringMotion( i )" class="absolute rounded-full border"
        :style="{
        width: size + 'px',
        height: size + 'px',
        borderColor: ringColor( i ),
      }" />
    </div>
  </ClientOnly>
</template>

<script setup lang="ts">
const sizes = [600, 820, 1060];

const ringColor = ( i: number ) =>
  `rgba(${i === 0 ? '212,160,23' : i === 1 ? '124,58,237' : '180,130,10'},0.07)`;

const ringMotion = ( i: number ) => ( {
  initial: { rotate: 0 },
  enter: {
    rotate: i % 2 === 0 ? 360 : -360,
    transition: {
      duration: 30000 + i * 12000,
      repeat: Infinity,
      easing: 'linear',
    },
  },
} );
</script>