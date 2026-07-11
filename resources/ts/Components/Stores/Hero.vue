<template>
  <div class="lg:h-[641px] lg:max-h-[704.7000122070312px] relative shrink-0 w-full">
    <div class="bg-clip-padding border-0 border-[transparent] border-solid relative size-full">
      <HeroBG />
      <HeroSparkles />
      <FloatingRings />
      <div class="absolute content-stretch flex flex-col h-[40px] items-start left-[535px] top-[703px] w-px">
        <div
          class="absolute bg-gradient-to-b from-[rgba(255,185,0,0.35)] h-[5.363px] left-0 to-[rgba(0,0,0,0)] top-0 w-px" />
      </div>
      <div
        class="lg:absolute content-stretch flex flex-col gap-[24px] lg:h-[326px] items-center justify-center left-0 pb-[48px] pt-[80px] px-8 lg:px-16 top-[-15px] w-full">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-start relative shrink-0 w-full">
          <h1
            class="font-['Cinzel',sans-serif] font-bold leading-[0] min-w-full relative shrink-0 text-[64px] text-white w-[min-content]">
            <span class="leading-[normal]">Browse </span>
            <span class="bg-clip-text leading-[normal] text-[transparent]"
              :style="{ backgroundImage: 'linear-gradient(90deg, rgb(76, 29, 149) 0%, rgb(124, 58, 237) 14.286%, rgb(168, 85, 247) 28.571%, rgb(192, 132, 252) 42.857%, rgb(221, 214, 254) 57.143%, rgb(168, 85, 247) 71.429%, rgb(124, 58, 237) 85.714%, rgb(76, 29, 149) 100%), linear-gradient(90deg, rgb(255, 255, 255) 0%, rgb(255, 255, 255) 100%)' }">
              Stores
            </span>
          </h1>
          <p
            class="font-['Jost',sans-serif] font-normal leading-[normal] opacity-60 relative shrink-0 text-[#a3a3a3] text-[18px] whitespace-nowrap">
            Find your next pull from verified local stores.</p>
        </div>
      </div>
      <div
        class="lg:absolute content-stretch flex flex-col gap-[32px] lg:h-[378px] items-center justify-center left-0 pb-[80px] px-8 lg:px-16 top-[311px] w-full">
        <div
          class="content-stretch space-y-8 lg:space-y-0 lg:flex lg:gap-[24px] lg:items-start relative lg:shrink-0 w-full">
          <Card v-for=" store in stores " :key="store.id" :store="store" />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import HeroBG from '@/Components/HeroBG.vue';
import HeroSparkles from '@/Components/HeroSparkles.vue';
import FloatingRings from '@/Components/FloatingRings.vue';
import svgPaths from '@/Components/Stores/svg';
import Card from '@/Components/Stores/Card.vue';
import type { Store } from '../../types';

const props = defineProps<{
  stores: Store[];
}>();
</script>