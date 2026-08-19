<template>
  <div class="bg-[#13101e] relative rounded-[8px]">
    <div class="content-stretch flex flex-col items-start overflow-clip relative rounded-[inherit] size-full">
      <div class="flex h-[190px] items-center justify-center overflow-clip relative shrink-0 w-full" :style="{
        backgroundImage: 'linear-gradient(90deg, rgba(123, 79, 233, 0.2) 0%, rgba(0, 0, 0, 0) 100%), linear-gradient(90deg, rgb(6, 6, 11) 0%, rgb(6, 6, 11) 100%)'
      }">
        <div class="-translate-x-1/2 -translate-y-1/2 absolute blur-[24px] left-1/2 size-[100px] top-1/2"
          :style="blurStyle" />
        <img v-if="batch.store.logo" :src="batch.store.logo" :alt="batch.store.name"
          class="absolute left-3 top-3 size-[50px] rounded-[6px] bg-[#06060b] object-contain p-0.5 border border-[rgba(220,193,117,0.15)]"
          loading="lazy" />
        <PackThumb :top-card-image1="batch.top_card_1_image" :top-card-image2="batch.top_card_2_image" />
      </div>
      <div class="relative shrink-0 w-full">
        <div class="content-stretch flex flex-col gap-[12px] items-start p-[20px] relative size-full">
          <div class="content-stretch flex flex-col items-start relative shrink-0 w-full gap-1">
            <div class="content-stretch flex flex-wrap items-center gap-[6px] relative shrink-0">
              <span v-if="batch.game_label"
                class="font-['Jost',sans-serif] font-semibold text-[10px] uppercase tracking-wide text-[#c9a84c] bg-[rgba(201,168,76,0.1)] border border-[rgba(201,168,76,0.25)] rounded-full px-2 py-[2px]">
                {{ batch.game_label }}</span>
              <span
                class="font-['Jost',sans-serif] font-semibold text-[10px] uppercase tracking-wide text-[#a3a3a3] bg-[rgba(255,255,255,0.04)] border border-[rgba(255,255,255,0.1)] rounded-full px-2 py-[2px]">
                1 Raw Card</span>
            </div>
            <p class="font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[18px] text-white w-full">
              Singles Pack &mdash; {{ batch.type_label }}</p>
            <p class="font-['Jost',sans-serif] text-xs text-[#a3a3a3]">
              {{ batch.store.name }} · {{ batch.remaining_packs }}/{{ batch.pack_count }} packs left</p>
          </div>
          <div class="content-stretch flex items-center justify-between relative shrink-0 w-full">
            <Link :href="`/${batch.store.slug}/${batch.id}`" class="content-stretch flex gap-[4px] items-center relative shrink-0">
              <p class="font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[11px] uppercase whitespace-nowrap">
                View Cards</p>
              <div class="relative shrink-0 size-[12px]">
                <svg class="absolute block inset-0 size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 12 12">
                  <g id="arrow-right">
                    <path :d="svgPaths.p278a3600" id="Vector" stroke="var(--stroke-0, #7B4FE9)" stroke-linecap="round"
                      stroke-width="2" />
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
import type { CardListBatch } from '../../types';
import svgPaths from '@/Components/Stores/svg';
import { Link } from '@inertiajs/vue3';
import PackThumb from './PackThumb.vue';

const props = defineProps<{
  batch: CardListBatch;
}>();

const blurBg = encodeURIComponent(`
  <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
    <rect x="0" y="0" height="100%" width="100%" fill="url(#grad)" opacity="1" />
    <defs>
      <radialGradient id="grad" gradientUnits="userSpaceOnUse" cx="0" cy="0" r="10"
        gradientTransform="matrix(0 -5 5 0 50 50)">
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
