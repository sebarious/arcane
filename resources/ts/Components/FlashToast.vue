<script setup lang="ts">
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/global';
import { toast, toasts, dismissToast } from '@/toast';

const page = usePage<PageProps>();

// This component is mounted once, outside Inertia's swappable page component
// (see AppRoot.vue) — an in-SPA navigation (a form post + redirect, e.g.
// "Request batch") updates page.props reactively without remounting
// anything, so a plain onMounted-once check would miss every flash except
// one already present on a genuine full browser page load. Watching instead
// catches both.
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) toast(flash.success, 'success');
    else if (flash?.error) toast(flash.error, 'error');
    else if (flash?.status) toast(flash.status, 'status');
  },
  { deep: true, immediate: true },
);

const ACCENTS: Record<'success' | 'error' | 'status', { border: string; dot: string }> = {
  success: { border: '#22c55e', dot: '#22c55e' },
  error: { border: '#ef4444', dot: '#ef4444' },
  status: { border: '#DCC175', dot: '#DCC175' },
};
</script>

<template>
  <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[200] flex flex-col gap-2 w-[calc(100%-2rem)] max-w-[420px] pointer-events-none">
    <transition-group enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-4"
      enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in absolute"
      leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4" move-class="transition duration-200 ease-out">
      <div v-for="t in toasts" :key="t.id"
        class="flex items-start gap-3 px-5 py-4 pointer-events-auto"
        :style="{
          background: '#13101e',
          border: `1px solid ${ACCENTS[t.kind].border}40`,
          borderRadius: '8px',
          boxShadow: '0 8px 28px rgba(0,0,0,0.45)',
        }">
        <span class="w-2 h-2 mt-1.5 rounded-full shrink-0" :style="{ background: ACCENTS[t.kind].dot }" />
        <p class="text-sm text-white/90 flex-1" style="font-family: 'Jost', sans-serif;">{{ t.message }}</p>
        <button type="button" @click="dismissToast(t.id)" aria-label="Dismiss"
          class="text-white/40 hover:text-white/80 text-lg leading-none shrink-0">
          &times;
        </button>
      </div>
    </transition-group>
  </div>
</template>
