<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';
import Orbs from '@/Components/Layout/Orbs.vue';
import Hero from '@/Components/CardLists/Hero.vue';
import type { CardListBatch } from '../../types';

interface Props {
  batches: CardListBatch[];
}

defineProps<Props>();
</script>

<template>
  <Head title="Card Lists" />

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
        <Hero :batches="batches" />
      </div>
    </div>
  </main>

  <Footer />
</template>
