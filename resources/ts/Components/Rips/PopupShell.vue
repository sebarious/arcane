<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

defineProps<{ closeHref: string }>();
</script>

<template>
  <div class="fixed inset-0 z-[150] flex items-center justify-center p-4 sm:p-8"
    :style="{ background: 'rgba(6,6,11,0.92)', backdropFilter: 'blur(10px)' }">
    <Link :href="closeHref" class="absolute top-5 right-5 sm:top-8 sm:right-8 text-white/50 hover:text-white transition-colors z-10" aria-label="Close">
      <X :size="26" />
    </Link>

    <!-- overflow-x must be explicitly hidden, not just left unset — per the
    CSS spec, setting only overflow-y to a non-visible value makes the
    browser compute overflow-x as auto too, so anything that briefly extends
    sideways (the fanned pack stack, or a tear-open half flying off) was
    triggering a horizontal scrollbar here as well. -->
    <div class="relative w-full max-w-2xl max-h-[92vh] overflow-y-auto overflow-x-hidden">
      <slot />
    </div>
  </div>
</template>
