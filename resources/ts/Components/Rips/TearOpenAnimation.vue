<script setup lang="ts">
import { ref } from 'vue';
import { gsap } from 'gsap';
import packImage from '@/Assets/Arcane_pack.webp';
import Fireworks from './Fireworks.vue';
import type { Card } from '../../types';

interface Props {
  card: Card | null;
  // True when the page is loaded on a rip that was already opened in an
  // earlier visit — skips straight to the revealed card, no tear animation.
  initiallyRevealed?: boolean;
  // 'small' is used in the multi-pack row (Rips/Order.vue), where several of
  // these sit side by side — same animation, just a smaller stage.
  size?: 'large' | 'small';
}

const props = withDefaults(defineProps<Props>(), { initiallyRevealed: false, size: 'large' });

const maxWidth = props.size === 'small' ? '190px' : '320px';

const emit = defineEmits<{ tap: []; revealed: [] }>();

const opening = ref(props.initiallyRevealed);
const revealed = ref(props.initiallyRevealed);
const showFireworks = ref(false);
const FIREWORKS_DURATION_MS = 3000;

const packEl = ref<HTMLElement | null>(null);
const topStripEl = ref<HTMLElement | null>(null);
const leftHalfEl = ref<HTMLElement | null>(null);
const rightHalfEl = ref<HTMLElement | null>(null);
const cardEl = ref<HTMLElement | null>(null);
const glowEl = ref<HTMLElement | null>(null);

// The printed tear strip sits ~40px down a ~472px-tall pack — roughly 8.5%
// of the height, so the split works at any rendered size.
const TEAR_LINE = '8.5%';

function begin() {
  if (opening.value) return;
  opening.value = true;
  emit('tap');
}

/** Called by the parent once the card has actually been drawn server-side and sent down. */
function play() {
  if (!topStripEl.value || !leftHalfEl.value || !rightHalfEl.value || !cardEl.value || !packEl.value) {
    return;
  }

  const tl = gsap.timeline({
    onComplete: () => {
      revealed.value = true;
      emit('revealed');

      // Only for a genuinely fresh reveal this visit, not every time an
      // already-opened legendary/mythic rip's page is later revisited
      // (initiallyRevealed skips play() entirely, so this never re-fires).
      if (props.card?.band === 'legendary' || props.card?.band === 'mythic') {
        showFireworks.value = true;
        window.setTimeout(() => { showFireworks.value = false; }, FIREWORKS_DURATION_MS);
      }
    },
  });

  // Anticipation — a few gathering wobbles before anything actually tears,
  // like someone bracing to rip into the foil. Everything (strip + both
  // halves, still sitting in their untorn position) is a child of packEl,
  // so animating it here moves the whole pack as one piece.
  tl.to(packEl.value, { rotate: -3, scale: 1.02, duration: 0.35, ease: 'sine.inOut' })
    .to(packEl.value, { rotate: 3, scale: 1, duration: 0.35, ease: 'sine.inOut' })
    .to(packEl.value, { rotate: -2.5, scale: 1.03, duration: 0.3, ease: 'sine.inOut' })
    .to(packEl.value, { rotate: 1.5, scale: 1.01, duration: 0.28, ease: 'sine.inOut' })
    .to(packEl.value, { rotate: 0, scale: 1, duration: 0.32, ease: 'power1.out' })
    // The tear itself — the top strip peels away first.
    .to(topStripEl.value, {
      y: '-160%',
      rotate: -24,
      opacity: 0,
      duration: 0.8,
      ease: 'power2.in',
    }, '+=0.15')
    // A soft flash behind the card right as it starts to show through.
    .to(glowEl.value, {
      opacity: 1,
      scale: 1.3,
      duration: 0.5,
      ease: 'power2.out',
    }, '-=0.35')
    .to(cardEl.value, {
      opacity: 1,
      scale: 1,
      duration: 1.1,
      ease: 'back.out(1.4)',
    }, '<')
    .to(leftHalfEl.value, {
      x: '-100%',
      rotate: -26,
      opacity: 0,
      duration: 1.2,
      ease: 'power3.out',
    }, '<+=0.1')
    .to(rightHalfEl.value, {
      x: '100%',
      rotate: 26,
      opacity: 0,
      duration: 1.2,
      ease: 'power3.out',
    }, '<')
    .to(glowEl.value, {
      opacity: 0,
      duration: 0.8,
      ease: 'power1.out',
    }, '-=0.6');
}

defineExpose({ play });
</script>

<template>
  <div ref="packEl" class="relative mx-auto select-none" :style="{ width: `min(${maxWidth}, 80vw)`, aspectRatio: '397/472' }">
    <!-- Soft flash behind the card at the moment of reveal -->
    <div ref="glowEl" class="absolute inset-0 opacity-0 pointer-events-none" :style="{
      background: 'radial-gradient(ellipse at 50% 50%, rgba(220,193,117,0.55) 0%, transparent 70%)',
      filter: 'blur(20px)',
    }" />

    <!-- Fireworks for a legendary/mythic pull — only ever shown once the
    tear itself has fully finished (see play()'s onComplete), so its
    explicit z-index sitting above the (by then invisible) foil pieces
    doesn't matter; it reads as bursting around the revealed card. -->
    <Fireworks v-if="showFireworks && card" :band="card.band === 'mythic' ? 'mythic' : 'legendary'" />

    <!-- Card revealed from behind the torn foil -->
    <div ref="cardEl" class="absolute inset-0 flex items-center justify-center"
      :class="initiallyRevealed ? 'opacity-100' : 'opacity-0'"
      :style="{ transform: initiallyRevealed ? 'scale(1)' : 'scale(0.85)' }">
      <img v-if="card" :src="card.image" :alt="card.name"
        class="w-[78%] rounded-xl shadow-[0_0_60px_rgba(220,193,117,0.35)] object-cover"
        style="aspect-ratio: 63/88;" />
    </div>

    <!-- Foil halves — only during an active tear this visit. A rip that was
    already opened on an earlier visit (initiallyRevealed) skips straight to
    the card above; GSAP never runs, so these must never render un-torn on
    top of it. -->
    <template v-if="opening && !initiallyRevealed">
      <img ref="topStripEl" :src="packImage" class="absolute inset-0 w-full h-full object-cover"
        :style="{ clipPath: `inset(0 0 ${100 - 8.5}% 0)` }" />
      <img ref="leftHalfEl" :src="packImage" class="absolute inset-0 w-full h-full object-cover"
        :style="{ clipPath: `inset(${TEAR_LINE} 50% 0 0)` }" />
      <img ref="rightHalfEl" :src="packImage" class="absolute inset-0 w-full h-full object-cover"
        :style="{ clipPath: `inset(${TEAR_LINE} 0 0 50%)` }" />
    </template>

    <!-- Idle whole pack, tap to begin -->
    <button v-else-if="!opening" type="button" @click="begin" class="absolute inset-0 w-full h-full group cursor-pointer">
      <img :src="packImage" alt="Arcane pack" class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-[1.02]" />
      <span :class="[
        'absolute inset-x-0 bottom-5 text-center font-[\'Jost\',sans-serif] tracking-[0.25em] uppercase text-white/90 bg-black/40 backdrop-blur-sm py-2 rounded',
        size === 'small' ? 'text-[9px] tracking-[0.15em]' : 'text-[11px]',
      ]">
        Tap to open
      </span>
    </button>
  </div>
</template>
