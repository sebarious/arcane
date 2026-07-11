<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

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

    <div class="relative shrink-0 w-full">
      <div
        class="content-stretch flex flex-col gap-[56px] items-start justify-center pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center relative shrink-0 text-center mx-auto">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Thank</span>
            <span class="leading-[normal] text-[#c9a84c]"> you</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]">
            Thanks for applying to become an Arcane seller. We’ll review your details and get back to you.</p>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>