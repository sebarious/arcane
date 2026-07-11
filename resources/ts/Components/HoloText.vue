<template>
  <ClientOnly>
    <span v-motion="motionOptions" :class="['inline-block text-transparent bg-clip-text', className]" :style="baseStyle">
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

const motionOptions = {
  initial: {
    backgroundPosition: '0% 50%',
  },
  enter: {
    backgroundPosition: '200% 50%',
    transition: {
      duration: 5000,
      repeat: Infinity,
      easing: 'linear',
    },
  },
};
</script>