<template>
  <span class="inline-flex flex-wrap gap-x-[0.25em]" :class="className">
    <span v-for="( word, i) in words" :key="i" class="overflow-hidden inline-block">
      <span v-motion="wordMotion( i )" class="inline-block" :style="style">
        {{ word }}
      </span>
    </span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  text: string;
  className?: string;
  delay?: number;
  style?: Record<string, string>;
}>();

const className = computed( () => props.className ?? '' );
const delay = computed( () => props.delay ?? 0 );
const words = computed( () => props.text.split( ' ' ) );

const wordMotion = ( i: number ) => ( {
  initial: { y: '110%', opacity: 0, rotateX: -40 },
  enter: {
    y: 0,
    opacity: 1,
    rotateX: 0,
    transition: {
      duration: 850,
      easing: [0.16, 1, 0.3, 1],
      delay: delay.value * 1000 + i * 120,
    },
  },
} );
</script>