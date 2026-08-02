<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const STORAGE_KEY = 'arcane_sell_popup_dismissed';
const SHOW_DELAY_MS = 10000;

const visible = ref( false );
let timer: ReturnType<typeof setTimeout> | undefined;

onMounted( () => {
  if ( localStorage.getItem( STORAGE_KEY ) ) return;
  timer = setTimeout( () => { visible.value = true; }, SHOW_DELAY_MS );
} );

onUnmounted( () => {
  if ( timer ) clearTimeout( timer );
} );

function dismiss() {
  visible.value = false;
  localStorage.setItem( STORAGE_KEY, '1' );
}
</script>

<template>
  <transition enter-active-class="transition duration-500 ease-out"
    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-4">
    <div v-if=" visible "
      class="fixed bottom-6 right-6 z-50 w-[300px] bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[12px] p-[20px] shadow-[0px_8px_28px_rgba(0,0,0,0.45)]">
      <button type="button" @click="dismiss" aria-label="Dismiss"
        class="absolute top-[10px] right-[12px] text-[#71717a] hover:text-white text-[18px] leading-none">
        ×
      </button>

      <p class="font-['Cinzel',sans-serif] font-bold text-[16px] text-white pr-[16px]">
        We want to buy your cards
      </p>
      <p class="font-['Jost',sans-serif] font-normal text-[13px] text-[#a3a3a3] mt-[8px] leading-relaxed">
        Get an instant offer of up to <span class="text-[#c9a84c] font-semibold">80% of market value</span>
        — fast, secure payment, no fees.
      </p>

      <Link href="/sell"
        class="mt-[16px] flex items-center justify-center h-[40px] rounded-[4px] w-full"
        style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
        <span class="font-['Jost',sans-serif] font-bold text-[13px] text-[#0d0b14] uppercase">Sell to us</span>
      </Link>
    </div>
  </transition>
</template>
