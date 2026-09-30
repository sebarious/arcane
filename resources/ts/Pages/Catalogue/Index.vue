<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';
import { useCardStock, type StockCard } from '@/composables/useCardStock';

const LETTERS = Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));

const showLetterPicker = ref(false);
const showFilterPicker = ref(false);

// Search, A-Z browse, filters and the featured landing view — shared with
// the in-store kiosk so the two stay in step.
const {
  query, results, searching, hasSearched,
  browseLetter, browseLoading,
  activeSet, activeRarity, gradedFilter, filterSets, filterRarities, hasGradedStock, setSearch,
  hasFilters, isFeatured, listMode, displayResults, visibleSets,
  scheduleSearch, selectLetter, clearLetter, toggleRarity, selectSet, setGraded, clearGraded,
  clearFilters, loadPage, init: initStock,
} = useCardStock();

// Same zoom levels as the kiosk (see resources/ts/Pages/Kiosk/Index.vue) —
// sized with inline styles rather than dynamic Tailwind classes, since
// arbitrary-value classes built from a data object at runtime never appear
// literally in source, so Tailwind's build-time scanner wouldn't generate
// CSS for them.
interface ZoomLevel {
  cols: number;
  imgW: number;
  imgH: number;
  nameSize: number;
  subSize: number;
  priceSize: number;
}
const ZOOM_LEVELS: ZoomLevel[] = [
  { cols: 2, imgW: 40, imgH: 55, nameSize: 14, subSize: 11, priceSize: 14 },
  { cols: 2, imgW: 52, imgH: 71, nameSize: 16, subSize: 12, priceSize: 16 },
  { cols: 1, imgW: 64, imgH: 88, nameSize: 18, subSize: 13, priceSize: 18 },
  { cols: 1, imgW: 88, imgH: 120, nameSize: 22, subSize: 15, priceSize: 22 },
];
const DEFAULT_ZOOM_INDEX = 1;
const zoomIndex = ref(DEFAULT_ZOOM_INDEX);
const zoom = computed(() => ZOOM_LEVELS[zoomIndex.value]);

function zoomIn() {
  zoomIndex.value = Math.min(zoomIndex.value + 1, ZOOM_LEVELS.length - 1);
}

function zoomOut() {
  zoomIndex.value = Math.max(zoomIndex.value - 1, 0);
}

const previewCard = ref<StockCard | null>(null);

function formatPence(pence: number): string {
  return '£' + (pence / 100).toFixed(2);
}

function openLetterPicker() {
  showLetterPicker.value = true;
}

// Two columns of card rows is unreadable on a phone, whatever the zoom
// level is set to — the tiles end up ~150px wide with an image, a name, a
// set and a price in them. Below the sm breakpoint it's always one.
const isNarrow = ref(false);
const gridCols = computed(() => (isNarrow.value ? 1 : zoom.value.cols));

function syncViewport() {
  isNarrow.value = window.innerWidth < 640;
}

// This is a normal scrollable page (not a fixed-height panel like the
// kiosk's), so infinite scroll watches the window instead of a container.
function onWindowScroll() {
  if (!listMode.value) return;

  const nearBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 400;

  if (nearBottom) loadPage();
}

onMounted(() => {
  window.addEventListener('scroll', onWindowScroll);
  window.addEventListener('resize', syncViewport);
  syncViewport();
  initStock();
});
onUnmounted(() => {
  window.removeEventListener('scroll', onWindowScroll);
  window.removeEventListener('resize', syncViewport);
});
</script>

<template>
  <Head title="Browse our stock" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0">
      <div class="flex items-center justify-between px-5 sm:px-8 lg:px-[64px] py-[20px] relative w-full">
        <div class="h-[49px] relative shrink-0 w-full">
          <Nav />
        </div>
      </div>
    </div>

    <div class="px-5 sm:px-8 lg:px-[64px] pt-[40px] pb-[16px] max-w-5xl mx-auto">
      <p class="font-['Cinzel',sans-serif] font-bold text-[40px] lg:text-[48px] text-white leading-tight">
        Browse our <span class="text-[#c9a84c]">stock</span>
      </p>
      <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[16px] mt-[10px] max-w-2xl">
        What we've currently got in the shop — pop in to buy, or use the kiosk in-store.
      </p>
    </div>

    <div class="px-5 sm:px-8 lg:px-[64px] pb-[100px] max-w-5xl mx-auto">
      <!-- Search takes the full width on a phone with the controls on their
           own row beneath; from sm up they sit on one line as before. -->
      <div class="flex flex-col gap-3 sm:flex-row">
        <div class="w-full sm:flex-1 bg-[#1a1628] border border-[#3d2f6e] rounded-[10px] h-[56px]">
          <input v-model="query" @input="scheduleSearch" type="text" placeholder="Card name, e.g. Charizard ex"
            class="w-full h-full bg-transparent border-none outline-none text-[16px] text-white px-5 placeholder:opacity-40 placeholder:text-white focus:ring-0" />
        </div>

        <div class="flex gap-3">
          <button type="button" @click="openLetterPicker"
            class="shrink-0 w-[56px] h-[56px] rounded-[10px] border font-['Cinzel',sans-serif] font-bold text-[16px] transition-colors"
            :class="browseLetter
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            {{ browseLetter ?? 'A-Z' }}
          </button>
          <button type="button" @click="showFilterPicker = true"
            class="flex-1 sm:flex-none sm:shrink-0 px-4 h-[56px] rounded-[10px] border font-semibold uppercase text-[13px] font-['Jost',sans-serif] transition-colors"
            :class="hasFilters
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            Filter
          </button>
          <div class="shrink-0 flex border border-[#3d2f6e] rounded-[10px] h-[56px] overflow-hidden">
            <button type="button" @click="zoomOut" :disabled="zoomIndex === 0"
              class="w-[40px] h-full flex items-center justify-center text-white text-[20px] font-bold hover:bg-[#1a1628] disabled:opacity-30 border-r border-[#3d2f6e]">
              −
            </button>
            <button type="button" @click="zoomIn" :disabled="zoomIndex === ZOOM_LEVELS.length - 1"
              class="w-[40px] h-full flex items-center justify-center text-white text-[20px] font-bold hover:bg-[#1a1628] disabled:opacity-30">
              +
            </button>
          </div>
        </div>
      </div>

      <p v-if="isFeatured" class="text-[#a3a3a3] text-[13px] font-['Jost',sans-serif] mt-3">
        A few fresh picks from the latest sets — search, browse A-Z, or filter to find something specific.
      </p>

      <div v-if="browseLetter || hasFilters" class="flex items-center flex-wrap gap-2 mt-3">
        <span v-if="browseLetter"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#3d2f6e] bg-[#1a1628] text-white text-[13px] font-['Jost',sans-serif]">
          Starting with "{{ browseLetter }}"
          <button type="button" @click="clearLetter" class="text-[#a3a3a3] hover:text-white text-[16px] leading-none">×</button>
        </span>
        <span v-if="activeSet"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[13px] font-['Jost',sans-serif]">
          {{ activeSet }}
          <button type="button" @click="selectSet(null)" class="hover:text-white text-[16px] leading-none">×</button>
        </span>
        <span v-if="activeRarity"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[13px] capitalize font-['Jost',sans-serif]">
          {{ activeRarity }}
          <button type="button" @click="toggleRarity(activeRarity)" class="hover:text-white text-[16px] leading-none">×</button>
        </span>
        <span v-if="gradedFilter"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[13px] font-['Jost',sans-serif]">
          {{ gradedFilter === 'only' ? 'Graded only' : 'No graded' }}
          <button type="button" @click="clearGraded" class="hover:text-white text-[16px] leading-none">×</button>
        </span>
      </div>

      <div class="mt-6">
        <p v-if="searching" class="text-[#a3a3a3] text-[14px] font-['Jost',sans-serif]">Searching…</p>
        <p v-else-if="!listMode && hasSearched && results.length === 0" class="text-[#a3a3a3] text-[14px] font-['Jost',sans-serif]">
          No matches in stock.
        </p>
        <p v-else-if="listMode && !browseLoading && displayResults.length === 0" class="text-[#a3a3a3] text-[14px] font-['Jost',sans-serif]">
          <template v-if="isFeatured">Nothing in stock right now.</template>
          <template v-else>Nothing in stock matches that{{ hasFilters ? ' — try removing a filter.' : '.' }}</template>
        </p>

        <div :style="{ display: 'grid', gridTemplateColumns: `repeat(${gridCols}, minmax(0, 1fr))`, gap: '12px' }">
          <button v-for="card in displayResults" :key="card.id" type="button" @click="previewCard = card"
            class="flex items-center gap-3 p-3 rounded-[10px] border border-[#3d2f6e] bg-[#13101e] hover:border-[#c9a84c] transition-colors text-left">
            <img v-if="card.image_url" :src="card.image_url" class="object-cover rounded-[4px] shrink-0"
              :style="{ width: zoom.imgW + 'px', height: zoom.imgH + 'px' }" />
            <div class="flex-1 min-w-0">
              <p class="text-white truncate font-['Jost',sans-serif]" :style="{ fontSize: zoom.nameSize + 'px' }">{{ card.card_name }}</p>
              <p class="text-[#a3a3a3] truncate font-['Jost',sans-serif]" :style="{ fontSize: zoom.subSize + 'px' }">{{ card.set_name }} · {{ card.rarity }}</p>
              <p class="text-[#c9a84c] font-semibold font-['Jost',sans-serif] mt-1" :style="{ fontSize: zoom.priceSize + 'px' }">{{ formatPence(card.price_pence) }}</p>
            </div>
          </button>
        </div>

        <p v-if="listMode && browseLoading" class="text-[#a3a3a3] text-[13px] font-['Jost',sans-serif] text-center py-4">Loading more…</p>
      </div>
    </div>
  </main>

  <Footer />

  <!-- Card preview -->
  <div v-if="previewCard" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 sm:p-8"
    @click="previewCard = null">
    <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-6 max-w-sm w-full flex flex-col items-center"
      @click.stop>
      <div v-if="previewCard.image_url" class="relative inline-block mb-5">
        <img :src="previewCard.image_url" class="max-h-[50vh] w-auto object-contain rounded-[8px]" />
        <div v-if="previewCard.product_badges?.length" class="absolute bottom-2 right-2 flex flex-col items-end gap-1">
          <span v-for="badge in previewCard.product_badges" :key="badge"
            class="px-1.5 py-0.5 rounded-[3px] text-[9px] font-bold uppercase tracking-[0.08em] text-white whitespace-nowrap"
            :style="{
              fontFamily: 'Jost, sans-serif',
              background: 'rgba(13,11,20,0.85)',
              border: '1px solid rgba(220,193,117,0.4)',
            }">
            {{ badge }}
          </span>
        </div>
      </div>
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px] text-center">{{ previewCard.card_name }}</p>
      <p class="text-[#a3a3a3] text-[14px] text-center mt-1 font-['Jost',sans-serif]">{{ previewCard.set_name }} · {{ previewCard.rarity }}</p>
      <p class="text-[#c9a84c] text-[26px] font-bold mt-3 font-['Jost',sans-serif]">{{ formatPence(previewCard.price_pence) }}</p>

      <button type="button" @click="previewCard = null"
        class="w-full h-[52px] mt-6 rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[14px] hover:border-[#c9a84c] transition-colors font-['Jost',sans-serif]">
        Close
      </button>
    </div>
  </div>

  <!-- Letter picker -->
  <div v-if="showLetterPicker" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 sm:p-8"
    @click="showLetterPicker = false">
    <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-6 max-w-lg w-full" @click.stop>
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px] text-center mb-5">Browse by letter</p>
      <div class="grid grid-cols-6 gap-2.5">
        <button v-for="letter in LETTERS" :key="letter" type="button"
          @click="selectLetter(letter); showLetterPicker = false"
          class="aspect-square rounded-[8px] border border-[#3d2f6e] text-white font-['Cinzel',sans-serif] font-bold text-[18px] hover:border-[#c9a84c] hover:text-[#c9a84c] transition-colors">
          {{ letter }}
        </button>
      </div>
      <button type="button" @click="showLetterPicker = false"
        class="w-full h-[48px] mt-5 rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[13px] hover:border-[#c9a84c] transition-colors font-['Jost',sans-serif]">
        Cancel
      </button>
    </div>
  </div>

  <!-- Filters -->
  <div v-if="showFilterPicker" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 sm:p-8"
    @click="showFilterPicker = false">
    <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-6 max-w-lg w-full flex flex-col max-h-[80vh] font-['Jost',sans-serif]"
      @click.stop>
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px] text-center mb-5 shrink-0">Filter cards</p>

      <p class="text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] mb-2 shrink-0">Rarity</p>
      <div class="flex flex-wrap gap-2 mb-5 shrink-0">
        <button v-for="rarity in filterRarities" :key="rarity" type="button" @click="toggleRarity(rarity)"
          class="px-4 h-[44px] rounded-[8px] border text-[15px] capitalize transition-colors"
          :class="activeRarity === rarity
            ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
            : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
          {{ rarity }}
        </button>
      </div>

      <p class="text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] mb-2 shrink-0">Graded</p>
      <div class="flex flex-wrap gap-2 mb-2 shrink-0">
        <button type="button" @click="setGraded('only')"
          class="px-4 h-[44px] rounded-[8px] border text-[15px] transition-colors"
          :class="gradedFilter === 'only'
            ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
            : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
          Graded only
        </button>
        <button type="button" @click="setGraded('exclude')"
          class="px-4 h-[44px] rounded-[8px] border text-[15px] transition-colors"
          :class="gradedFilter === 'exclude'
            ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
            : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
          Hide graded
        </button>
      </div>
      <p v-if="!hasGradedStock" class="text-[#71717a] text-[12px] mb-5 shrink-0">No graded cards in stock right now.</p>
      <div v-else class="mb-5"></div>

      <p class="text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] mb-2 shrink-0">Set</p>
      <input v-model="setSearch" type="text" placeholder="Find a set…"
        class="w-full h-[48px] bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] text-white text-[15px] px-4 mb-2 shrink-0 outline-none placeholder:opacity-40 placeholder:text-white focus:ring-0" />

      <div class="flex-1 overflow-y-auto min-h-0 space-y-1.5">
        <button type="button" @click="selectSet(null)"
          class="w-full text-left px-4 h-[44px] rounded-[8px] border text-[15px] transition-colors"
          :class="activeSet === null
            ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
            : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
          All sets
        </button>
        <button v-for="set in visibleSets" :key="set" type="button" @click="selectSet(set)"
          class="w-full text-left px-4 h-[44px] rounded-[8px] border text-[15px] truncate transition-colors"
          :class="activeSet === set
            ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
            : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
          {{ set }}
        </button>
        <p v-if="visibleSets.length === 0" class="text-[#71717a] text-[14px] px-1 py-2">No sets match that.</p>
      </div>

      <div class="flex gap-3 mt-5 shrink-0">
        <button type="button" :disabled="!hasFilters" @click="clearFilters"
          class="flex-1 h-[48px] rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[13px] hover:border-[#c9a84c] disabled:opacity-30 transition-colors">
          Clear all
        </button>
        <button type="button" @click="showFilterPicker = false"
          class="flex-1 h-[48px] rounded-[6px] text-[#0d0b14] font-bold uppercase text-[13px]"
          style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
          Done
        </button>
      </div>
    </div>
  </div>
</template>
