<template>
  <div>
    <nav v-motion="navMotion" :class="[
      'fixed top-0 left-0 right-0 z-50 px-6 lg:px-16 py-5 flex items-center justify-between transition-all duration-500',
      scrolled
        ? 'backdrop-blur-2xl bg-[#06060b]/70 border-b border-[#DCC175]/10'
        : '',
    ]">
      <div class="flex items-center">
        <img :src="arcaneLogo" alt="Arcane" class="h-10 w-auto" />
      </div>

      <!-- Desktop links -->
      <div class="hidden md:flex items-center gap-9">
        <div class="flex gap-9 text-xs tracking-[0.22em] uppercase text-[#DCC175]/60"
          :style="{ fontFamily: 'Jost, sans-serif' }">
          <a v-for=" [label, href] in NAV_LINKS " :key="label" :href="href"
            class="hover:text-[#DCC175] transition-colors duration-300">
            {{ label }}
          </a>
        </div>
        <a href="#seller"
          class="text-xs tracking-[0.18em] uppercase px-5 py-2.5 bg-[#DCC175] text-black font-semibold hover:bg-[#e8d49a] transition-all duration-300"
          :style="{ borderRadius: '3px', fontFamily: 'Jost, sans-serif' }">
          Become a Seller
        </a>
      </div>

      <!-- Burger button — mobile only -->
      <button
        class="md:hidden flex items-center justify-center w-10 h-10 text-[#DCC175]/70 hover:text-[#DCC175] transition-colors"
        @click="toggleOpen" aria-label="Toggle menu">
        <X v-if=" open " :size="22" />
        <Menu v-else :size="22" />
      </button>
    </nav>

    <!-- Mobile drawer -->
    <div v-motion="drawerMotion" class="fixed inset-0 z-40 md:hidden flex flex-col"
      :style="{ background: 'rgba(6,6,11,0.97)', backdropFilter: 'blur(24px)' }" :aria-hidden="!open">
      <div class="h-20" />
      <nav class="flex flex-col px-8 gap-1 flex-1">
        <a v-for="( [label, href], i) in NAV_LINKS" :key="label" :href="href" @click="close"
          class="py-4 text-2xl text-[#DCC175]/70 hover:text-[#DCC175] border-b border-[#DCC175]/8 transition-colors"
          :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
          {{ label }}
        </a>
      </nav>
      <div class="px-8 pb-12 pt-8">
        <a href="#seller" @click="close"
          class="block w-full text-center py-4 bg-[#DCC175] text-black text-sm font-bold tracking-[0.2em] uppercase hover:bg-[#e8d49a] transition-colors"
          :style="{ borderRadius: '4px', fontFamily: 'Jost, sans-serif' }">
          Become a Seller
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Menu, X } from 'lucide-vue-next';
import arcaneLogo from './imports/Link___Arcane.png';

const NAV_LINKS: [string, string][] = [
  ['Stores', '#stores'],
  ['Sell to Us', '#sell'],
  ['Log In', '#login'],
];

const scrolled = ref( false );
const open = ref( false );

const toggleOpen = () => {
  open.value = !open.value;
};
const close = () => {
  open.value = false;
};

const onScroll = () => {
  scrolled.value = window.scrollY > 60;
};

onMounted( () => {
  window.addEventListener( 'scroll', onScroll, { passive: true } );
} );

onUnmounted( () => {
  window.removeEventListener( 'scroll', onScroll );
} );

// lock body scroll when menu open
watch( open, ( val ) => {
  document.body.style.overflow = val ? 'hidden' : '';
} );

// motion configs
const navMotion = {
  initial: { y: -80, opacity: 0 },
  enter: {
    y: 0,
    opacity: 1,
    transition: { duration: 900, easing: [0.16, 1, 0.3, 1] },
  },
};

const drawerMotion = {
  initial: { opacity: 0, x: '100%' },
  enter: () => ( {
    opacity: open.value ? 1 : 0,
    x: open.value ? '0%' : '100%',
    transition: { duration: 350, easing: [0.16, 1, 0.3, 1] },
  } ),
};
</script>