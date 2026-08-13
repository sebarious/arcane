<template>
  <ClientOnly>
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute inset-0 bg-[#06060b]" />
      <div v-for="( b, i) in blobs" :key="i" class="absolute hero-blob" :style="{
        width: b.size + 'px',
        height: b.size + 'px',
        top: b.top,
        left: b.left,
        background: b.color,
        filter: `blur(${b.blur}px)`,
        animationDuration: b.dur * 1000 + 'ms',
        animationDelay: i * 1500 + 'ms',
        '--blob-x1': b.xPath[1] + 'px', '--blob-y1': b.yPath[1] + 'px',
        '--blob-x2': b.xPath[2] + 'px', '--blob-y2': b.yPath[2] + 'px',
        '--blob-x3': b.xPath[3] + 'px', '--blob-y3': b.yPath[3] + 'px',
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
  </ClientOnly>
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


</script>

<style scoped>
/* Was a @vueuse/motion per-frame JS tween of x/y, running forever — see the
   same reasoning in Orbs.vue/FloatingRings.vue. The path is data-driven per
   blob, so the waypoints are passed in as CSS custom properties rather than
   hardcoded, in case more blobs are added to the array later. */
.hero-blob {
  border-radius: 50%;
  animation-name: hero-blob-drift;
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
}

@keyframes hero-blob-drift {
  0%, 100% { transform: translate( 0, 0 ); }
  25% { transform: translate( var( --blob-x1 ), var( --blob-y1 ) ); }
  50% { transform: translate( var( --blob-x2 ), var( --blob-y2 ) ); }
  75% { transform: translate( var( --blob-x3 ), var( --blob-y3 ) ); }
}
</style>