<script setup lang="ts">
import ImpersonationBanner from '@/Components/ImpersonationBanner.vue';
import FlashToast from '@/Components/FlashToast.vue';

defineProps<{ appComponent: unknown; appProps: object }>();
</script>

<template>
  <!-- Must render after appComponent: Inertia's App component (appComponent)
       populates its internal page store in its own setup(), and usePage() in
       ImpersonationBanner/FlashToast reads that store — mounting first would
       read it before it's populated. Fixed positioning keeps both visually
       pinned regardless of this DOM order. -->
  <component :is="appComponent" v-bind="appProps" />
  <ImpersonationBanner />
  <FlashToast />
</template>
