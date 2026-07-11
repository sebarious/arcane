<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { onMounted, onBeforeUnmount, ref, computed } from 'vue';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic'

interface Store {
  id: number
  slug: string
  name: string
  city: string
  postcode: string
}

interface Batch {
  id: number
  reference: string
  type: string | null
  game: string | null
  game_label: string | null
  pack_count: number
  created_at: string | null
}

interface BandCard {
  name: string | null
  set: string | null
  number: string | null
  image: string | null
  band: Rarity | null
  sold: boolean
}

interface BandInfo {
  count: number
  cards: BandCard[]
}

interface Props {
  store: Store
  batch: Batch
  bands: Record<Rarity, BandInfo>
}

const props = defineProps<Props>()

// Local reactive copy so we can update counts live
const bands = ref<Record<Rarity, BandInfo>>( props.bands )

const odds = {} as Record<Rarity, number>;
Object.entries(bands.value).forEach(([band, info]) => {
  odds[band as Rarity] = info.count;
});

const totalOdds = computed( () =>
  ( Object.values( odds ) as number[] ).reduce( ( sum, x ) => sum + x, 0 )
)

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

let channel: any = null;
onMounted( () => {
  if ( !window.Echo ) return;
  channel = window.Echo.channel( `store.${props.store.id}` )
    .listen( '.PackSold', ( payload: { band: Rarity | null; } ) => {
      const band = payload.band;
      if ( !band ) return;
      const current = bands.value[band];
      if ( !current ) return;
      const newCount = Math.max( 0, current.count - 1 );
      bands.value = {
        ...bands.value,
        [band]: {
          ...current,
          count: newCount,
          // If you later send per-card sold flags, you can update them here too.
        },
      };
    } );
} );
onBeforeUnmount( () => {
  if ( channel && window.Echo ) {
    window.Echo.leaveChannel( `store.${props.store.id}` );
  }
} )

const imageLoading = (band: Rarity): 'lazy' | 'eager' =>
  band === 'mythic' || band === 'legendary' ? 'eager' : 'lazy'

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

    <div class="relative shrink-0 w-full">
      <div class="content-stretch flex flex-col gap-[24px] items-start px-8 lg:px-[64px] py-[40px] relative size-full">
        <div class="content-stretch flex items-center relative shrink-0">
          <p
            class="[word-break:break-word] font-['Jost','Noto_Sans','Noto_Sans_Math','Noto_Sans_Symbols','Noto_Sans_Symbols2',sans-serif] font-medium leading-[normal] relative shrink-0 text-[#7b4fe9] text-[14px] whitespace-nowrap">
            <a :href="'/' + store.slug" class="hover:underline">
              ← Back to {{ store.name }}
            </a>
          </p>
        </div>
        <div class="content-stretch flex items-end justify-between relative shrink-0 w-full">
          <div class="content-stretch flex flex-col gap-[16px] items-start relative">
            <p
              class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative text-[48px] text-white">
              {{ store.name }} Card List</p>
            <div class="content-stretch flex gap-[12px] items-center relative shrink-0">
              <div
                class="bg-[rgba(123,79,233,0.1)] content-stretch flex items-center px-[12px] py-[6px] relative rounded-[4px] shrink-0">
                <div aria-hidden
                  class="absolute border border-[rgba(123,79,233,0.25)] border-solid inset-0 pointer-events-none rounded-[4px]" />
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[11px] uppercase whitespace-nowrap">
                  {{ batch.type }}</p>
              </div>
              <div
                class="bg-[rgba(255,255,255,0.04)] content-stretch flex items-center px-[12px] py-[6px] relative rounded-[4px] shrink-0">
                <div aria-hidden
                  class="absolute border border-[rgba(255,255,255,0.1)] border-solid inset-0 pointer-events-none rounded-[4px]" />
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[#a3a3a3] text-[11px] uppercase whitespace-nowrap">
                  {{ batch.pack_count }} packs</p>
              </div>
              <p
                class="[word-break:break-word] font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[14px]">
                Created {{ batch.created_at }}</p>
            </div>
          </div>
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
                  {{ ( ( odds.mythic / totalOdds ) * 100 ).toFixed( 1 ) }}%</p>
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
                  {{ ( ( odds.legendary / totalOdds ) * 100 ).toFixed( 1 ) }}%</p>
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
                  {{ ( ( odds.super / totalOdds ) * 100 ).toFixed( 1 ) }}%</p>
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
                  {{ ( ( odds.rare / totalOdds ) * 100 ).toFixed( 1 ) }}%</p>
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
                  {{ ( ( odds.common / totalOdds ) * 100 ).toFixed( 1 ) }}%</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="relative shrink-0 w-full">
      <div
        class="content-stretch flex flex-col gap-[80px] items-start pb-[120px] pt-[60px] px-8 lg:px-[64px] relative size-full">
        <div v-for=" band in bandOrder " :key="band.key"
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
                  {{ bands[band.key]?.count ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div
            class="content-stretch grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-[24px] relative shrink-0 w-full">
            <div v-for=" card in bands[band.key].cards "
              :key="( card.name || '' ) + ( card.number || '' ) + ( card.set || '' )"
              :class="['bg-[#13101e]', `drop-shadow-[0px_0px_8px_${band.colors.shadow}]`, 'flex-[1_0_0]', 'min-w-px', 'relative', 'rounded-[8px]']">
              <div aria-hidden
                :class="['absolute', 'border', `border-[${band.colors.card_border}]`, 'border-solid', 'inset-0', 'pointer-events-none', 'rounded-[8px]']" />
              <div class="content-stretch flex flex-col gap-[16px] items-start p-[12px] relative size-full">
                <div class="aspect-[2.5/3.5] relative rounded-[6px] shrink-0 w-full">
                  <img v-if="card?.image" :loading="imageLoading( band.key )"
                    class="absolute inset-0 max-w-none object-cover pointer-events-none rounded-[6px] size-full"
                    :alt="card?.name ?? ''" :src="card.image" />
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
  </main>

  <Footer />
</template>
