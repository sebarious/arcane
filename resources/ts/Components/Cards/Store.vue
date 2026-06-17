<template>
  <div class="outline-root relativ">
    <div class="card-panel flex items-center gap-5 outline-inner py-5">
      <span></span>
      <div
        class="w-24 h-24 rounded-lg overflow-hidden border border-arcane-border/60 bg-arcane-surface/80 flex items-center p-2">
        <template v-if=" store.logo ">
          <img :src="store.logo" alt="" class="h-full w-full object-contain" loading="lazy" />
        </template>
        <template v-else>
          <img src="/images/logo.png" alt="Arcane" class="h-12 mx-auto" />
        </template>
      </div>
      <div class="w-full">
        <div class="text-sm font-semibold truncate">
          {{ store.name }}
        </div>
        <div class="text-xs text-arcane-muted truncate mt-2">
          <span class="text-arcane-accent2 hover:underline">
            Visit store
          </span>
        </div>
      </div>
    </div>
    <Link :href="`/${store.slug}`" class="absolute inset-0">
    </Link>
  </div>
</template>

<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';

interface Store {
  id: number;
  name: string;
  slug: string;
  logo?: string | null;
}

const props = defineProps<{
  store: Store;
}>();

</script>