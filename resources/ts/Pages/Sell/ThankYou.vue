<script setup lang="ts">
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

interface Props {
  reference: string;
}
const props = defineProps<Props>();

const generalMotion = {
  initial: { opacity: 0, y: 18 },
  enter: {
    opacity: 1,
    y: 0,
    transition: { delay: 350, duration: 900 },
  },
};
</script>

<template>
  <main class="bg-[#0d0b14] overflow-x-hidden">
    <div class="relative shrink-0">
      <div
        class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full">
        <div class="h-[49px] relative shrink-0">
          <Nav />
        </div>
      </div>
    </div>

    <div class="relative shrink-0 w-full" v-motion="generalMotion">
      <div
        class="content-stretch flex flex-col gap-[56px] items-start justify-center pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center relative shrink-0 text-center mx-auto">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Thank</span>
            <span class="leading-[normal] text-[#c9a84c]"> you</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px] max-w-xl">
            We've got your submission. Your reference is <strong>{{ reference }}</strong>. We’ll review your photos and
            get back to you by email with an offer. You don’t need to send anything yet – we’ll confirm the details first.</p>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>