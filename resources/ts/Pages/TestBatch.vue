<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic'

interface BatchMeta {
  type: string | null
  game_label: string | null
  pack_count: number
  generated_at: string | null
}

interface BandCard {
  name: string | null
  set: string | null
  number: string | null
  image: string | null
  band: Rarity | null
  market_price: number | null
  product_badges: string[]
}

interface BandInfo {
  count: number
  cards: BandCard[]
}

interface Props {
  available: boolean
  batch?: BatchMeta
  bands?: Record<Rarity, BandInfo>
}

const props = defineProps<Props>()

const odds = {} as Record<Rarity, number>;
if (props.bands) {
  Object.entries(props.bands).forEach(([band, info]) => {
    odds[band as Rarity] = info.count;
  });
}

const totalOdds = computed(() =>
  (Object.values(odds) as number[]).reduce((sum, x) => sum + x, 0)
)

const ogTitle = 'Diamond Batch Preview | Arcane'
const ogDescription = 'A live preview of what a 500-pack Diamond batch looks like — built from our current real stock and refreshed weekly.'

const bandOrder: { key: Rarity; label: string, colors: Record<string, string> }[] = [
  {
    key: 'mythic',
    label: 'Mythic',
    colors: {
      border: 'rgba(220,193,117,0.1)',
      gradient_from: 'rgba(201,168,76,0.1)',
      text: '#c9a84c',
      background: 'rgba(201,168,76,0.13)',
      inner_border: 'rgba(201,168,76,0.27)',
      shadow: 'rgba(201,168,76,0.13)',
      card_border: 'rgba(201,168,76,0.25)'
    }
  },
  {
    key: 'legendary',
    label: 'Legendary',
    colors: {
      border: 'rgba(220,193,117,0.1)',
      gradient_from: 'rgba(123,79,233,0.1)',
      text: '#7b4fe9',
      background: 'rgba(123,79,233,0.13)',
      inner_border: 'rgba(123,79,233,0.27)',
      shadow: 'rgba(123,79,233,0.13)',
      card_border: 'rgba(123,79,233,0.25)'
    }
  },
  {
    key: 'super',
    label: 'Super',
    colors: {
      border: 'rgba(220,193,117,0.1)',
      gradient_from: 'rgba(45,212,191,0.1)',
      text: '#2dd4bf',
      background: 'rgba(45,212,191,0.13)',
      inner_border: 'rgba(45,212,191,0.27)',
      shadow: 'rgba(45,212,191,0.13)',
      card_border: 'rgba(45,212,191,0.25)'
    }
  },
  {
    key: 'rare',
    label: 'Rare',
    colors: {
      border: 'rgba(220,193,117,0.1)',
      gradient_from: 'rgba(59,130,246,0.1)',
      text: '#3b82f6',
      background: 'rgba(59,130,246,0.13)',
      inner_border: 'rgba(59,130,246,0.27)',
      shadow: 'rgba(59,130,246,0.13)',
      card_border: 'rgba(59,130,246,0.25)'
    }
  },
  {
    key: 'common',
    label: 'Common',
    colors: {
      border: 'rgba(220,193,117,0.1)',
      gradient_from: 'rgba(163,163,163,0.1)',
      text: '#a3a3a3',
      background: 'rgba(163,163,163,0.13)',
      inner_border: 'rgba(163,163,163,0.27)',
      shadow: 'rgba(163,163,163,0.13)',
      card_border: 'rgba(163,163,163,0.25)'
    }
  },
];

const imageLoading = (band: Rarity): 'lazy' | 'eager' =>
  band === 'mythic' || band === 'legendary' ? 'eager' : 'lazy'
</script>

<template>
  <Head>
    <title>{{ ogTitle }}</title>
    <meta property="og:title" :content="ogTitle" />
    <meta property="og:description" :content="ogDescription" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" :content="ogTitle" />
    <meta name="twitter:description" :content="ogDescription" />
  </Head>

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0">
      <div
        class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full">
        <div class="h-[49px] relative shrink-0">
          <Nav />
        </div>
      </div>
    </div>

    <div v-if="!available" class="flex flex-col items-center justify-center text-center px-8 py-[160px]">
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[28px] mb-3">Preview regenerating</p>
      <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[15px] max-w-md">
        The demo batch is being rebuilt from current stock — check back shortly.
      </p>
    </div>

    <template v-else>
      <div class="relative shrink-0 w-full">
        <div class="content-stretch flex flex-col gap-[24px] items-start px-8 lg:px-[64px] py-[40px] relative size-full">
          <div class="content-stretch flex items-center relative shrink-0">
            <p
              class="[word-break:break-word] font-['Jost',sans-serif] font-medium leading-[normal] relative shrink-0 text-[#7b4fe9] text-[14px] whitespace-nowrap">
              <a href="/card-lists" class="hover:underline">
                ← Back to Card Lists
              </a>
            </p>
          </div>
          <div class="content-stretch flex items-end justify-between relative shrink-0 w-full">
            <div class="content-stretch flex flex-col gap-[16px] items-start relative">
              <p
                class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative text-[48px] text-white">
                Diamond Batch Preview</p>
              <div class="content-stretch flex flex-wrap gap-[12px] items-center relative shrink-0">
                <div
                  class="bg-[rgba(123,79,233,0.1)] content-stretch flex items-center px-[12px] py-[6px] relative rounded-[4px] shrink-0">
                  <div aria-hidden
                    class="absolute border border-[rgba(123,79,233,0.25)] border-solid inset-0 pointer-events-none rounded-[4px]" />
                  <p
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[11px] uppercase whitespace-nowrap">
                    {{ batch?.type }}</p>
                </div>
                <div
                  class="bg-[rgba(255,255,255,0.04)] content-stretch flex items-center px-[12px] py-[6px] relative rounded-[4px] shrink-0">
                  <div aria-hidden
                    class="absolute border border-[rgba(255,255,255,0.1)] border-solid inset-0 pointer-events-none rounded-[4px]" />
                  <p
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#a3a3a3] text-[11px] uppercase whitespace-nowrap">
                    {{ batch?.pack_count }} packs</p>
                </div>
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[14px]">
                  Last refreshed {{ batch?.generated_at }}</p>
              </div>
            </div>
          </div>

          <div
            class="bg-[rgba(220,193,117,0.06)] content-stretch flex items-start gap-[10px] px-[16px] py-[14px] relative rounded-[6px] shrink-0 w-full">
            <div aria-hidden
              class="absolute border border-[rgba(220,193,117,0.2)] border-solid inset-0 pointer-events-none rounded-[6px]" />
            <p class="relative font-['Jost',sans-serif] text-[13px] text-[#DCC175]/90 leading-relaxed">
              This is a live preview of a Diamond (500-pack) batch, built from our current real stock and
              refreshed weekly. It isn't a real batch — nothing shown here can be bought.
            </p>
          </div>
        </div>
      </div>

      <div class="relative shrink-0 w-full">
        <div class="content-stretch flex items-start px-8 lg:px-[64px] relative size-full">
          <div class="bg-[#13101e] flex-[1_0_0] min-w-px relative rounded-[12px]">
            <div aria-hidden
              class="absolute border border-[rgba(220,193,117,0.1)] border-solid inset-0 pointer-events-none rounded-[12px]" />
            <div class="content-stretch flex flex-col gap-[20px] items-start p-[24px] relative size-full">
              <div class="content-start flex flex-wrap gap-[12px] items-start relative shrink-0 w-full">
                <div
                  class="content-stretch flex gap-[8px] items-center px-[16px] py-[8px] relative rounded-[40px] shrink-0"
                  :style="{
                    backgroundImage: 'linear-gradient(163.443deg, rgba(201, 168, 76, 0.2) 0%, rgba(201, 168, 76, 0.067) 100%)'
                  }">
                  <div aria-hidden
                    class="absolute border border-[rgba(201,168,76,0.25)] border-solid inset-0 pointer-events-none rounded-[40px]" />
                  <p
                    class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#c9a84c] text-[12px] whitespace-nowrap">
                    MYTHIC</p>
                  <div class="bg-[#c9a84c] opacity-50 relative rounded-[2px] shrink-0 size-[4px]" />
                  <p
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#c9a84c] text-[12px] whitespace-nowrap">
                    {{ ((odds.mythic / totalOdds) * 100).toFixed(1) }}%</p>
                </div>
                <div
                  class="bg-[rgba(123,79,233,0.1)] content-stretch flex gap-[8px] items-center px-[16px] py-[8px] relative rounded-[40px] shrink-0">
                  <div aria-hidden
                    class="absolute border border-[rgba(123,79,233,0.25)] border-solid inset-0 pointer-events-none rounded-[40px]" />
                  <p
                    class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[12px] whitespace-nowrap">
                    LEGENDARY</p>
                  <div class="bg-[#7b4fe9] opacity-50 relative rounded-[2px] shrink-0 size-[4px]" />
                  <p
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[12px] whitespace-nowrap">
                    {{ ((odds.legendary / totalOdds) * 100).toFixed(1) }}%</p>
                </div>
                <div
                  class="bg-[rgba(45,212,191,0.1)] content-stretch flex gap-[8px] items-center px-[16px] py-[8px] relative rounded-[40px] shrink-0">
                  <div aria-hidden
                    class="absolute border border-[rgba(45,212,191,0.25)] border-solid inset-0 pointer-events-none rounded-[40px]" />
                  <p
                    class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#2dd4bf] text-[12px] whitespace-nowrap">
                    SUPER</p>
                  <div class="bg-[#2dd4bf] opacity-50 relative rounded-[2px] shrink-0 size-[4px]" />
                  <p
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#2dd4bf] text-[12px] whitespace-nowrap">
                    {{ ((odds.super / totalOdds) * 100).toFixed(1) }}%</p>
                </div>
                <div
                  class="bg-[rgba(59,130,246,0.1)] content-stretch flex gap-[8px] items-center px-[16px] py-[8px] relative rounded-[40px] shrink-0">
                  <div aria-hidden
                    class="absolute border border-[rgba(59,130,246,0.25)] border-solid inset-0 pointer-events-none rounded-[40px]" />
                  <p
                    class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#3b82f6] text-[12px] whitespace-nowrap">
                    RARE</p>
                  <div class="bg-[#3b82f6] opacity-50 relative rounded-[2px] shrink-0 size-[4px]" />
                  <p
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#3b82f6] text-[12px] whitespace-nowrap">
                    {{ ((odds.rare / totalOdds) * 100).toFixed(1) }}%</p>
                </div>
                <div
                  class="bg-[rgba(163,163,163,0.1)] content-stretch flex gap-[8px] items-center px-[16px] py-[8px] relative rounded-[40px] shrink-0">
                  <div aria-hidden
                    class="absolute border border-[rgba(163,163,163,0.25)] border-solid inset-0 pointer-events-none rounded-[40px]" />
                  <p
                    class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#a3a3a3] text-[12px] whitespace-nowrap">
                    COMMON</p>
                  <div class="bg-[#a3a3a3] opacity-50 relative rounded-[2px] shrink-0 size-[4px]" />
                  <p
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#a3a3a3] text-[12px] whitespace-nowrap">
                    {{ ((odds.common / totalOdds) * 100).toFixed(1) }}%</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="relative shrink-0 w-full">
        <div
          class="content-stretch flex flex-col gap-[80px] items-start pb-[120px] pt-[60px] px-8 lg:px-[64px] relative size-full">
          <div v-for="band in bandOrder" :key="band.key"
            class="content-stretch flex flex-col gap-[32px] items-start relative shrink-0 w-full">
            <div class="content-stretch flex items-center justify-between py-[16px] relative shrink-0 w-full">
              <div aria-hidden
                :class="['absolute border-b border-solid inset-0 pointer-events-none', `border-[${band.colors.border}]`]" />
              <div
                :class="['absolute bg-gradient-to-r bottom-0', `from-[${band.colors.gradient_from}]`, 'left-0', 'to-[rgba(0,0,0,0)]', 'top-0', 'w-[400px]']" />
              <div class="content-stretch flex gap-[16px] items-center relative shrink-0 px-[16px] w-full">
                <p
                  :class="['[word-break:break-word]', 'font-[\'Cinzel\',sans-serif]', 'font-bold', 'leading-[normal]', 'relative', 'shrink-0', `text-[${band.colors.text}]`, 'text-[24px]', 'whitespace-nowrap']">
                  {{ band.label }}</p>
                <div
                  :class="[`bg-[${band.colors.background}]`, 'content-stretch', 'flex', 'items-start', 'px-[8px]', 'py-[2px]', 'relative', 'rounded-[4px]', 'shrink-0']">
                  <div aria-hidden
                    :class="['absolute', 'border', `border-[${band.colors.inner_border}]`, 'border-solid', 'inset-0', 'pointer-events-none', 'rounded-[4px]']" />
                  <p
                    :class="['[word-break:break-word]', 'font-[\'Jost\',sans-serif]', 'font-semibold', 'leading-[normal]', 'relative', 'shrink-0', `text-[${band.colors.text}]`, 'text-[12px]', 'whitespace-nowrap']">
                    {{ bands?.[band.key]?.count ?? 0 }}</p>
                </div>
              </div>
            </div>
            <div
              class="content-stretch grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-[24px] relative shrink-0 w-full">
              <div v-for="card in bands?.[band.key].cards"
                :key="(card.name || '') + (card.number || '') + (card.set || '')"
                :class="['bg-[#13101e]', `drop-shadow-[0px_0px_8px_${band.colors.shadow}]`, 'flex-[1_0_0]', 'min-w-px', 'relative', 'rounded-[8px]']">
                <div aria-hidden
                  :class="['absolute', 'border', `border-[${band.colors.card_border}]`, 'border-solid', 'inset-0', 'pointer-events-none', 'rounded-[8px]']" />
                <div class="content-stretch flex flex-col gap-[16px] items-start p-[12px] relative size-full">
                  <div class="aspect-[2.5/3.5] relative rounded-[6px] shrink-0 w-full">
                    <img v-if="card?.image" :loading="imageLoading(band.key)"
                      class="absolute inset-0 max-w-none object-cover pointer-events-none rounded-[6px] size-full"
                      :alt="card?.name ?? ''" :src="card.image" />
                    <div v-if="card?.product_badges?.length"
                      class="absolute bottom-1.5 right-1.5 flex flex-col items-end gap-1">
                      <span v-for="badge in card.product_badges" :key="badge"
                        class="px-1.5 py-0.5 rounded-[3px] text-[8px] font-bold uppercase tracking-[0.08em] text-white whitespace-nowrap"
                        :style="{
                          fontFamily: 'Jost, sans-serif',
                          background: 'rgba(13,11,20,0.85)',
                          border: '1px solid rgba(220,193,117,0.4)',
                        }">
                        {{ badge }}
                      </span>
                    </div>
                  </div>
                  <div
                    class="[word-break:break-word] content-stretch flex flex-col gap-[8px] items-start leading-[normal] relative shrink-0 w-full whitespace-nowrap">
                    <div class="content-stretch flex flex-col gap-[2px] items-start relative shrink-0 w-full">
                      <p
                        class="font-['Cinzel:Bold',sans-serif] font-bold overflow-hidden relative shrink-0 text-[16px] text-ellipsis text-white w-full">
                        {{ card.name }}</p>
                      <div
                        class="content-stretch flex font-['Jost',sans-serif] font-normal gap-[6px] items-center relative shrink-0 w-full">
                        <p
                          class="flex-[1_0_0] min-w-px overflow-hidden relative text-[#a3a3a3] text-[12px] text-ellipsis">
                          {{ card.set }}</p>
                        <p class="relative shrink-0 text-[10px] text-[rgba(255,255,255,0.35)]">#{{ card.number }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </main>

  <Footer />
</template>
