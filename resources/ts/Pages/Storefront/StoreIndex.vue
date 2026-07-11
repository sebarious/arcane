<script setup lang="ts">
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';
import Orbs from '@/Components/Layout/Orbs.vue';
import LivePool from '@/Components/LivePool.vue';
import Hero from '@/Components/Stores/Hero.vue';
import type { Store, Pull } from '../../types';

interface Props {
  stores: Store[];
  whatsInThePool: Pull[];
}

const props = defineProps<Props>();
</script>

<template>
  <main class="bg-[#06060b] content-stretch flex flex-col items-start relative size-full overflow-x-hidden">
    <Orbs />

    <div class="relative shrink-0">
      <div
        class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-[64px] py-[20px] relative size-full">
        <div class="h-[49px] relative shrink-0">
          <Nav />
        </div>
      </div>
    </div>

    <div class="relative shrink-0 w-full">
      <div
        class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start relative size-full">
        <Hero :stores="stores" />
        <LivePool :pulls="whatsInThePool" />
      </div>
    </div>
  </main>

  <Footer />
</template>