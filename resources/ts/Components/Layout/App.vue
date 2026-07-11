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
</script>