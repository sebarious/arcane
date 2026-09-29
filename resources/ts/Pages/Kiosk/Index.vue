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

  loadFilterOptions();
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

const activeSet = ref<string | null>(null);
const activeRarity = ref<string | null>(null);
const filterSets = ref<string[]>([]);
const filterRarities = ref<string[]>([]);
const showFilterPicker = ref(false);
const setSearch = ref('');

const hasFilters = computed(() => activeSet.value !== null || activeRarity.value !== null);

// 80-odd sets is far too many to thumb through on a tablet, so the picker
// narrows as you type.
const visibleSets = computed(() => {
  const q = setSearch.value.trim().toLowerCase();
  return q ? filterSets.value.filter((s) => s.toLowerCase().includes(q)) : filterSets.value;
});

// Params both the search and browse endpoints take, so a filter applies
// whichever mode produced the current list.
function filterParams(): Record<string, string> {
  const params: Record<string, string> = {};
  if (activeSet.value) params.set = activeSet.value;
  if (activeRarity.value) params.rarity = activeRarity.value;
  return params;
}

const showLetterPicker = ref(false);
const browseLetter = ref<string | null>(null);
const browseResults = ref<SearchResult[]>([]);
const browsePage = ref(1);
const browseHasMore = ref(true);
const browseLoading = ref(false);

// Browse (paginated) backs both the A-Z picker and filter-only listings —
// picking a set with nothing typed should show that set, not an empty panel.
const browseActive = computed(() =>
  browseLetter.value !== null || (hasFilters.value && query.value.trim().length < 2));

// Whichever mode is active — typed search or A-Z browse — the results panel
// renders from the same list, so add-to-basket/preview don't need to know
// which one produced it.
const displayResults = computed(() => (browseActive.value ? browseResults.value : results.value));

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
const currentOrderId = ref<number | null>(null);
const cancelling = ref(false);
const cancelError = ref('');

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
    // Nothing typed but a filter is on — fall back to listing what that
    // filter matches rather than emptying the panel.
    if (hasFilters.value) restartBrowse();
    return;
  }

  searching.value = true;
  hasSearched.value = true;

  try {
    const { data } = await axios.get('/kiosk/search', { params: { q, ...filterParams() } });
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

/** The "Clear" next to the letter chip — drops the letter but keeps any filters, reloading what they still match. */
function clearLetter() {
  clearBrowse();
  if (hasFilters.value) restartBrowse();
}

async function loadBrowsePage() {
  if (!browseActive.value || browseLoading.value || !browseHasMore.value) return;

  browseLoading.value = true;

  try {
    const { data } = await axios.get('/kiosk/browse', {
      params: {
        ...(browseLetter.value ? { letter: browseLetter.value } : {}),
        page: browsePage.value,
        ...filterParams(),
      },
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

/** Reloads the browse list from page 1 — after a filter change, or when a filter replaces a search. */
function restartBrowse() {
  browseResults.value = [];
  browsePage.value = 1;
  browseHasMore.value = true;
  loadBrowsePage();
}

async function loadFilterOptions() {
  try {
    const { data } = await axios.get('/kiosk/filters');
    filterSets.value = data.data?.sets ?? [];
    filterRarities.value = data.data?.rarities ?? [];
  } catch {
    // Non-fatal — search and browse still work unfiltered.
  }
}

/** Re-runs whichever view is showing, so a filter change is reflected immediately. */
function applyFilters() {
  if (query.value.trim().length >= 2) {
    runSearch();
    return;
  }

  if (hasFilters.value || browseLetter.value) {
    restartBrowse();
    return;
  }

  // Last filter cleared with nothing typed and no letter — back to a blank slate.
  browseResults.value = [];
}

function toggleRarity(rarity: string) {
  activeRarity.value = activeRarity.value === rarity ? null : rarity;
  applyFilters();
}

function selectSet(set: string | null) {
  activeSet.value = set;
  showFilterPicker.value = false;
  setSearch.value = '';
  applyFilters();
}

function clearFilters() {
  activeSet.value = null;
  activeRarity.value = null;
  applyFilters();
}

// Infinite scroll — fetch the next page a little before the user actually
// hits the bottom, so it's already loaded by the time they get there.
function onResultsScroll(event: Event) {
  if (!browseActive.value) return;

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

    if (browseActive.value) {
      // Stay in browse mode — just drop the now-reserved card and keep
      // scroll position, so picking several cards off the same letter or
      // filter doesn't mean re-opening the picker each time.
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
  cancelError.value = '';
  resetZoom();

  try {
    const { data } = await axios.post('/kiosk/checkout');
    orderReference.value = data.data.reference;
    orderTotalPence.value = data.data.total_pence;
    currentOrderId.value = data.data.order_id;
    pollOrder(data.data.order_id);
  } catch (e: any) {
    payError.value = e?.response?.data?.message ?? 'Could not start checkout — please try again.';
    screen.value = 'declined';
  }
}

let pollTimer: ReturnType<typeof setTimeout> | undefined;
const POLL_TIMEOUT_MS = 3 * 60 * 1000;
// Kept so a cancel that fails can pick polling back up where it left off,
// rather than handing the customer a fresh 3 minutes at the reader.
let pollElapsedMs = 0;

function pollOrder(orderId: number, elapsedMs = 0) {
  pollElapsedMs = elapsedMs;

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
  cancelError.value = '';
  currentOrderId.value = null;
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
  cancelError.value = '';
  currentOrderId.value = null;
  resetZoom();
  screen.value = 'shopping';
}

/**
 * Abandons payment from the reader screen. The basket is left alone on
 * purpose — the cards are still held for this session, so "cancel" means
 * back to the basket to edit or retry, not start again from nothing.
 */
async function cancelPayment() {
  if (currentOrderId.value === null || cancelling.value) return;

  cancelling.value = true;
  cancelError.value = '';
  if (pollTimer) clearTimeout(pollTimer);

  try {
    const { data } = await axios.post(`/kiosk/orders/${currentOrderId.value}/cancel`);

    // They got their card in just as they hit cancel — the payment stands,
    // so show it as the sale it is rather than bouncing them to the basket.
    if (data.data.status === 'paid') {
      orderReference.value = data.data.reference;
      screen.value = 'success';
      return;
    }

    backToBasket();
  } catch {
    // Couldn't reach the server to call it off, so the reader may well still
    // be live — keep watching instead of stranding them on a dead screen.
    cancelError.value = 'Could not cancel — please follow the reader, or ask a member of staff.';
    pollOrder(currentOrderId.value, pollElapsedMs);
  } finally {
    cancelling.value = false;
  }
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
  // Keeps any set/rarity filter — that's a browsing preference, not basket
  // state — so the list has to be reloaded rather than just emptied.
  clearLetter();
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
      <div class="flex-1 flex gap-6 px-8 py-8 min-h-0">
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
            <button type="button" @click="showFilterPicker = true"
              class="shrink-0 px-5 h-[64px] rounded-[10px] border font-semibold uppercase text-[14px] transition-colors"
              :class="hasFilters
                ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
                : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
              Filter
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

          <div v-if="browseLetter || hasFilters" class="flex items-center flex-wrap gap-2 mt-3 shrink-0">
            <span v-if="browseLetter"
              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#3d2f6e] bg-[#1a1628] text-white text-[13px]">
              Starting with "{{ browseLetter }}"
              <button type="button" @click="clearLetter" class="text-[#a3a3a3] hover:text-white text-[16px] leading-none">×</button>
            </span>
            <span v-if="activeSet"
              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[13px]">
              {{ activeSet }}
              <button type="button" @click="selectSet(null)" class="hover:text-white text-[16px] leading-none">×</button>
            </span>
            <span v-if="activeRarity"
              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[13px] capitalize">
              {{ activeRarity }}
              <button type="button" @click="toggleRarity(activeRarity)" class="hover:text-white text-[16px] leading-none">×</button>
            </span>
          </div>

          <div class="flex-1 overflow-y-auto mt-4 min-h-0" @scroll="onResultsScroll">
            <p v-if="searching" class="text-[#a3a3a3] text-[15px] px-2">Searching…</p>
            <p v-else-if="!browseActive && hasSearched && results.length === 0" class="text-[#a3a3a3] text-[15px] px-2">No matches in stock.</p>
            <p v-else-if="browseActive && !browseLoading && browseResults.length === 0" class="text-[#a3a3a3] text-[15px] px-2">
              Nothing in stock matches that{{ hasFilters ? ' — try removing a filter.' : '.' }}
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

            <p v-if="browseActive && browseLoading" class="text-[#a3a3a3] text-[13px] text-center py-4">Loading more…</p>
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

      <button type="button" :disabled="cancelling" @click="cancelPayment"
        class="mt-10 px-8 h-[52px] rounded-[6px] border border-[#3d2f6e] text-[#a3a3a3] font-semibold uppercase text-[14px] hover:border-[#ef4444] hover:text-[#ef4444] disabled:opacity-40 transition-colors">
        {{ cancelling ? 'Cancelling…' : 'Cancel payment' }}
      </button>
      <p class="text-[#a3a3a3] text-[13px] mt-4">Your basket will be kept.</p>

      <p v-if="cancelError" class="text-[#ef4444] text-[14px] mt-4 max-w-sm">{{ cancelError }}</p>
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

    <!-- Filters -->
    <div v-if="showFilterPicker" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
      @click="showFilterPicker = false">
      <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-6 max-w-lg w-full flex flex-col max-h-[80vh]"
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
  </div>
</template>
