<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useCardStock, type StockCard } from '@/composables/useCardStock';

/**
 * The look-only catalogue for a tablet standing in the shop: full height, no
 * site chrome, and nothing that takes money — that's the till at /kiosk.
 *
 * Its own page rather than a mode of the public catalogue, which stays a
 * normal web page for customers on their own phones. The part that would
 * actually drift between them — search, browse, filters, the featured
 * view — is the composable, which both share.
 */
const LETTERS = Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));

const showLetterPicker = ref(false);
const showFilterPicker = ref(false);
const previewCard = ref<StockCard | null>(null);

const {
  query, results, searching, hasSearched,
  browseLetter, browseLoading,
  activeSet, activeRarity, gradedFilter, filterSets, filterRarities, hasGradedStock, setSearch,
  hasFilters, isFeatured, listMode, displayResults, visibleSets,
  scheduleSearch, selectLetter, clearLetter, toggleRarity, selectSet, setGraded, clearGraded,
  clearFilters, onScroll, reset: resetAll, init: initStock,
} = useCardStock();

// In onMounted, not at setup: init() fetches, and at setup level that would
// run during server-side rendering too.
onMounted(() => initStock());

// Bigger tiles than the website's: this is read at arm's length on a stand,
// not held in the hand.
const ZOOM_LEVELS = [
  { cols: 4, imgW: 52, imgH: 71, nameSize: 15, subSize: 12, priceSize: 15 },
  { cols: 3, imgW: 64, imgH: 88, nameSize: 17, subSize: 13, priceSize: 17 },
  { cols: 2, imgW: 84, imgH: 115, nameSize: 20, subSize: 15, priceSize: 20 },
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

function formatPence(pence: number): string {
  return '£' + (pence / 100).toFixed(2);
}

/** Clears everything back to the featured view — for the next person walking up. */
function startOver() {
  clearFilters();
  resetAll();
  zoomIndex.value = DEFAULT_ZOOM_INDEX;
  previewCard.value = null;
}
</script>

<template>
  <Head title="Card catalogue" />

  <div class="fixed inset-0 bg-[#0d0b14] overflow-hidden flex flex-col font-['Jost',sans-serif] select-none">

    <!-- Controls -->
    <div class="shrink-0 px-6 pt-6">
      <div class="flex gap-3">
        <div class="flex-1 bg-[#1a1628] border border-[#3d2f6e] rounded-[10px] h-[64px]">
          <input v-model="query" @input="scheduleSearch" type="text" placeholder="Search for a card…"
            class="w-full h-full bg-transparent border-none outline-none text-[20px] text-white px-6 placeholder:opacity-40 placeholder:text-white focus:ring-0" />
        </div>

        <button type="button" @click="showLetterPicker = true"
          class="shrink-0 w-[64px] h-[64px] rounded-[10px] border font-['Cinzel',sans-serif] font-bold text-[20px] transition-colors"
          :class="browseLetter
            ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
            : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
          {{ browseLetter ?? 'A-Z' }}
        </button>

        <button type="button" @click="showFilterPicker = true"
          class="shrink-0 px-5 h-[64px] rounded-[10px] border font-semibold uppercase text-[14px] transition-colors"
          :class="hasFilters
            ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
            : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
          Filter
        </button>

        <div class="shrink-0 flex border border-[#3d2f6e] rounded-[10px] h-[64px] overflow-hidden">
          <button type="button" @click="zoomOut" :disabled="zoomIndex === 0" aria-label="Smaller"
            class="w-[52px] h-full flex items-center justify-center text-white text-[24px] font-bold hover:bg-[#1a1628] disabled:opacity-30 border-r border-[#3d2f6e]">
            −
          </button>
          <button type="button" @click="zoomIn" :disabled="zoomIndex === ZOOM_LEVELS.length - 1" aria-label="Bigger"
            class="w-[52px] h-full flex items-center justify-center text-white text-[24px] font-bold hover:bg-[#1a1628] disabled:opacity-30">
            +
          </button>
        </div>

        <button type="button" @click="startOver"
          class="shrink-0 px-5 h-[64px] rounded-[10px] border border-[#3d2f6e] text-[#a3a3a3] text-[14px] uppercase tracking-[0.08em] hover:border-[#c9a84c] hover:text-white transition-colors">
          Start over
        </button>
      </div>

      <p v-if="isFeatured" class="text-[#a3a3a3] text-[14px] mt-3">
        A few fresh picks from the latest sets — search, browse A-Z, or filter to find something specific.
      </p>

      <div v-if="browseLetter || hasFilters" class="flex items-center flex-wrap gap-2 mt-3">
        <span v-if="browseLetter"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#3d2f6e] bg-[#1a1628] text-white text-[14px]">
          Starting with "{{ browseLetter }}"
          <button type="button" @click="clearLetter" class="text-[#a3a3a3] hover:text-white text-[18px] leading-none">×</button>
        </span>
        <span v-if="activeSet"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[14px]">
          {{ activeSet }}
          <button type="button" @click="selectSet(null)" class="hover:text-white text-[18px] leading-none">×</button>
        </span>
        <span v-if="activeRarity"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[14px] capitalize">
          {{ activeRarity }}
          <button type="button" @click="toggleRarity(activeRarity)" class="hover:text-white text-[18px] leading-none">×</button>
        </span>
        <span v-if="gradedFilter"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[14px]">
          {{ gradedFilter === 'only' ? 'Graded only' : 'No graded' }}
          <button type="button" @click="clearGraded" class="hover:text-white text-[18px] leading-none">×</button>
        </span>
      </div>
    </div>

    <!-- Results: the panel scrolls, the page never does -->
    <div class="flex-1 overflow-y-auto min-h-0 px-6 pt-5 pb-6" @scroll="onScroll">
      <p v-if="searching" class="text-[#a3a3a3] text-[17px] px-1">Searching…</p>
      <p v-else-if="!listMode && hasSearched && results.length === 0" class="text-[#a3a3a3] text-[17px] px-1">
        No matches in stock.
      </p>
      <p v-else-if="listMode && !browseLoading && displayResults.length === 0" class="text-[#a3a3a3] text-[17px] px-1">
        <template v-if="isFeatured">Nothing in stock right now.</template>
        <template v-else>Nothing in stock matches that{{ hasFilters ? ' — try removing a filter.' : '.' }}</template>
      </p>

      <div :style="{ display: 'grid', gridTemplateColumns: `repeat(${zoom.cols}, minmax(0, 1fr))`, gap: '14px' }">
        <button v-for="card in displayResults" :key="card.id" type="button" @click="previewCard = card"
          class="flex items-center gap-3 p-3 rounded-[10px] border border-[#3d2f6e] bg-[#13101e] hover:border-[#c9a84c] transition-colors text-left">
          <img v-if="card.image_url" :src="card.image_url" alt="" class="object-cover rounded-[4px] shrink-0"
            :style="{ width: zoom.imgW + 'px', height: zoom.imgH + 'px' }" />
          <div class="flex-1 min-w-0">
            <p class="text-white truncate" :style="{ fontSize: zoom.nameSize + 'px' }">{{ card.card_name }}</p>
            <p class="text-[#a3a3a3] truncate" :style="{ fontSize: zoom.subSize + 'px' }">{{ card.set_name }} · {{ card.rarity }}</p>
            <p class="text-[#c9a84c] font-semibold mt-1" :style="{ fontSize: zoom.priceSize + 'px' }">{{ formatPence(card.price_pence) }}</p>
          </div>
        </button>
      </div>

      <p v-if="listMode && browseLoading" class="text-[#a3a3a3] text-[14px] text-center py-5">Loading more…</p>
    </div>

    <p class="shrink-0 text-center text-[#71717a] text-[12px] tracking-[0.14em] uppercase pb-4">
      Ask a member of staff to buy any of these
    </p>
  </div>

  <!-- Card preview -->
  <div v-if="previewCard" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
    @click="previewCard = null">
    <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-6 max-w-md w-full flex flex-col items-center"
      @click.stop>
      <div v-if="previewCard.image_url" class="relative inline-block mb-5">
        <img :src="previewCard.image_url" alt="" class="max-h-[55vh] w-auto object-contain rounded-[8px]" />
        <div v-if="previewCard.product_badges?.length" class="absolute bottom-2 right-2 flex flex-col items-end gap-1">
          <span v-for="badge in previewCard.product_badges" :key="badge"
            class="px-1.5 py-0.5 rounded-[3px] text-[10px] font-bold uppercase tracking-[0.08em] text-white whitespace-nowrap"
            style="background: rgba(13,11,20,0.85); border: 1px solid rgba(220,193,117,0.4);">
            {{ badge }}
          </span>
        </div>
      </div>
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[22px] text-center">{{ previewCard.card_name }}</p>
      <p class="text-[#a3a3a3] text-[15px] text-center mt-1">{{ previewCard.set_name }} · {{ previewCard.rarity }}</p>
      <p class="text-[#c9a84c] text-[28px] font-bold mt-3">{{ formatPence(previewCard.price_pence) }}</p>

      <button type="button" @click="previewCard = null"
        class="w-full h-[56px] mt-6 rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[14px] hover:border-[#c9a84c] transition-colors">
        Close
      </button>
    </div>
  </div>

  <!-- Letter picker -->
  <div v-if="showLetterPicker" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
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
        class="w-full h-[52px] mt-5 rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[13px] hover:border-[#c9a84c] transition-colors">
        Cancel
      </button>
    </div>
  </div>

  <!-- Filters -->
  <div v-if="showFilterPicker" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
    @click="showFilterPicker = false">
    <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] w-full max-w-lg max-h-[85vh] flex flex-col"
      @click.stop>
      <div class="flex items-center justify-between px-6 py-4 border-b border-[#3d2f6e] shrink-0">
        <p class="font-['Cinzel',sans-serif] font-bold text-white text-[19px]">Filter cards</p>
        <button type="button" @click="showFilterPicker = false" aria-label="Close filters"
          class="w-[40px] h-[40px] -mr-2 flex items-center justify-center text-[#a3a3a3] hover:text-white text-[24px] leading-none">
          &times;
        </button>
      </div>

      <div class="flex-1 overflow-y-auto overscroll-contain min-h-0 px-6 py-5">
        <p class="text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] mb-2">Rarity</p>
        <div class="flex flex-wrap gap-2 mb-5">
          <button v-for="rarity in filterRarities" :key="rarity" type="button" @click="toggleRarity(rarity)"
            class="px-4 h-[48px] rounded-[8px] border text-[16px] capitalize transition-colors"
            :class="activeRarity === rarity
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            {{ rarity }}
          </button>
        </div>

        <p class="text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] mb-2">Graded</p>
        <div class="flex flex-wrap gap-2 mb-2">
          <button type="button" @click="setGraded('only')"
            class="px-4 h-[48px] rounded-[8px] border text-[16px] transition-colors"
            :class="gradedFilter === 'only'
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            Graded only
          </button>
          <button type="button" @click="setGraded('exclude')"
            class="px-4 h-[48px] rounded-[8px] border text-[16px] transition-colors"
            :class="gradedFilter === 'exclude'
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            Hide graded
          </button>
        </div>
        <p v-if="!hasGradedStock" class="text-[#71717a] text-[13px] mb-5">No graded cards in stock right now.</p>
        <div v-else class="mb-5"></div>

        <p class="text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] mb-2">Set</p>
        <input v-model="setSearch" type="text" placeholder="Find a set…"
          class="w-full h-[52px] bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] text-white text-[16px] px-4 mb-2 outline-none placeholder:opacity-40 placeholder:text-white focus:ring-0" />

        <div class="space-y-1.5">
          <button type="button" @click="selectSet(null)"
            class="w-full text-left px-4 h-[48px] rounded-[8px] border text-[16px] transition-colors"
            :class="activeSet === null
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            All sets
          </button>
          <button v-for="set in visibleSets" :key="set" type="button" @click="selectSet(set)"
            class="w-full text-left px-4 h-[48px] rounded-[8px] border text-[16px] truncate transition-colors"
            :class="activeSet === set
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            {{ set }}
          </button>
          <p v-if="visibleSets.length === 0" class="text-[#71717a] text-[15px] px-1 py-2">No sets match that.</p>
        </div>
      </div>

      <div class="flex gap-3 px-6 py-4 border-t border-[#3d2f6e] shrink-0">
        <button type="button" :disabled="!hasFilters" @click="clearFilters"
          class="flex-1 h-[52px] rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[13px] hover:border-[#c9a84c] disabled:opacity-30 transition-colors">
          Clear all
        </button>
        <button type="button" @click="showFilterPicker = false"
          class="flex-1 h-[52px] rounded-[6px] text-[#0d0b14] font-bold uppercase text-[13px]"
          style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
          Done
        </button>
      </div>
    </div>
  </div>
</template>
