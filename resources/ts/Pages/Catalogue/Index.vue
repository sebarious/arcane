<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

interface StockCard {
  id: number;
  card_name: string;
  set_name: string | null;
  card_number: string | null;
  rarity: string | null;
  image_url: string | null;
  price_pence: number;
  product_badges: string[];
}

const query = ref('');
const results = ref<StockCard[]>([]);
const searching = ref(false);
const hasSearched = ref(false);

const LETTERS = Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));

const showLetterPicker = ref(false);
const browseLetter = ref<string | null>(null);
const browseResults = ref<StockCard[]>([]);
const browsePage = ref(1);
const browseHasMore = ref(true);
const browseLoading = ref(false);

const displayResults = computed(() => (browseLetter.value ? browseResults.value : results.value));

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

let debounceTimer: ReturnType<typeof setTimeout> | undefined;

function scheduleSearch() {
  if (browseLetter.value) clearBrowse();
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(runSearch, 350);
}

async function runSearch() {
  const q = query.value.trim();

  if (q.length < 2) {
    results.value = [];
    hasSearched.value = false;
    return;
  }

  searching.value = true;
  hasSearched.value = true;

  try {
    const { data } = await axios.get('/kiosk/search', { params: { q } });
    results.value = data.data ?? [];
  } catch {
    results.value = [];
  } finally {
    searching.value = false;
  }
}

function openLetterPicker() {
  showLetterPicker.value = true;
}

function selectLetter(letter: string) {
  showLetterPicker.value = false;

  query.value = '';
  results.value = [];
  hasSearched.value = false;

  browseLetter.value = letter;
  browseResults.value = [];
  browsePage.value = 1;
  browseHasMore.value = true;
  loadBrowsePage();
}

function clearBrowse() {
  browseLetter.value = null;
  browseResults.value = [];
  browsePage.value = 1;
  browseHasMore.value = true;
}

async function loadBrowsePage() {
  if (!browseLetter.value || browseLoading.value || !browseHasMore.value) return;

  browseLoading.value = true;

  try {
    const { data } = await axios.get('/kiosk/browse', {
      params: { letter: browseLetter.value, page: browsePage.value },
    });
    browseResults.value.push(...(data.data ?? []));
    browseHasMore.value = Boolean(data.has_more);
    browsePage.value += 1;
  } catch {
    browseHasMore.value = false;
  } finally {
    browseLoading.value = false;
  }
}

// This is a normal scrollable page (not a fixed-height panel like the
// kiosk's), so infinite scroll watches the window instead of a container.
function onWindowScroll() {
  if (!browseLetter.value) return;

  const nearBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 400;

  if (nearBottom) loadBrowsePage();
}

onMounted(() => window.addEventListener('scroll', onWindowScroll));
onUnmounted(() => window.removeEventListener('scroll', onWindowScroll));
</script>

<template>
  <Head title="Browse our stock" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0">
      <div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full">
        <div class="h-[49px] relative shrink-0 w-full">
          <Nav />
        </div>
      </div>
    </div>

    <div class="px-8 lg:px-[64px] pt-[40px] pb-[16px] max-w-5xl mx-auto">
      <p class="font-['Cinzel',sans-serif] font-bold text-[40px] lg:text-[48px] text-white leading-tight">
        Browse our <span class="text-[#c9a84c]">stock</span>
      </p>
      <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[16px] mt-[10px] max-w-2xl">
        What we've currently got in the shop — pop in to buy, or use the kiosk in-store.
      </p>
    </div>

    <div class="px-8 lg:px-[64px] pb-[100px] max-w-5xl mx-auto">
      <div class="flex gap-3">
        <div class="flex-1 bg-[#1a1628] border border-[#3d2f6e] rounded-[10px] h-[56px]">
          <input v-model="query" @input="scheduleSearch" type="text" placeholder="Card name, e.g. Charizard ex"
            class="w-full h-full bg-transparent border-none outline-none text-[16px] text-white px-5 placeholder:opacity-40 placeholder:text-white focus:ring-0" />
        </div>
        <button type="button" @click="openLetterPicker"
          class="shrink-0 w-[56px] h-[56px] rounded-[10px] border font-['Cinzel',sans-serif] font-bold text-[16px] transition-colors"
          :class="browseLetter
            ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
            : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
          {{ browseLetter ?? 'A-Z' }}
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

      <div v-if="browseLetter" class="flex items-center gap-2 mt-3">
        <p class="text-[#a3a3a3] text-[13px] font-['Jost',sans-serif]">Browsing cards starting with "{{ browseLetter }}"</p>
        <button type="button" @click="clearBrowse" class="text-[#c9a84c] text-[13px] underline">Clear</button>
      </div>

      <div class="mt-6">
        <p v-if="searching" class="text-[#a3a3a3] text-[14px] font-['Jost',sans-serif]">Searching…</p>
        <p v-else-if="!browseLetter && hasSearched && results.length === 0" class="text-[#a3a3a3] text-[14px] font-['Jost',sans-serif]">
          No matches in stock.
        </p>
        <p v-else-if="browseLetter && !browseLoading && browseResults.length === 0" class="text-[#a3a3a3] text-[14px] font-['Jost',sans-serif]">
          No cards in stock starting with "{{ browseLetter }}".
        </p>

        <div :style="{ display: 'grid', gridTemplateColumns: `repeat(${zoom.cols}, minmax(0, 1fr))`, gap: '12px' }">
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

        <p v-if="browseLetter && browseLoading" class="text-[#a3a3a3] text-[13px] font-['Jost',sans-serif] text-center py-4">Loading more…</p>
      </div>
    </div>
  </main>

  <Footer />

  <!-- Card preview -->
  <div v-if="previewCard" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
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
  <div v-if="showLetterPicker" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
    @click="showLetterPicker = false">
    <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-6 max-w-lg w-full" @click.stop>
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px] text-center mb-5">Browse by letter</p>
      <div class="grid grid-cols-6 gap-2.5">
        <button v-for="letter in LETTERS" :key="letter" type="button" @click="selectLetter(letter)"
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
</template>
