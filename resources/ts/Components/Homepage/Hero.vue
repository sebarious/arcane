<template>
  <section ref="sectionRef" class="relative sm:min-h-[95vh] sm:max-h-[95vh] flex items-center overflow-hidden"
    @mousemove="handleMouseMove">
    <!-- Background layers -->
    <HeroBG />
    <HeroSparkles />
    <FloatingRings />

    <!-- Main content grid (parallax + fade via computed style) -->
    <div
      class="relative z-10 w-full px-8 lg:px-16 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center pt-24 pb-16 lg:pt-14 lg:pb-0">
      <!-- Left: Copy -->
      <div class="relative z-[2]">
        <!-- Live badge -->
        <div v-if=" (totalAvailableCards ?? 0 ) > 0" class="inline-flex items-center gap-2 px-3 py-1.5 border border-[#DCC175]/25 bg-[#DCC175]/8 mb-9"
          :style="{ borderRadius: '3px' }">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
          <span class="text-[10px] text-[#DCC175]/70 tracking-[0.3em] uppercase"
            :style="{ fontFamily: 'Jost, sans-serif' }">
            Live Pool — {{ (totalAvailableCards ?? 0).toLocaleString() }} Cards Available
          </span>
        </div>

        <!-- Heading: One Card. Infinite Chase. -->
        <h1 class="mb-8 text-[42px] md:text-[64px]" :style="{
          fontFamily: 'Cinzel, serif',
          fontWeight: 900,
          lineHeight: 1.05,
        }">
          <div class="overflow-hidden mb-1">
            <SplitWords text="Know the Odds." className="text-white" :delay="0.25" />
          </div>
          <div class="overflow-hidden whitespace-nowrap">
            <SplitWords text="Love Every Pull." :delay="0.5" :style="infiniteChaseStyle" />
          </div>
        </h1>

        <!-- Subtext -->
        <p class="text-sm md:text-base leading-relaxed mb-10 max-w-md" :style="{
          fontFamily: 'Jost, sans-serif',
          fontWeight: 300,
          color: '#e8e4f0',
        }">
          Mystery packs built on transparency. Live card pools, real-time pricing and verified pulls give you complete
          confidence before every purchase.
        </p>

        <!-- CTAs -->
        <div class="grid grid-cols-2 md:flex gap-4 md:flex-wrap">
          <Link href="/apply"
            class="text-center md:px-8 py-3.5 bg-[#DCC175] text-black text-xs tracking-[0.22em] uppercase font-semibold hover:bg-[#e8d49a] transition-colors duration-300"
            :style="{ borderRadius: '3px', fontFamily: 'Jost, sans-serif' }">
          Apply Now
          </Link>
          <a href="/stores"
            class="text-center md:px-8 py-3.5 text-[#DCC175]/70 text-xs tracking-[0.22em] uppercase border border-[#DCC175]/25 hover:border-[#DCC175]/50 hover:text-[#DCC175] transition-all duration-300 backdrop-blur-sm"
            :style="{ borderRadius: '3px', fontFamily: 'Jost, sans-serif' }">
            Browse stores
          </a>
        </div>
      </div>

      <!-- Right: floating pack (desktop only) -->
      <div class="relative z-[1]">
        <FloatingPack :mouseX="rawX" :mouseY="rawY" />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import HeroBG from '../HeroBG.vue';
import HeroSparkles from '../HeroSparkles.vue';
import FloatingRings from '../FloatingRings.vue';
import FloatingPack from './FloatingPack.vue';
import SplitWords from './SplitWords.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  totalAvailableCards: number;
}>();

// --- local PACK_TIERS (same as React) ---------------------------------------
const PACK_TIERS = [
  {
    name: 'Sapphire',
    qty: 'x125',
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

const isMobile = ref( false );
const checkMobile = (): void => {
  isMobile.value = window.innerWidth < 1024;
};
onMounted( () => {
  checkMobile();
  window.addEventListener( 'resize', checkMobile );
  window.addEventListener( 'scroll', onScroll, { passive: true } );
  onScroll();
} );
onUnmounted( () => {
  window.removeEventListener( 'resize', checkMobile );
  window.removeEventListener( 'scroll', onScroll );
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
</script>