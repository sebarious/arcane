<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/global';

const page = usePage<PageProps>();

// Captured once on mount rather than read reactively — Laravel's session
// flash data is only ever present for the single request right after a
// redirect (e.g. the /q/{token} QR scan), so there's nothing to re-sync
// against on later Inertia navigations within the same page load.
const kind = ref<'success' | 'error' | 'status' | null>(null);
const message = ref('');
const visible = ref(false);

const AUTO_DISMISS_MS = 6000;

onMounted(() => {
  const flash = page.props.flash;
  const found = flash?.success
    ? ('success' as const)
    : flash?.error
      ? ('error' as const)
      : flash?.status
        ? ('status' as const)
        : null;

  if (!found) return;

  kind.value = found;
  message.value = flash[found] ?? '';
  visible.value = true;

  window.setTimeout(() => {
    visible.value = false;
  }, AUTO_DISMISS_MS);
});

const dismiss = () => {
  visible.value = false;
};

const accent = computed(() => ({
  success: { border: '#22c55e', bg: 'rgba(34,197,94,0.1)', dot: '#22c55e' },
  error: { border: '#ef4444', bg: 'rgba(239,68,68,0.1)', dot: '#ef4444' },
  status: { border: '#DCC175', bg: 'rgba(220,193,117,0.1)', dot: '#DCC175' },
}[kind.value ?? 'status']));
</script>

<template>
  <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
    <div v-if="visible && kind"
      class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[200] w-[calc(100%-2rem)] max-w-[420px] flex items-start gap-3 px-5 py-4"
      :style="{
        background: '#13101e',
        border: `1px solid ${accent.border}40`,
        borderRadius: '8px',
        boxShadow: '0 8px 28px rgba(0,0,0,0.45)',
      }">
      <span class="w-2 h-2 mt-1.5 rounded-full shrink-0" :style="{ background: accent.dot }" />
      <p class="text-sm text-white/90 flex-1" :style="{ fontFamily: 'Jost, sans-serif' }">
        {{ message }}
      </p>
      <button type="button" @click="dismiss" aria-label="Dismiss"
        class="text-white/40 hover:text-white/80 text-lg leading-none shrink-0">
        &times;
      </button>
    </div>
  </transition>
</template>
