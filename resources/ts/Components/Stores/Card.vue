<template>
  <div class="bg-[#13101e] flex-[1_0_0] min-w-px relative rounded-[8px]">
    <div class="content-stretch flex flex-col items-start overflow-clip relative rounded-[inherit] size-full">
      <div class="content-stretch flex h-[151px] items-center justify-center overflow-clip relative shrink-0 w-full"
        :style="{
          backgroundImage: 'linear-gradient(90deg, rgba(123, 79, 233, 0.2) 0%, rgba(0, 0, 0, 0) 100%), linear-gradient(90deg, rgb(6, 6, 11) 0%, rgb(6, 6, 11) 100%)'
        }">
        <div class="h-[151px] relative shrink-0 w-[270px]">
          <svg class="absolute block inset-0 size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 270 151">
            <g id="atom" opacity="0.1">
              <path :d="svgPaths.p14ec8e80" id="Vector" stroke="var(--stroke-0, #7B4FE9)" strokeLinecap="round"
                strokeWidth="2" />
            </g>
          </svg>
        </div>
        <div class="-translate-x-1/2 -translate-y-1/2 absolute blur-[20px] left-1/2 size-[80px] top-1/2"
          :style="blurStyle" />
        <template v-if=" store.logo ">
          <img :src="store.logo" :alt="store.name"
            class="-translate-x-1/2 -translate-y-1/2 absolute left-1/2 size-[100px] top-1/2"
            loading="lazy" />
        </template>
      </div>
      <div class="relative shrink-0 w-full">
        <div class="content-stretch flex flex-col gap-[12px] items-start p-[20px] relative size-full">
          <div class="content-stretch flex flex-col items-start relative shrink-0 w-full">
            <p
              class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[18px] text-white w-full">
              {{ store.name }}</p>
          </div>
          <div class="content-stretch flex items-center justify-between relative shrink-0 w-full">
            <Link :href="`/${store.slug}`" class="content-stretch flex gap-[4px] items-center relative shrink-0">
            <p
              class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[11px] uppercase whitespace-nowrap">
              Visit Store</p>
            <div class="relative shrink-0 size-[12px]">
              <svg class="absolute block inset-0 size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 12 12">
                <g id="arrow-right">
                  <path :d="svgPaths.p278a3600" id="Vector" stroke="var(--stroke-0, #7B4FE9)" strokeLinecap="round"
                    strokeWidth="2" />
                </g>
              </svg>
            </div>
            </Link>
          </div>
        </div>
      </div>
    </div>
    <div aria-hidden
      class="absolute border border-[rgba(220,193,117,0.1)] border-solid inset-0 pointer-events-none rounded-[8px]" />
  </div>
</template>

<script lang="ts" setup>
import type { Store } from '../../types';
import svgPaths from '@/Components/Stores/svg';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  store: Store;
}>();

const blurBg = encodeURIComponent(`
  <svg viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
    <rect x="0" y="0" height="100%" width="100%" fill="url(#grad)" opacity="1" />
    <defs>
      <radialGradient id="grad" gradientUnits="userSpaceOnUse" cx="0" cy="0" r="10"
        gradientTransform="matrix(0 -4 4 0 40 40)">
        <stop stop-color="rgba(123,79,233,0.25098)" offset="0" />
        <stop stop-color="rgba(62,40,117,0.12549)" offset="0.4" />
        <stop stop-color="rgba(0,0,0,0)" offset="0.8" />
      </radialGradient>
    </defs>
  </svg>
`);
const blurStyle = {
  backgroundImage: `url("data:image/svg+xml,${blurBg}")`,
};
</script>