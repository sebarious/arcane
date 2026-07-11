<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Nav from '@/Components/Layout/Nav.vue';
import imgBanner from "@/Assets/a4ced2700801f0662d08b119fe0b16c8c188c4e2.png";
import Discord from '@/Components/Icons/Logos/Discord.vue';
import Facebook from '@/Components/Icons/Logos/Facebook.vue';
import Instagram from '@/Components/Icons/Logos/Instagram.vue';
import Twitter from '@/Components/Icons/Logos/Twitter.vue';
import Youtube from '@/Components/Icons/Logos/Youtube.vue';
import TikTok from '@/Components/Icons/Logos/TikTok.vue';
import { MapPinIcon } from '@heroicons/vue/24/outline';
import { ShieldCheck, Globe } from 'lucide-vue-next';
import PullsSlider from '@/Components/PullsSlider.vue';
import Footer from '@/Components/Layout/Footer.vue';

type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic';

interface Store {
  id: number;
  slug: string;
  name: string;
  city: string;
  postcode: string;
  logo: string;
  location: string;
  social_links: any;
  description: string;
  total_batches: number;
  total_packs_remaining: number;
  total_pull_count: number;
  created_at: string;
}

interface Batch {
  id: number;
  reference: string;
  type: string | null;
  created_at: string | null;
  pack_count: number;
  game: string | null;
  game_label: string | null;
  remaining_packs: number;
}

interface PullCard {
  name: string | null;
  set: string | null;
  number: string | null;
  image: string | null;
  band: Rarity | null;
}

interface RecentPull {
  id: number;
  sequence: number;
  sold_at: string | null;
  batch: { id: number; reference: string | null; };
  card: PullCard | null;
}

interface Props {
  store: Store;
  batches: Batch[];
  recentPulls: RecentPull[];
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
  <main class="bg-[#06060b] overflow-x-hidden">
    <div class="relative shrink-0">
      <div
        class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-[64px] py-[20px] relative size-full">
        <div class="h-[49px] relative shrink-0">
          <Nav />
        </div>
      </div>
    </div>

    <div class="content-stretch flex flex-col items-start relative shrink-0 w-full">
      <div class="content-stretch flex flex-col h-[408px] items-start overflow-clip relative shrink-0 w-full">
        <div class="content-stretch flex h-[200px] lg:h-[340px] items-start relative shrink-0 w-full">
          <div aria-hidden class="absolute inset-0 pointer-events-none">
            <img alt="Hero banner" class="absolute max-w-none object-cover size-full" :src="imgBanner" loading="lazy" />
            <div class="absolute bg-[rgba(13,11,20,0.4)] inset-0" />
          </div>
        </div>
        <div
          class="absolute content-stretch lg:flex gap-[24px] items-start left-0 px-[64px] right-0 top-[150px] lg:top-[272px] space-y-6">
          <div class="relative rounded-[50px] shrink-0 size-[100px] bg-black">
            <img :alt="store.name"
              class="absolute inset-0 max-w-none object-cover pointer-events-none rounded-[50px] size-full"
              :src="store.logo" />
          </div>
          <div class="content-stretch flex flex-col gap-[12px] items-start pb-[20px] relative shrink-0">
            <p
              class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[36px] lg:text-[48px] text-white lg:whitespace-nowrap">
              {{ store.name }}</p>
            <div class="content-stretch flex gap-[24px] items-center relative shrink-0">
              <div v-if="store.location" class="content-stretch flex gap-[6px] items-center relative shrink-0">
                <div class="relative shrink-0 size-[16px]">
                  <MapPinIcon class="size-5 text-[#a3a3a3]" />
                </div>
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[14px] whitespace-nowrap">
                  {{ store.location }}</p>
              </div>
              <div class="content-stretch flex gap-[16px] items-center relative shrink-0">
                <div class="relative shrink-0 size-[18px]">
                  <a :href="store?.social_links?.facebook" rel="noopener noreferrer" target="_blank">
                    <Facebook class="size-5" />
                  </a>
                </div>
                <div class="relative shrink-0 size-[18px]" v-if="store?.social_links?.instagram">
                  <a :href="store?.social_links?.instagram" rel="noopener noreferrer" target="_blank">
                    <Instagram class="size-5" />
                  </a>
                </div>
                <div class="relative shrink-0 size-[18px]" v-if="store?.social_links?.youtube">
                  <a :href="store?.social_links?.youtube" rel="noopener noreferrer" target="_blank">
                    <Youtube class="size-5" />
                  </a>
                </div>
                <div class="relative shrink-0 size-[18px]" v-if="store?.social_links?.x">
                  <a :href="store?.social_links?.x" rel="noopener noreferrer" target="_blank">
                    <Twitter class="size-5" />
                  </a>
                </div>
                <div class="relative shrink-0 size-[18px]" v-if="store?.social_links?.tiktok">
                  <a :href="store?.social_links?.tiktok" rel="noopener noreferrer" target="_blank">
                    <TikTok class="size-5" />
                  </a>
                </div>
                <div class="relative shrink-0 size-[18px]" v-if="store?.social_links?.discord">
                  <a :href="store?.social_links?.discord" rel="noopener noreferrer" target="_blank">
                    <Discord class="size-5" />
                  </a>
                </div>
                <div class="relative shrink-0 size-[18px]" v-if="store?.social_links?.website">
                  <a :href="store?.social_links?.website" rel="noopener noreferrer" target="_blank">
                    <Globe class="size-5" />
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="relative w-full">
        <div
          class="content-stretch space-y-8 lg:space-y-0 lg:flex gap-[32px] items-start px-[64px] py-[40px] relative size-full">
          <div class="content-stretch flex flex-col gap-[32px] items-start relative w-full">
            <div
              class="[word-break:break-word] content-stretch flex flex-col gap-[16px] items-start relative shrink-0 max-w-xl">
              <p
                class="font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[24px] text-white whitespace-nowrap">
                About This Store</p>
              <p v-if="store?.description"
                class="font-['Jost',sans-serif] font-normal leading-[26px] min-w-full relative shrink-0 text-[#a3a3a3] text-[16px] ">
                {{ store?.description }}</p>
            </div>
            <div class="content-stretch flex gap-[12px] items-start relative shrink-0">
              <div
                class="bg-[rgba(201,168,76,0.1)] content-stretch flex gap-[8px] items-center px-[12px] py-[6px] relative rounded-[4px] shrink-0">
                <div aria-hidden
                  class="absolute border border-[rgba(201,168,76,0.25)] border-solid inset-0 pointer-events-none rounded-[4px]" />
                <ShieldCheck class="size-4 text-[#c9a84c]" />
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#c9a84c] text-[12px] uppercase whitespace-nowrap">
                  Verified Seller</p>
              </div>
            </div>
          </div>
          <div
            class="bg-[#13101e] content-stretch flex flex-col gap-[24px] items-start p-[24px] relative rounded-[12px] w-full">
            <div aria-hidden
              class="absolute border border-[rgba(220,193,117,0.1)] border-solid inset-0 pointer-events-none rounded-[12px]" />
            <div
              class="[word-break:break-word] content-stretch flex gap-[24px] items-start leading-[normal] relative shrink-0 w-full whitespace-nowrap">
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[4px] items-start min-w-px relative">
                <p
                  class="font-['Jost',sans-serif] font-normal relative shrink-0 text-[12px] text-[rgba(255,255,255,0.35)] uppercase">
                  Active Batches</p>
                <p class="font-['Cinzel',sans-serif] font-bold relative shrink-0 text-[28px] text-white">{{
                  store?.total_batches ?? 0 }}</p>
              </div>
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[4px] items-start min-w-px relative">
                <p
                  class="font-['Jost',sans-serif] font-normal relative shrink-0 text-[12px] text-[rgba(255,255,255,0.35)] uppercase">
                  Packs Remaining</p>
                <p class="font-['Cinzel',sans-serif] font-bold relative shrink-0 text-[28px] text-white">{{
                  store?.total_packs_remaining ?? 0 }}
                </p>
              </div>
            </div>
            <div class="h-0 relative shrink-0 w-full">
              <div class="absolute inset-[-1px_0_0_0]">
                <svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 480 1">
                  <line id="Line" stroke="var(--stroke-0, #DCC175)" strokeOpacity="0.101961" x2="480" y1="0.5"
                    y2="0.5" />
                </svg>
              </div>
            </div>
            <div
              class="[word-break:break-word] content-stretch flex gap-[24px] items-start leading-[normal] relative shrink-0 w-full whitespace-nowrap">
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[4px] items-start min-w-px relative">
                <p
                  class="font-['Jost',sans-serif] font-normal relative shrink-0 text-[12px] text-[rgba(255,255,255,0.35)] uppercase">
                  Cards Pulled</p>
                <p class="font-['Cinzel',sans-serif] font-bold relative shrink-0 text-[28px] text-white">{{
                  store?.total_pull_count ?? 0 }}
                </p>
              </div>
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[4px] items-start min-w-px relative">
                <p
                  class="font-['Jost',sans-serif] font-normal relative shrink-0 text-[12px] text-[rgba(255,255,255,0.35)] uppercase">
                  Customer since</p>
                <p class="font-['Cinzel',sans-serif] font-bold relative shrink-0 text-[28px] text-white">{{
                  store?.created_at }}</p>

              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="relative shrink-0 w-full">
        <div class="content-stretch flex flex-col gap-[24px] items-start px-[64px] py-[40px] relative size-full">
          <div class="content-stretch flex flex-col items-start relative w-full">
            <div class="content-stretch flex items-center relative shrink-0">
              <h2
                class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[32px] text-white whitespace-nowrap">
                Active Batches</h2>
            </div>
          </div>
          <div class="bg-[#13101e] relative rounded-[8px] w-full">
            <div aria-hidden
              class="absolute border border-[rgba(220,193,117,0.1)] border-solid inset-0 pointer-events-none rounded-[8px]" />
            <div class="flex flex-row items-center size-full">
              <template v-for="batch in batches" :key="batch.reference">
                <div class="content-stretch flex items-center justify-between p-[20px] relative size-full">
                  <div class="content-stretch space-y-5 lg:space-y-0 lg:flex gap-[32px] items-center relative">
                    <div
                      class="[word-break:break-word] content-stretch lg:flex flex-col gap-[4px] items-start leading-[normal] relative lg:whitespace-nowrap">
                      <p
                        class="font-['Jost',sans-serif] font-normal relative shrink-0 text-[12px] text-[rgba(255,255,255,0.35)] uppercase">
                        Batch Ref</p>
                      <p class="font-['Jost',sans-serif] font-semibold relative shrink-0 text-[16px] text-white">
                        {{ batch.reference }}</p>
                    </div>
                    <div class="content-stretch flex gap-[12px] items-start relative shrink-0">
                      <div
                        class="bg-[rgba(123,79,233,0.1)] content-stretch flex items-center px-[12px] py-[6px] relative rounded-[4px] shrink-0"
                        data-name="Frame">
                        <div aria-hidden
                          class="absolute border border-[rgba(123,79,233,0.25)] border-solid inset-0 pointer-events-none rounded-[4px]" />
                        <p
                          class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[12px] uppercase whitespace-nowrap">
                          {{ batch.type  }}</p>
                      </div>
                      <div
                        class="bg-[rgba(255,255,255,0.04)] content-stretch flex items-center px-[12px] py-[6px] relative rounded-[4px] shrink-0">
                        <div aria-hidden
                          class="absolute border border-[rgba(255,255,255,0.1)] border-solid inset-0 pointer-events-none rounded-[4px]" />
                        <p
                          class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#a3a3a3] text-[12px] uppercase whitespace-nowrap">
                          {{ batch.game_label }}</p>
                      </div>
                    </div>
                    <div
                      class="[word-break:break-word] content-stretch flex flex-col font-['Jost',sans-serif] font-normal gap-[4px] items-start leading-[normal] relative shrink-0 whitespace-nowrap">
                      <p class="relative shrink-0 text-[12px] text-[rgba(255,255,255,0.35)] uppercase">Created</p>
                      <p class="relative shrink-0 text-[14px] text-white">{{ batch.created_at }}</p>
                    </div>
                    <div
                      class="[word-break:break-word] content-stretch flex flex-col font-['Jost',sans-serif] font-normal gap-[4px] items-start leading-[normal] relative shrink-0 whitespace-nowrap">
                      <p class="relative shrink-0 text-[12px] text-[rgba(255,255,255,0.35)] uppercase">Availability
                      </p>
                      <p class="relative shrink-0 text-[14px] text-white">{{ batch.remaining_packs }} packs Left</p>
                    </div>
                  </div>
                  <div
                    class="bg-[#7b4fe9] content-stretch drop-shadow-[0px_0px_8px_rgba(123,79,233,0.5)] flex items-start px-[24px] py-[12px] relative rounded-[4px] shrink-0">
                    <p
                      class="[word-break:break-word] font-['Jost',sans-serif] font-bold leading-[normal] relative shrink-0 text-[14px] text-white uppercase whitespace-nowrap">
                      <a :href="`${store.slug}/${batch.id}`">Explore</a></p>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <PullsSlider :pulls="(recentPulls as any[])" />
  </main>

  <Footer />
</template>