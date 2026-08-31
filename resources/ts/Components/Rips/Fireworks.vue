<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ band: 'legendary' | 'mythic' }>();

const GOLD = ['#DCC175', '#e8d49a', '#c9a84c', '#fff3d1'];
const PURPLE = ['#7b4fe9', '#a78bfa', '#c084fc', '#ddd6fe'];

interface Particle {
  id: number;
  color: string;
  tx: string;
  ty: string;
  size: string;
}

interface Burst {
  id: number;
  originX: string;
  originY: string;
  delay: string;
  particles: Particle[];
}

// A handful of burst centres, each firing a ring of particles outward —
// staggered delays so they read as several fireworks going off in sequence
// rather than one single blast. Positions/timings are seeded once per
// mount — this component only ever exists for the few seconds a legendary/
// mythic reveal is being celebrated (see TearOpenAnimation.vue), so there's
// nothing to keep in sync on re-render.
//
// Teleported to <body> and positioned via the viewport (see the template) —
// origins/distances here are sized for a full-screen show, not just the
// small pack box the animation itself sits in.
const bursts = computed<Burst[]>(() => {
  const palette = props.band === 'mythic' ? GOLD : PURPLE;
  const BURST_COUNT = 7;
  const PARTICLES_PER_BURST = 18;

  return Array.from({ length: BURST_COUNT }, (_, b) => ({
    id: b,
    originX: `${5 + Math.random() * 90}%`,
    originY: `${5 + Math.random() * 65}%`,
    delay: `${b * 0.35 + Math.random() * 0.15}s`,
    particles: Array.from({ length: PARTICLES_PER_BURST }, (_, p) => {
      const angle = (p / PARTICLES_PER_BURST) * 360 + Math.random() * 10;
      const distance = 90 + Math.random() * 110;

      return {
        id: p,
        color: palette[p % palette.length],
        tx: `${Math.cos((angle * Math.PI) / 180) * distance}px`,
        ty: `${Math.sin((angle * Math.PI) / 180) * distance}px`,
        size: `${4 + Math.random() * 4}px`,
      };
    }),
  }));
});
</script>

<template>
  <!-- Teleported out to <body> and fixed to the viewport — a full-screen
  celebration, not confined to the (much smaller) pack box the reveal
  animation itself lives in. Fixed positioning also means this can never
  contribute to any ancestor's scrollable content size, regardless of how
  far particles travel, so it can't resurrect the popup scrollbar bug the
  contained version was built to avoid. -->
  <Teleport to="body">
    <div class="fixed inset-0 pointer-events-none overflow-hidden" style="z-index: 160;">
      <div v-for="burst in bursts" :key="burst.id" class="absolute" :style="{ left: burst.originX, top: burst.originY }">
        <span v-for="p in burst.particles" :key="p.id" class="firework-particle"
          :style="{
            '--tx': p.tx,
            '--ty': p.ty,
            width: p.size,
            height: p.size,
            background: p.color,
            color: p.color,
            animationDelay: burst.delay,
          }" />
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.firework-particle {
  position: absolute;
  border-radius: 50%;
  opacity: 0;
  animation: firework-burst 1.4s ease-out forwards;
  box-shadow: 0 0 10px 2px currentColor;
}

@keyframes firework-burst {
  0% {
    transform: translate(0, 0) scale(1);
    opacity: 1;
  }
  60% {
    opacity: 1;
  }
  100% {
    transform: translate(var(--tx), var(--ty)) scale(0.3);
    opacity: 0;
  }
}
</style>
