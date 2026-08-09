<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types/global';

const page = usePage<PageProps>();

const impersonating = computed(() => !!page.props?.impersonating);
const userName = computed(() => page.props?.auth?.user?.name);

// A native form submit, not router.post(): the stop endpoint redirects to
// /admin, a Filament (non-Inertia) route. Routing that through Inertia's
// client relies on it detecting the missing X-Inertia header and falling
// back to a full navigation — a plain form sidesteps that entirely and just
// always does a real page load, which is what leaving the SPA needs anyway.
const csrfToken =
  typeof document !== 'undefined'
    ? (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '')
    : '';
</script>

<template>
  <div v-if="impersonating"
    class="fixed inset-x-0 top-0 z-[999] w-full bg-[#c9a84c] text-[#0d0b14] font-['Jost',sans-serif]">
    <form method="POST" action="/admin/impersonate/stop"
      class="flex items-center justify-center gap-3 px-4 py-2 text-sm">
      <input type="hidden" name="_token" :value="csrfToken" />
      <span class="font-semibold">Viewing as {{ userName }}</span>
      <button type="submit"
        class="font-semibold uppercase tracking-wide underline underline-offset-2 hover:opacity-70 transition-opacity">
        Stop impersonating
      </button>
    </form>
  </div>
</template>
