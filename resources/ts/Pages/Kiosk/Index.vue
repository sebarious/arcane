<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

// Registers the no-op service worker Chrome requires before it'll offer
// "Add to Home Screen" — see public/kiosk-sw.js. Installing that (rather than
// just bookmarking) is what lets the tablet launch this full-screen with no
// browser chrome, which is what makes OS-level kiosk lockdown (Guided
// Access / screen pinning) actually work.
onMounted(() => {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/kiosk-sw.js', { scope: '/kiosk' }).catch(() => {
      // Not fatal — the page still works, it just won't be installable.
    });
  }
});

interface SearchResult {
  id: number;
  card_name: string;
  set_name: string | null;
  card_number: string | null;
  rarity: string | null;
  image_url: string | null;
  price_pence: number;
  product_badges: string[];
}

interface BasketItem {
  id: number;
  card_name: string;
  set_name: string | null;
  card_number: string | null;
  image_url: string | null;
  price_pence: number;
}

type Screen = 'shopping' | 'paying' | 'success' | 'declined';

const screen = ref<Screen>('shopping');

const query = ref('');
const results = ref<SearchResult[]>([]);
const searching = ref(false);
const hasSearched = ref(false);

const LETTERS = Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));

const showLetterPicker = ref(false);
const browseLetter = ref<string | null>(null);
const browseResults = ref<SearchResult[]>([]);
const browsePage = ref(1);
const browseHasMore = ref(true);
const browseLoading = ref(false);

// Whichever mode is active — typed search or A-Z browse — the results panel
// renders from the same list, so add-to-basket/preview don't need to know
// which one produced it.
const displayResults = computed(() => (browseLetter.value ? browseResults.value : results.value));

// Results-grid zoom. Sized with inline styles rather than dynamic Tailwind
// classes on purpose — arbitrary-value classes built from a data object at
// runtime (e.g. `w-[${n}px]`) never appear literally in source, so Tailwind's
// build-time scanner wouldn't generate the CSS for them.
interface ZoomLevel {
  cols: number;
  imgW: number;
  imgH: number;
  nameSize: number;
  subSize: number;
  priceSize: number;
}
const ZOOM_LEVELS: ZoomLevel[] = [
  { cols: 3, imgW: 36, imgH: 50, nameSize: 13, subSize: 10, priceSize: 13 },
  { cols: 2, imgW: 44, imgH: 60, nameSize: 15, subSize: 12, priceSize: 16 },
  { cols: 2, imgW: 56, imgH: 76, nameSize: 17, subSize: 13, priceSize: 18 },
  { cols: 1, imgW: 76, imgH: 104, nameSize: 22, subSize: 15, priceSize: 22 },
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

function resetZoom() {
  zoomIndex.value = DEFAULT_ZOOM_INDEX;
}

const basket = ref<BasketItem[]>([]);
const basketBusy = ref(false);
const basketError = ref('');

// Tapping a search result's thumbnail opens this instead of adding it
// straight to the basket — lets someone check they've got the right card
// (illustration, rarity) before committing.
const previewCard = ref<SearchResult | null>(null);

const orderReference = ref('');
const orderTotalPence = ref(0);
const payError = ref('');

const totalPence = computed(() => basket.value.reduce((sum, item) => sum + item.price_pence, 0));

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

  // Leaving search mode entirely — browsing replaces it, not the other way
  // round (scheduleSearch() clears browse mode if the customer starts typing).
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

// Infinite scroll — fetch the next page a little before the user actually
// hits the bottom, so it's already loaded by the time they get there.
function onResultsScroll(event: Event) {
  if (!browseLetter.value) return;

  const el = event.target as HTMLElement;
  const nearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 200;

  if (nearBottom) loadBrowsePage();
}

async function addToBasket(card: SearchResult) {
  basketBusy.value = true;
  basketError.value = '';

  try {
    const { data } = await axios.post('/kiosk/basket', { card_inventory_id: card.id });
    basket.value = data.data;

    if (browseLetter.value) {
      // Stay in browse mode — just drop the now-reserved card and keep
      // scroll position, so picking several cards off the same letter
      // doesn't mean re-opening the picker each time.
      browseResults.value = browseResults.value.filter((c) => c.id !== card.id);
    } else {
      query.value = '';
      results.value = [];
      hasSearched.value = false;
    }
  } catch (e: any) {
    basketError.value = e?.response?.data?.message ?? 'Could not add that card — please try again.';
  } finally {
    basketBusy.value = false;
  }
}

async function addToBasketFromPreview() {
  if (!previewCard.value) return;
  await addToBasket(previewCard.value);
  previewCard.value = null;
}

async function removeFromBasket(id: number) {
  basketBusy.value = true;

  try {
    const { data } = await axios.delete(`/kiosk/basket/${id}`);
    basket.value = data.data;
  } finally {
    basketBusy.value = false;
  }
}

async function checkout() {
  if (basket.value.length === 0) return;

  screen.value = 'paying';
  payError.value = '';
  resetZoom();

  try {
    const { data } = await axios.post('/kiosk/checkout');
    orderReference.value = data.data.reference;
    orderTotalPence.value = data.data.total_pence;
    pollOrder(data.data.order_id);
  } catch (e: any) {
    payError.value = e?.response?.data?.message ?? 'Could not start checkout — please try again.';
    screen.value = 'declined';
  }
}

let pollTimer: ReturnType<typeof setTimeout> | undefined;
const POLL_TIMEOUT_MS = 3 * 60 * 1000;

function pollOrder(orderId: number, elapsedMs = 0) {
  if (elapsedMs > POLL_TIMEOUT_MS) {
    payError.value = 'This took too long — please ask a member of staff for help.';
    screen.value = 'declined';
    return;
  }

  pollTimer = setTimeout(async () => {
    try {
      const { data } = await axios.get(`/kiosk/orders/${orderId}/status`);

      if (data.data.status === 'paid') {
        screen.value = 'success';
        return;
      }

      if (data.data.status === 'pending_payment') {
        pollOrder(orderId, elapsedMs + 2000);
        return;
      }

      payError.value = 'That order could not be completed — please ask a member of staff for help.';
      screen.value = 'declined';
    } catch {
      pollOrder(orderId, elapsedMs + 2000);
    }
  }, 2000);
}

function startNewOrder() {
  if (pollTimer) clearTimeout(pollTimer);
  basket.value = [];
  orderReference.value = '';
  orderTotalPence.value = 0;
  payError.value = '';
  resetZoom();
  screen.value = 'shopping';
}

// A decline/timeout doesn't clear the basket — the same items may well still
// be held (see reserved_until), so let the customer just retry payment.
function backToBasket() {
  if (pollTimer) clearTimeout(pollTimer);
  orderReference.value = '';
  orderTotalPence.value = 0;
  payError.value = '';
  resetZoom();
  screen.value = 'shopping';
}

async function clearBasket() {
  if (basket.value.length === 0) return;

  basketBusy.value = true;

  try {
    const { data } = await axios.delete('/kiosk/basket');
    basket.value = data.data;
  } finally {
    basketBusy.value = false;
  }

  query.value = '';
  results.value = [];
  hasSearched.value = false;
  clearBrowse();
  resetZoom();
}
</script>

<template>
  <Head title="Arcane Kiosk">
    <link rel="manifest" href="/kiosk-manifest.json" />
    <meta name="theme-color" content="#0d0b14" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <link rel="apple-touch-icon" href="/images/logo.png" />
  </Head>

  <div class="fixed inset-0 bg-[#0d0b14] overflow-hidden font-['Jost',sans-serif] select-none">
    <!-- Shopping -->
    <div v-if="screen === 'shopping'" class="h-full flex flex-col">
      <div class="px-8 pt-8 pb-4 shrink-0">
        <p class="font-['Cinzel',sans-serif] font-bold text-white text-[32px]">
          Buy <span class="text-[#c9a84c]">singles</span>
        </p>
        <p class="text-[#a3a3a3] text-[16px] mt-1">Search for a card, add it to your basket, then pay at the reader.</p>
      </div>

      <div class="flex-1 flex gap-6 px-8 pb-8 min-h-0">
        <!-- Search + results -->
        <div class="flex-[2] flex flex-col min-h-0">
          <div class="flex gap-3 shrink-0">
            <div class="flex-1 bg-[#1a1628] border border-[#3d2f6e] rounded-[10px] h-[64px]">
              <input v-model="query" @input="scheduleSearch" type="text" placeholder="Card name, e.g. Charizard ex"
                class="w-full h-full bg-transparent border-none outline-none text-[20px] text-white px-6 placeholder:opacity-40 placeholder:text-white focus:ring-0" />
            </div>
            <button type="button" @click="openLetterPicker"
              class="shrink-0 w-[64px] h-[64px] rounded-[10px] border font-['Cinzel',sans-serif] font-bold text-[20px] transition-colors"
              :class="browseLetter
                ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
                : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
              {{ browseLetter ?? 'A-Z' }}
            </button>
            <div class="shrink-0 flex border border-[#3d2f6e] rounded-[10px] h-[64px] overflow-hidden">
              <button type="button" @click="zoomOut" :disabled="zoomIndex === 0"
                class="w-[44px] h-full flex items-center justify-center text-white text-[22px] font-bold hover:bg-[#1a1628] disabled:opacity-30 border-r border-[#3d2f6e]">
                −
              </button>
              <button type="button" @click="zoomIn" :disabled="zoomIndex === ZOOM_LEVELS.length - 1"
                class="w-[44px] h-full flex items-center justify-center text-white text-[22px] font-bold hover:bg-[#1a1628] disabled:opacity-30">
                +
              </button>
            </div>
          </div>

          <div v-if="browseLetter" class="flex items-center gap-2 mt-3 shrink-0">
            <p class="text-[#a3a3a3] text-[13px]">Browsing cards starting with "{{ browseLetter }}"</p>
            <button type="button" @click="clearBrowse" class="text-[#c9a84c] text-[13px] underline">Clear</button>
          </div>

          <div class="flex-1 overflow-y-auto mt-4 min-h-0" @scroll="onResultsScroll">
            <p v-if="searching" class="text-[#a3a3a3] text-[15px] px-2">Searching…</p>
            <p v-else-if="!browseLetter && hasSearched && results.length === 0" class="text-[#a3a3a3] text-[15px] px-2">No matches in stock.</p>
            <p v-else-if="browseLetter && !browseLoading && browseResults.length === 0" class="text-[#a3a3a3] text-[15px] px-2">
              No cards in stock starting with "{{ browseLetter }}".
            </p>

            <div :style="{ display: 'grid', gridTemplateColumns: `repeat(${zoom.cols}, minmax(0, 1fr))`, gap: '12px' }">
              <div v-for="card in displayResults" :key="card.id"
                class="flex items-center gap-3 p-3 rounded-[10px] border border-[#3d2f6e] bg-[#13101e] hover:border-[#c9a84c] transition-colors">
                <button v-if="card.image_url" type="button" @click="previewCard = card"
                  class="shrink-0 rounded-[4px] overflow-hidden">
                  <img :src="card.image_url" class="object-cover"
                    :style="{ width: zoom.imgW + 'px', height: zoom.imgH + 'px' }" />
                </button>
                <button type="button" :disabled="basketBusy" @click="addToBasket(card)"
                  class="flex-1 flex items-center gap-3 min-w-0 text-left disabled:opacity-50">
                  <div class="flex-1 min-w-0">
                    <p class="text-white truncate" :style="{ fontSize: zoom.nameSize + 'px' }">{{ card.card_name }}</p>
                    <p class="text-[#a3a3a3] truncate" :style="{ fontSize: zoom.subSize + 'px' }">{{ card.set_name }} · {{ card.rarity }}</p>
                  </div>
                  <p class="text-[#c9a84c] font-semibold shrink-0" :style="{ fontSize: zoom.priceSize + 'px' }">{{ formatPence(card.price_pence) }}</p>
                </button>
              </div>
            </div>

            <p v-if="browseLetter && browseLoading" class="text-[#a3a3a3] text-[13px] text-center py-4">Loading more…</p>
          </div>
        </div>

        <!-- Basket -->
        <div class="flex-1 flex flex-col bg-[#13101e] border border-[rgba(124,58,237,0.3)] rounded-[12px] p-5 min-h-0">
          <div class="flex items-center justify-between mb-3">
            <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px]">Basket</p>
            <button type="button" :disabled="basket.length === 0 || basketBusy" @click="clearBasket"
              class="text-[#a3a3a3] hover:text-red-400 text-[13px] underline disabled:opacity-30 disabled:no-underline">
              Clear basket
            </button>
          </div>

          <p v-if="basketError" class="text-red-400 text-[13px] mb-2">{{ basketError }}</p>

          <div class="flex-1 overflow-y-auto space-y-2 min-h-0">
            <p v-if="basket.length === 0" class="text-[#71717a] text-[14px]">Nothing yet — tap a card to add it.</p>

            <div v-for="item in basket" :key="item.id"
              class="flex items-center gap-3 p-2.5 rounded-[8px] border border-[#3d2f6e] bg-[#1a1628]">
              <img v-if="item.image_url" :src="item.image_url" class="w-[36px] h-[50px] object-cover rounded-[4px] shrink-0" />
              <div class="flex-1 min-w-0">
                <p class="text-white text-[14px] truncate">{{ item.card_name }}</p>
                <p class="text-[#a3a3a3] text-[11px] truncate">{{ item.set_name }}</p>
              </div>
              <p class="text-[#c9a84c] text-[14px] shrink-0">{{ formatPence(item.price_pence) }}</p>
              <button type="button" :disabled="basketBusy" @click="removeFromBasket(item.id)"
                class="text-[#a3a3a3] hover:text-red-400 text-[20px] leading-none px-1 shrink-0">×</button>
            </div>
          </div>

          <div class="border-t border-[#3d2f6e] pt-4 mt-4 shrink-0">
            <div class="flex items-center justify-between mb-4">
              <p class="text-white text-[16px]">Total</p>
              <p class="text-[#c9a84c] text-[24px] font-bold">{{ formatPence(totalPence) }}</p>
            </div>
            <button type="button" :disabled="basket.length === 0" @click="checkout"
              class="w-full h-[56px] rounded-[6px] text-[#0d0b14] font-bold uppercase text-[16px] disabled:opacity-40"
              style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
              Pay now
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Paying -->
    <div v-else-if="screen === 'paying'" class="h-full flex flex-col items-center justify-center text-center px-8">
      <div class="w-16 h-16 rounded-full border-4 border-[#3d2f6e] border-t-[#c9a84c] animate-spin mb-8" />
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[28px]">Tap, insert, or swipe your card</p>
      <p class="text-[#a3a3a3] text-[16px] mt-3">{{ formatPence(orderTotalPence) }} — follow the reader's prompts</p>
    </div>

    <!-- Success -->
    <div v-else-if="screen === 'success'" class="h-full flex flex-col items-center justify-center text-center px-8">
      <div class="w-20 h-20 rounded-full bg-[rgba(34,197,94,0.15)] flex items-center justify-center mb-6">
        <svg width="40" height="40" viewBox="0 0 20 20" fill="none">
          <path d="M4 10.5L8 14.5L16 5.5" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[28px]">Payment complete</p>
      <p class="text-[#a3a3a3] text-[16px] mt-3">Order {{ orderReference }} — a member of staff will bring your cards over shortly.</p>
      <button type="button" @click="startNewOrder"
        class="mt-10 px-8 h-[52px] rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[14px] hover:border-[#c9a84c] transition-colors">
        Start a new order
      </button>
    </div>

    <!-- Declined / error -->
    <div v-else class="h-full flex flex-col items-center justify-center text-center px-8">
      <div class="w-20 h-20 rounded-full bg-[rgba(239,68,68,0.15)] flex items-center justify-center mb-6">
        <svg width="36" height="36" viewBox="0 0 20 20" fill="none">
          <path d="M6 6L14 14M14 6L6 14" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" />
        </svg>
      </div>
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[28px]">Payment not completed</p>
      <p class="text-[#a3a3a3] text-[16px] mt-3">{{ payError }}</p>
      <button type="button" @click="backToBasket"
        class="mt-10 px-8 h-[52px] rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[14px] hover:border-[#c9a84c] transition-colors">
        Back to basket
      </button>
    </div>

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
        <p class="text-[#a3a3a3] text-[14px] text-center mt-1">{{ previewCard.set_name }} · {{ previewCard.rarity }}</p>
        <p class="text-[#c9a84c] text-[26px] font-bold mt-3">{{ formatPence(previewCard.price_pence) }}</p>

        <div class="flex gap-3 w-full mt-6">
          <button type="button" @click="previewCard = null"
            class="flex-1 h-[52px] rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[14px] hover:border-[#c9a84c] transition-colors">
            Close
          </button>
          <button type="button" :disabled="basketBusy" @click="addToBasketFromPreview"
            class="flex-1 h-[52px] rounded-[6px] text-[#0d0b14] font-bold uppercase text-[14px] disabled:opacity-50"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            Add to basket
          </button>
        </div>
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
          class="w-full h-[48px] mt-5 rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[13px] hover:border-[#c9a84c] transition-colors">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>
