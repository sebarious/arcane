<template>
  <div class="bg-background text-foreground min-h-screen relative overflow-x-hidden">
    <!-- film grain overlay -->
    <div class="fixed inset-0 pointer-events-none z-10 opacity-[0.028]" :style="filmGrainStyle" />
    <Orbs />
    <Nav />

    <main class="relative z-20">
      <slot />
    </main>
  </div>
</template>

<script lang="ts" setup>
import Orbs from '../Components/Homepage/Orbs.vue';
import Nav from '../Components/Homepage/Nav.vue';

const filmGrainStyle = {
  backgroundImage:
    "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='[w3.org](http://www.w3.org/2000/svg)'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E\")",
  backgroundSize: '200px 200px',
};
</script>