<template>
  <div>
    <nav :class="[
      'fixed top-0 left-0 right-0 z-50 px-6 lg:px-16 py-5 flex items-center justify-between transition-all duration-500',
      scrolled
        ? 'backdrop-blur-2xl bg-[#06060b]/70 border-b border-[#DCC175]/10'
        : '',
    ]">
      <a class="flex items-center" href="/" title="Arcane">
        <img :src="arcaneLogo" alt="Arcane" class="h-10 w-auto" />
      </a>

      <!-- Desktop links -->
      <div class="hidden md:flex items-center gap-9">
        <div class="flex gap-9 text-xs tracking-[0.22em] uppercase text-[#DCC175]"
          :style="{ fontFamily: 'Jost, sans-serif' }">
          <a v-for=" [label, href, active] in NAV_LINKS " :key="label" :href="href"
            :class="['hover:text-[#DCC175] transition-colors duration-300', active ? 'text-white underline' : 'text-[#DCC175]']">
            {{ label }}
          </a>
        </div>
        <Link :href="isLoggedIn ? '/seller' : '/apply'"
          class="text-xs tracking-[0.18em] uppercase px-5 py-2.5 bg-[#DCC175] text-black font-semibold hover:bg-[#e8d49a] transition-all duration-300"
          :style="{ borderRadius: '3px', fontFamily: 'Jost, sans-serif' }">
          {{ isLoggedIn ? 'Seller Dashboard' : 'Become a Seller' }}
        </Link>
      </div>

      <!-- Burger button — mobile only -->
      <button
        class="md:hidden flex items-center justify-center w-10 h-10 text-[#DCC175] hover:text-[#DCC175] transition-colors"
        @click="toggleOpen" aria-label="Toggle menu">
        <X v-if=" open " :size="22" />
        <Menu v-else :size="22" />
      </button>
    </nav>

    <!-- Mobile drawer -->
    <div class="fixed inset-0 z-[100] md:hidden flex flex-col" v-if="open"
      :style="{ background: 'rgba(6,6,11,0.97)', backdropFilter: 'blur(24px)' }" :aria-hidden="!open">
      <div class="h-20" />
      <nav class="flex flex-col px-8 gap-1 flex-1">
        <a v-for="( [label, href, active], i) in NAV_LINKS" :key="label" :href="href" @click="close"
          :class="[
            'py-4 text-2xl border-b border-[#DCC175]/8 transition-colors',
            active ? 'text-white underline' : 'text-[#DCC175] hover:text-[#DCC175]',
          ]"
          :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
          {{ label }}
        </a>
      </nav>
      <div class="px-8 pb-12 pt-8">
        <Link :href="isLoggedIn ? '/seller' : '/apply'" @click="close"
          class="block w-full text-center py-4 bg-[#DCC175] text-black text-sm font-bold tracking-[0.2em] uppercase hover:bg-[#e8d49a] transition-colors"
          :style="{ borderRadius: '4px', fontFamily: 'Jost, sans-serif' }">
          {{ isLoggedIn ? 'Seller Dashboard' : 'Become a Seller' }}
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { Menu, X } from 'lucide-vue-next';
import arcaneLogo from '@/Assets/Link___Arcane.png';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const isLoggedIn = computed( () => !!(page?.props?.auth as any)?.user );

const NAV_LINKS = computed( (): [string, string, boolean][] => [
  ['Sell to Us', '/sell', !!(page?.props?.route as any)?.name?.startsWith('sell')],
  ['Stores', '/stores', !!(page?.props?.route as any)?.name?.startsWith('stores')],
  ['Affiliate', '/affiliate-program', !!(page?.props?.route as any)?.name?.startsWith('affiliate-program')],
  ...( isLoggedIn.value
    ? []
    : [['Log In', '/login', !!(page?.props?.route as any)?.name?.startsWith('login')] as [string, string, boolean]] ),
] );

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
    transition: { duration: 500, easing: [0.16, 1, 0.3, 1] },
  },
};

const drawerMotion = {
  initial: { opacity: 0, x: '100%' },
  enter: {
    opacity: 1,
    x: '0%',
    transition: { duration: 250, easing: [0.16, 1, 0.3, 1] },
  },
  leave: {
    opacity: 0,
    x: '100%',
    transition: { duration: 250, easing: [0.16, 1, 0.3, 1] },
  },
};
</script>