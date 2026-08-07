<template>
  <footer class="border-t border-[#DCC175]/8 px-8 lg:px-16 pt-14 pb-8">
    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-10">
        <div class="col-span-2 sm:col-span-1">
          <div class="flex items-center gap-2.5">
            <img :src="arcaneLogo" alt="Arcane" class="h-8 w-auto" loading="lazy" />
            <span class="text-xs tracking-[0.25em] text-[#DCC175]/60 uppercase"
              :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
              Arcane
            </span>
          </div>
          <p class="font-['Jost',sans-serif] text-[13px] text-[#a3a3a3] leading-relaxed mt-[16px] max-w-[220px]">
            Authenticated, near-mint Pokémon singles sealed into mystery packs — live pull odds, zero guesswork.
          </p>

          <div class="mt-4">
            <a href="/verified"
              class="bg-[rgba(34,197,94,0.08)] content-stretch inline-flex items-center gap-[6px] px-[12px] py-[6px] relative rounded-[4px] shrink-0 hover:bg-[rgba(34,197,94,0.14)] transition-colors">
              <div
                class="absolute border border-[rgba(34,197,94,0.25)] border-solid inset-0 pointer-events-none rounded-[4px]">
              </div>
              <p
                class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#22c55e] text-[11px] uppercase whitespace-nowrap">
                Randomised · SHA-256 · Verifiable</p>
            </a>
          </div>
        </div>

        <div v-for=" column in columns " :key=" column.title ">
          <p
            class="font-['Cinzel',sans-serif] font-bold text-[12px] tracking-[0.15em] text-[#DCC175]/70 uppercase mb-[16px]">
            {{ column.title }}
          </p>
          <ul class="flex flex-col gap-[10px]">
            <li v-for=" link in column.links " :key=" link.label ">
              <Link :href=" link.href "
                class="font-['Jost',sans-serif] text-[13px] text-[#a3a3a3] hover:text-[#DCC175] transition-colors">
              {{ link.label }}
              </Link>
            </li>
          </ul>
        </div>
      </div>

      <div
        class="mt-[48px] pt-[24px] border-t border-[#DCC175]/8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <span class="text-[10px] text-[#DCC175]/70 tracking-widest text-center"
          :style="{ fontFamily: 'Jost, sans-serif' }">
          © {{ new Date().getFullYear() }} Arcane. All cards authenticated. All packs sealed fresh.
        </span>
        <div class="flex gap-[20px]">
          <Link href="/terms" class="text-[10px] text-[#a3a3a3] hover:text-[#DCC175] tracking-widest uppercase"
            :style="{ fontFamily: 'Jost, sans-serif' }">
          Terms
          </Link>
          <Link href="/privacy" class="text-[10px] text-[#a3a3a3] hover:text-[#DCC175] tracking-widest uppercase"
            :style="{ fontFamily: 'Jost, sans-serif' }">
          Privacy
          </Link>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import arcaneLogo from '@/Assets/Link___Arcane.png';

const columns = [
  {
    title: 'Explore',
    links: [
      { label: 'Stores', href: '/stores' },
      { label: 'Sell to Us', href: '/sell' },
      { label: 'Become a Seller', href: '/apply' },
    ],
  },
  {
    title: 'Program',
    links: [
      { label: 'Affiliate Program', href: '/affiliate-program' },
    ],
  },
  {
    title: 'Legal',
    links: [
      { label: 'Terms & Conditions', href: '/terms' },
      { label: 'Privacy Policy', href: '/privacy' },
    ],
  },
];
</script>
