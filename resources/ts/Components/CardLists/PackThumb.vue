<template>
  <div class="relative shrink-0" :style="{ width: totalWidth + 'px', height: totalHeight + 'px' }">
    <div v-if="topCardImage1" class="absolute overflow-hidden rounded-[4px] shadow-lg" :style="leftCardStyle">
      <img :src="topCardImage1" alt="" class="w-full h-full object-cover select-none" draggable="false"
        loading="lazy" />
    </div>

    <div v-if="topCardImage2" class="absolute overflow-hidden rounded-[4px] shadow-lg" :style="rightCardStyle">
      <img :src="topCardImage2" alt="" class="w-full h-full object-cover select-none" draggable="false"
        loading="lazy" />
    </div>

    <div class="absolute overflow-hidden rounded-[6px] shadow-xl" :style="packStyle">
      <img :src="arcanePack" alt="Arcane Mystery Pack" class="w-full h-full object-cover select-none"
        draggable="false" loading="lazy" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import arcanePack from '@/Assets/Arcane_pack.png';

const props = defineProps<{
  topCardImage1?: string | null;
  topCardImage2?: string | null;
}>();

// Same relative proportions as the homepage hero's pack+cards composition
// (Components/Homepage/FloatingPack.vue), just scaled down to fit a grid
// thumbnail instead of the full hero — a static box card doesn't need that
// component's mouse-tilt interactivity, just the same "look". Geometry is
// derived from FloatingPack's own numbers (pack width 330, card 250x350,
// left card offset -187.5, right card margin -62.5) scaled by PACK_WIDTH/330,
// then laid out left-to-right in a wrapper sized to exactly fit all three.
const PACK_WIDTH = 108;
const PACK_HEIGHT = Math.round((PACK_WIDTH * 88) / 63);
const CARD_WIDTH = Math.round(PACK_WIDTH * (250 / 330));
const CARD_HEIGHT = Math.round(PACK_WIDTH * (350 / 330));
const LEFT_OFFSET = Math.round(PACK_WIDTH * (187.5 / 330));
const RIGHT_MARGIN = Math.round(PACK_WIDTH * (62.5 / 330));

// Shifting everything right by LEFT_OFFSET makes the left card's left edge
// land at 0 — see the comment above for how each element's position derives.
const totalWidth = LEFT_OFFSET + PACK_WIDTH - RIGHT_MARGIN + CARD_WIDTH;
const totalHeight = Math.max(PACK_HEIGHT, CARD_HEIGHT) + 16;

const packStyle = computed(() => ({
  left: LEFT_OFFSET + 'px',
  top: (totalHeight - PACK_HEIGHT) / 2 + 'px',
  width: PACK_WIDTH + 'px',
  height: PACK_HEIGHT + 'px',
}));

const leftCardStyle = computed(() => ({
  left: '0px',
  top: '50%',
  width: CARD_WIDTH + 'px',
  height: CARD_HEIGHT + 'px',
  transform: 'translateY(-50%) rotate(-12deg)',
}));

const rightCardStyle = computed(() => ({
  left: LEFT_OFFSET + PACK_WIDTH - RIGHT_MARGIN + 'px',
  top: '50%',
  width: CARD_WIDTH + 'px',
  height: CARD_HEIGHT + 'px',
  transform: 'translateY(-50%) rotate(12deg)',
}));
</script>
