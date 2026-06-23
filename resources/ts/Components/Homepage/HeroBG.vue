<template>
  <div class="absolute inset-0 overflow-hidden">
    <div class="absolute inset-0 bg-[#06060b]" />
    <div v-for="( b, i) in blobs" :key="i" v-motion="blobMotion( b, i )" class="absolute" :style="{
      width: b.size + 'px',
      height: b.size + 'px',
      top: b.top,
      left: b.left,
      background: b.color,
      filter: `blur(${b.blur}px)`,
    }" />
    <div class="absolute inset-0 bg-[#06060b]/60" />
    <div class="absolute inset-0" :style="{
      background:
        'radial-gradient(ellipse at 50% 40%, transparent 20%, rgba(6,6,11,0.88) 80%)',
    }" />
    <div class="absolute inset-0 opacity-[0.04] pointer-events-none" :style="{
      backgroundImage:
        'repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255,255,255,1) 2px, rgba(255,255,255,1) 3px)',
      backgroundSize: '100% 3px',
    }" />
  </div>
</template>

<script setup lang="ts">
const blobs = [
  // same BLOBS array you had, but simplified to only needed fields
  {
    color: 'rgba(88,28,220,0.28)',
    size: 820,
    top: '-10%',
    left: '-5%',
    xPath: [0, 120, -60, 40, 0],
    yPath: [0, -80, 110, -40, 0],
    dur: 18,
    blur: 160,
  },
  // ...rest
];

const blobMotion = ( b: ( typeof blobs )[number], i: number ) => ( {
  initial: { x: 0, y: 0, borderRadius: '50%' },
  enter: {
    x: b.xPath,
    y: b.yPath,
    transition: {
      duration: b.dur * 1000,
      repeat: Infinity,
      easing: 'easeInOut',
      delay: i * 1500,
    },
  },
} );
</script>