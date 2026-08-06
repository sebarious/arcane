<template>
  <div class="relative shrink-0 w-full">
    <div class="bg-clip-padding border-0 border-[transparent] border-solid relative size-full">
      <HeroBG />
      <HeroSparkles />
      <FloatingRings />
      <div
        class="lg:absolute content-stretch flex flex-col gap-[24px] lg:h-[326px] items-center justify-center left-0 pb-[48px] pt-[80px] px-8 lg:px-16 top-[-15px] w-full">
        <div class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-start relative shrink-0 w-full">
          <h1 class="font-['Cinzel',sans-serif] font-bold leading-[0] min-w-full relative shrink-0 text-[64px] text-white w-[min-content]">
            <span class="leading-[normal]">Browse </span>
            <span class="bg-clip-text leading-[normal] text-[transparent]"
              :style="{ backgroundImage: 'linear-gradient(90deg, rgb(76, 29, 149) 0%, rgb(124, 58, 237) 14.286%, rgb(168, 85, 247) 28.571%, rgb(192, 132, 252) 42.857%, rgb(221, 214, 254) 57.143%, rgb(168, 85, 247) 71.429%, rgb(124, 58, 237) 85.714%, rgb(76, 29, 149) 100%), linear-gradient(90deg, rgb(255, 255, 255) 0%, rgb(255, 255, 255) 100%)' }">
              Card Lists
            </span>
          </h1>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] opacity-60 relative shrink-0 text-[#a3a3a3] text-[18px]">
            Every mystery pack batch on offer, across every store, in one place.</p>
        </div>
      </div>
      <div class="relative flex flex-col gap-[32px] items-center justify-center pb-[80px] lg:pt-[311px] lg:pt-[380px] px-8 lg:px-16 w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
          <Card v-for="batch in batches" :key="batch.id" :batch="batch" />
        </div>
        <p v-if="batches.length === 0" class="font-['Jost',sans-serif] text-[#a3a3a3] text-sm py-12">
          No batches are on offer right now — check back soon.
        </p>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import HeroBG from '@/Components/HeroBG.vue';
import HeroSparkles from '@/Components/HeroSparkles.vue';
import FloatingRings from '@/Components/FloatingRings.vue';
import Card from '@/Components/CardLists/Card.vue';
import type { CardListBatch } from '../../types';

defineProps<{
  batches: CardListBatch[];
}>();
</script>
