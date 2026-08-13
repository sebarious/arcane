<template>
  <ClientOnly>
    <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center">
      <div v-for="( size, i) in sizes" :key="size" class="absolute rounded-full border floating-ring"
        :style="{
        width: size + 'px',
        height: size + 'px',
        borderColor: ringColor( i ),
        animationDuration: ( 30000 + i * 12000 ) + 'ms',
        animationDirection: i % 2 === 0 ? 'normal' : 'reverse',
      }" />
    </div>
  </ClientOnly>
</template>

<script setup lang="ts">
const sizes = [600, 820, 1060];

const ringColor = ( i: number ) =>
  `rgba(${i === 0 ? '212,160,23' : i === 1 ? '124,58,237' : '180,130,10'},0.07)`;
</script>

<style scoped>
/* Runs on the compositor thread instead of via @vueuse/motion's per-frame JS
   tween — these rotate forever for as long as the page is open, and with
   several of them stacked on a page the JS-driven version was a measurable
   drag on main-thread responsiveness (scroll/tap jank). */
.floating-ring {
  animation-name: floating-ring-spin;
  animation-timing-function: linear;
  animation-iteration-count: infinite;
}

@keyframes floating-ring-spin {
  from { transform: rotate( 0deg ); }
  to { transform: rotate( 360deg ); }
}
</style>