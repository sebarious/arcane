<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';

const props = defineProps<{ status: number }>();

const COPY: Record<number, { title: string; message: string }> = {
  403: {
    title: 'Access denied',
    message: 'You don\'t have permission to view this page — if that seems wrong, get in touch and we\'ll take a look.',
  },
  404: {
    title: 'Page not found',
    message: 'The page you\'re looking for doesn\'t exist, or may have moved.',
  },
  500: {
    title: 'Something went wrong',
    message: 'An unexpected error happened on our end. We\'ve been notified and are already looking into it.',
  },
  503: {
    title: 'Down for maintenance',
    message: 'We\'re making some improvements behind the scenes. Please check back shortly.',
  },
};

const copy = computed(() => COPY[props.status] ?? {
  title: 'Unexpected error',
  message: 'Something didn\'t go to plan. Please try again.',
});
</script>

<template>
  <Head :title="`${status} — ${copy.title}`" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen flex flex-col">
    <div class="relative shrink-0">
      <div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full">
        <div class="h-[49px] relative shrink-0 w-full">
          <Nav />
        </div>
      </div>
    </div>

    <div class="flex-1 flex items-center justify-center px-8 lg:px-[64px] py-[80px]">
      <div class="max-w-xl text-center">
        <p class="font-['Cinzel',sans-serif] font-bold text-[100px] lg:text-[140px] leading-none bg-clip-text text-transparent"
          style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
          {{ status }}
        </p>
        <p class="font-['Cinzel',sans-serif] font-bold text-[28px] lg:text-[34px] text-white mt-[8px]">
          {{ copy.title }}
        </p>
        <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[16px] mt-[16px] leading-relaxed">
          {{ copy.message }}
        </p>

        <div class="flex items-center justify-center gap-[12px] mt-[36px]">
          <Link href="/"
            class="px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14]"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            Take me home
          </Link>
          <a href="mailto:support@arcanepacks.com"
            class="px-6 py-3 rounded-[4px] border border-[#3d2f6e] text-white text-sm font-['Jost',sans-serif] font-semibold uppercase tracking-wide hover:border-[#c9a84c] transition-colors">
            Contact support
          </a>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>
