<template>
  <section ref="sectionRef" class="relative min-h-[95vh] max-h-[95vh] flex items-center overflow-hidden"
    @mousemove="handleMouseMove">
    <!-- Background layers -->
    <HeroBG />
    <HeroSparkles />
    <FloatingRings />

    <!-- Main content grid (parallax + fade via computed style) -->
    <div
      class="relative z-10 w-full px-8 lg:px-16 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 items-center pt-6 lg:pt-14"
      :style="contentStyle">
      <!-- Left: Copy -->
      <div>
        <!-- Live badge -->
        <div v-motion="liveBadgeMotion"
          class="inline-flex items-center gap-2 px-3 py-1.5 border border-[#DCC175]/25 bg-[#DCC175]/8 mb-9"
          :style="{ borderRadius: '3px' }">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
          <span class="text-[10px] text-[#DCC175]/70 tracking-[0.3em] uppercase"
            :style="{ fontFamily: 'Jost, sans-serif' }">
            Live Pool — 8 Cards Available
          </span>
        </div>

        <!-- Heading: One Card. Infinite Chase. -->
        <div class="mb-8" :style="{
          fontFamily: 'Cinzel, serif',
          fontWeight: 900,
          fontSize: '64px',
          lineHeight: 1.05,
        }">
          <div class="overflow-hidden mb-1">
            <SplitWords text="One Card." className="text-white" :delay="0.25" />
          </div>
          <div class="overflow-hidden whitespace-nowrap">
            <SplitWords text="Infinite Chase." :delay="0.5" :style="infiniteChaseStyle" />
          </div>
        </div>

        <!-- Subtext -->
        <p v-motion="subtextMotion" class="text-base leading-relaxed mb-10 max-w-md" :style="{
          fontFamily: 'Jost, sans-serif',
          fontWeight: 300,
          color: '#e8e4f0',
        }">
          Authenticated, near‑mint Pokémon singles sealed into mystery packs —
          one toploaded hit per pack, every time. See the live card pool before
          you buy.
        </p>

        <!-- CTAs -->
        <div v-motion="ctaRowMotion" class="flex gap-4 flex-wrap">
          <a href="#pool"
            class="px-8 py-3.5 bg-[#DCC175] text-black text-xs tracking-[0.22em] uppercase font-semibold hover:bg-[#e8d49a] transition-colors duration-300"
            :style="{ borderRadius: '3px', fontFamily: 'Jost, sans-serif' }">
            Browse Stores
          </a>
          <a href="#how-it-works"
            class="px-8 py-3.5 text-[#DCC175]/70 text-xs tracking-[0.22em] uppercase border border-[#DCC175]/25 hover:border-[#DCC175]/50 hover:text-[#DCC175] transition-all duration-300 backdrop-blur-sm"
            :style="{ borderRadius: '3px', fontFamily: 'Jost, sans-serif' }">
            Sign Up
          </a>
        </div>

        <!-- Pack tier tiles — mobile only -->
        <div v-motion="mobileTiersMotion" class="flex gap-2.5 mt-8 lg:hidden">
          <div v-for=" tier in PACK_TIERS " :key="tier.name"
            class="flex-1 px-3 py-2.5 backdrop-blur-xl flex flex-col gap-0.5" :style="{
              borderRadius: '6px',
              background: tier.bg,
              border: `1px solid ${tier.border}`,
              boxShadow: `0 4px 24px rgba(0,0,0,0.6), 0 0 18px ${tier.glow}`,
            }">
            <span class="text-[10px] font-bold tracking-[0.12em] uppercase" :style="{
              color: tier.color,
              fontFamily: 'Cinzel, serif',
              textShadow: `0 0 10px ${tier.glow}`,
            }">
              {{ tier.name }}
            </span>
            <span class="text-[9px] tracking-widest" :style="{
              color: `${tier.color}90`,
              fontFamily: 'Jost, sans-serif',
            }">
              {{ tier.qty }}
            </span>
          </div>
        </div>
      </div>

      <!-- Right: floating pack (desktop only) -->
      <div class="hidden lg:block">
        <FloatingPack :mouseX="rawX" :mouseY="rawY" />
      </div>
    </div>

    <!-- Scroll cue -->
    <div v-motion="scrollCueMotion"
      class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
      <div v-motion="scrollLineMotion" class="w-px h-10 bg-gradient-to-b from-amber-400/35 to-transparent"
        :style="{ transformOrigin: 'top' }" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import HeroBG from './HeroBG.vue';
import HeroSparkles from './HeroSparkles.vue';
import FloatingRings from './FloatingRings.vue';
import FloatingPack from './FloatingPack.vue';
import SplitWords from './SplitWords.vue';

// --- local PACK_TIERS (same as React) ---------------------------------------
const PACK_TIERS = [
  {
    name: 'Sapphire',
    qty: 'x100',
    color: '#93c5fd',
    glow: 'rgba(59,130,246,0.7)',
    bg: 'linear-gradient(135deg, #0c1f6e 0%, #1a4db5 100%)',
    border: 'rgba(96,165,250,0.5)',
  },
  {
    name: 'Ruby',
    qty: 'x250',
    color: '#fca5a5',
    glow: 'rgba(220,38,38,0.7)',
    bg: 'linear-gradient(135deg, #6e0c0c 0%, #b91c1c 100%)',
    border: 'rgba(248,113,113,0.5)',
  },
  {
    name: 'Diamond',
    qty: 'x500',
    color: '#e8f0ff',
    glow: 'rgba(200,220,255,0.8)',
    bg: 'rgba(255,255,255,0.18)',
    border: 'rgba(210,230,255,0.55)',
  },
];

// --- scroll-based parallax replacement for useScroll/useTransform -----------

const sectionRef = ref<HTMLElement | null>( null );
const scrollYProgress = ref( 0 ); // 0..1

const onScroll = () => {
  const el = sectionRef.value;
  if ( !el ) return;

  const rect = el.getBoundingClientRect();
  const viewportHeight = window.innerHeight || 1;

  // approximate progress from top of section entering viewport to top leaving
  const start = rect.top - viewportHeight;
  const end = rect.bottom;
  const y = -start;
  const range = end - start || 1;

  const p = Math.min( 1, Math.max( 0, y / range ) );
  scrollYProgress.value = p;
};

onMounted( () => {
  window.addEventListener( 'scroll', onScroll, { passive: true } );
  onScroll(); // initial
} );
onUnmounted( () => {
  window.removeEventListener( 'scroll', onScroll );
} );

const contentStyle = computed( () => {
  // y: 0% -> 28% as you scroll
  const y = 28 * scrollYProgress.value;
  // start fading only after 30% scroll, and be fully gone by 100%
  const fadeStart = 0.6;
  const fadeEnd = 1;
  let opacity = 1;
  if ( scrollYProgress.value > fadeStart ) {
    const t =
      ( scrollYProgress.value - fadeStart ) / ( fadeEnd - fadeStart || 1 );
    opacity = 1 - Math.min( 1, Math.max( 0, t ) );
  }
  return {
    transform: `translateY(${y}%)`,
    opacity,
  };
} );

// --- mouse tracking replacement for useMotionValue --------------------------

const rawX = ref( 0 ); // -0.5 .. 0.5
const rawY = ref( 0 );

const handleMouseMove = ( e: MouseEvent ) => {
  const el = sectionRef.value;
  if ( !el ) return;
  const rect = el.getBoundingClientRect();
  rawX.value = ( e.clientX - rect.left ) / rect.width - 0.5;
  rawY.value = ( e.clientY - rect.top ) / rect.height - 0.5;
};

// --- styles and motion configs ----------------------------------------------

const infiniteChaseStyle = {
  backgroundImage:
    'linear-gradient(90deg,#4c1d95,#7c3aed,#a855f7,#c084fc,#ddd6fe,#a855f7,#7c3aed,#4c1d95)',
  backgroundSize: '300% 100%',
  WebkitBackgroundClip: 'text',
  WebkitTextFillColor: 'transparent',
  backgroundClip: 'text',
  display: 'inline-block',
};

const liveBadgeMotion = {
  initial: { opacity: 0, y: 20 },
  enter: {
    opacity: 1,
    y: 0,
    transition: { delay: 200, duration: 800 },
  },
};

const subtextMotion = {
  initial: { opacity: 0, y: 18 },
  enter: {
    opacity: 1,
    y: 0,
    transition: { delay: 1000, duration: 900 },
  },
};

const ctaRowMotion = {
  initial: { opacity: 0, y: 18 },
  enter: {
    opacity: 1,
    y: 0,
    transition: { delay: 1250, duration: 900 },
  },
};

const mobileTiersMotion = {
  initial: { opacity: 0, y: 16 },
  enter: {
    opacity: 1,
    y: 0,
    transition: {
      delay: 1400,
      duration: 800,
      easing: [0.16, 1, 0.3, 1],
    },
  },
};

const scrollCueMotion = {
  initial: { opacity: 0 },
  enter: {
    opacity: 1,
    transition: { delay: 2200 },
  },
};

const scrollLineMotion = {
  initial: { scaleY: 0 },
  enter: {
    scaleY: [0, 1, 0],
    transition: {
      duration: 2200,
      repeat: Infinity,
      easing: 'easeInOut',
    },
  },
};
</script>