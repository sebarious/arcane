<template>
  <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
    <div v-motion="orb1" class="absolute w-[700px] h-[700px] rounded-full" :style="{
      background: 'radial-gradient(circle, rgba(124,58,237,0.14) 0%, transparent 68%)',
      top: '-20%',
      left: '-15%',
    }" />
    <div v-motion="orb2" class="absolute w-[550px] h-[550px] rounded-full" :style="{
      background: 'radial-gradient(circle, rgba(212,160,23,0.1) 0%, transparent 68%)',
      bottom: '5%',
      right: '-10%',
    }" />
    <div v-motion="orb3" class="absolute w-[420px] h-[420px] rounded-full" :style="{
      background: 'radial-gradient(circle, rgba(109,40,217,0.1) 0%, transparent 68%)',
      top: '40%',
      left: '42%',
    }" />
  </div>
</template>

<script setup lang="ts">
const orb1 = {
  initial: { x: 0, y: 0 },
  enter: {
    x: [0, 80, -40, 0],
    y: [0, -60, 80, 0],
    transition: { duration: 22000, repeat: Infinity, easing: 'easeInOut' },
  },
};

const orb2 = {
  initial: { x: 0, y: 0 },
  enter: {
    x: [0, -90, 50, 0],
    y: [0, 70, -80, 0],
    transition: {
      duration: 26000,
      repeat: Infinity,
      easing: 'easeInOut',
      delay: 5000,
    },
  },
};

const orb3 = {
  initial: { x: 0, y: 0 },
  enter: {
    x: [0, 55, -70, 0],
    y: [0, -40, 60, 0],
    transition: {
      duration: 30000,
      repeat: Infinity,
      easing: 'easeInOut',
      delay: 11000,
    },
  },
};
</script>