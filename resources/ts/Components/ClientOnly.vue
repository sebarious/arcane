<script setup lang="ts">
import { onMounted, ref } from 'vue';

// Renders nothing during SSR and nothing on the client's first paint either
// — only after onMounted fires does the slot actually render. That's
// deliberate: every current user of this component (Orbs, HeroBG,
// HeroSparkles, FloatingRings, HoloText) either uses Math.random() to pick
// its content (HeroSparkles' particles, HeroBG's blob paths) or is otherwise
// meant to be pure client-side decoration. Rendering that during SSR means
// the server bakes in one random result, then the client's first render
// (hydration) computes a *different* random result — a hydration mismatch,
// which Vue recovers from by discarding the server-rendered DOM for that
// subtree and re-rendering it from scratch on the client, right in the
// middle of the page becoming interactive. That repair work was very likely
// the actual cause of the reported mobile slowness (tap lag on the nav menu,
// below-the-fold content staying blank for several seconds) — worse on
// mobile simply because the CPU is slower, so the repair takes longer and
// is more likely to be still running when someone taps something.
//
// Deferring to onMounted sidesteps this entirely: the server renders
// nothing, the client's hydration pass also expects nothing (matching, no
// mismatch), and the random content only ever appears afterwards, as an
// ordinary reactive patch rather than a hydration repair.
const mounted = ref(false);

onMounted(() => {
  mounted.value = true;
});
</script>

<template>
  <slot v-if="mounted" />
</template>
