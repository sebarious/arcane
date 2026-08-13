<template>
  <ClientOnly>
    <span :class="['inline-block text-transparent bg-clip-text holo-text', className]" :style="baseStyle">
      <slot />
    </span>
  </ClientOnly>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  className?: string;
}>();

const className = computed( () => props.className ?? '' );

const baseStyle = {
  backgroundImage:
    'linear-gradient(90deg,#4c1d95,#7c3aed,#a855f7,#c084fc,#ddd6fe,#a855f7,#7c3aed,#4c1d95)',
  backgroundSize: '300% 100%',
  WebkitBackgroundClip: 'text',
  WebkitTextFillColor: 'transparent',
};
</script>

<style scoped>
/* Was a @vueuse/motion per-frame JS tween — see FloatingRings.vue for why
   that's worth avoiding for anything that runs forever, especially since
   this renders more than once per page (e.g. LivePool also uses it). */
.holo-text {
  animation: holo-shimmer 5s linear infinite;
}

@keyframes holo-shimmer {
  from { background-position: 0% 50%; }
  to { background-position: 200% 50%; }
}
</style>