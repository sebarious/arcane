<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import { useCardStock, type StockCard } from '@/composables/useCardStock';
import { useIdleTimer } from '@/composables/useIdleTimer';

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

  // Loads the filter options and the featured landing list.
  initStock();
  // The basket lives in the session, so it survives a refresh, a crashed
  // tablet or the screen being locked and reopened — load it back rather
  // than showing an empty one over the top of it.
  loadBasket();
});

type SearchResult = StockCard;

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

const LETTERS = Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));

const showLetterPicker = ref(false);
const showFilterPicker = ref(false);

// Search, A-Z browse, filters and the featured landing view all come from
// here — shared with the public catalogue so the two can't drift.
const {
  query, results, searching, hasSearched,
  browseLetter, browseLoading,
  activeSet, activeRarity, gradedFilter, filterSets, filterRarities, hasGradedStock, setSearch,
  hasFilters, isFeatured, listMode, displayResults, visibleSets,
  scheduleSearch, selectLetter, clearLetter, toggleRarity, selectSet, setGraded, clearGraded,
  clearFilters, onScroll, removeFromResults, reset: resetStock, init: initStock,
} = useCardStock();

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

interface CustomLine {
  id: string;
  label: string;
  price_pence: number;
}

interface Discount {
  type: 'percent' | 'fixed';
  value: number;
}

// Totals come back with every basket response rather than being added up
// here — the figure on screen is then always the figure the reader will ask
// for, discount and all.
const customLines = ref<CustomLine[]>([]);
const discount = ref<Discount | null>(null);
const subtotalPence = ref(0);
const discountPence = ref(0);
const totalPence = ref(0);

const showReceipt = ref(false);
const receiptEmail = ref('');
const receiptSending = ref(false);
const receiptSentTo = ref('');
const receiptError = ref('');

/** Emails the customer a receipt for the sale that just completed on this tablet. */
async function sendReceipt() {
  if (currentOrderId.value === null || !receiptEmail.value.trim()) return;

  receiptSending.value = true;
  receiptError.value = '';

  try {
    const { data } = await axios.post(`/kiosk/orders/${currentOrderId.value}/receipt`, {
      email: receiptEmail.value.trim(),
    });
    receiptSentTo.value = data.data.email;
    showReceipt.value = false;
    receiptEmail.value = '';
  } catch (e: any) {
    receiptError.value = e?.response?.data?.message
      ?? e?.response?.data?.errors?.email?.[0]
      ?? 'Could not send that receipt — check the address and try again.';
  } finally {
    receiptSending.value = false;
  }
}

const showCustomItem = ref(false);
const customLabel = ref('');
const customAmount = ref('');
const showDiscount = ref(false);
const discountType = ref<'percent' | 'fixed'>('percent');
const discountValue = ref('');

/** Every basket endpoint returns the same shape, so one reader keeps them in step. */
function applyBasket(payload: any) {
  basket.value = payload.data ?? [];
  customLines.value = payload.custom_lines ?? [];
  discount.value = payload.discount ?? null;
  subtotalPence.value = payload.subtotal_pence ?? 0;
  discountPence.value = payload.discount_pence ?? 0;
  totalPence.value = payload.total_pence ?? 0;
}

const discountLabel = computed(() => {
  if (!discount.value) return '';
  return discount.value.type === 'percent'
    ? `${discount.value.value}% off`
    : `${formatPence(discount.value.value)} off`;
});

const basketEmpty = computed(() => basket.value.length === 0 && customLines.value.length === 0);

async function loadBasket() {
  try {
    const { data } = await axios.get('/kiosk/basket');
    applyBasket(data);
  } catch {
    // Non-fatal — the basket just shows empty until the next change.
  }
}

async function addCustomItem() {
  if (!customLabel.value.trim() || !customAmount.value) return;

  basketBusy.value = true;
  basketError.value = '';

  try {
    const { data } = await axios.post('/kiosk/basket/custom', {
      label: customLabel.value.trim(),
      amount: customAmount.value,
    });
    applyBasket(data);
    customLabel.value = '';
    customAmount.value = '';
    showCustomItem.value = false;
  } catch (e: any) {
    basketError.value = e?.response?.data?.message ?? 'Could not add that item.';
  } finally {
    basketBusy.value = false;
  }
}

async function removeCustomLine(id: string) {
  basketBusy.value = true;

  try {
    const { data } = await axios.delete(`/kiosk/basket/custom/${id}`);
    applyBasket(data);
  } finally {
    basketBusy.value = false;
  }
}

async function applyDiscount() {
  if (!discountValue.value) return;

  basketBusy.value = true;
  basketError.value = '';

  try {
    const { data } = await axios.post('/kiosk/basket/discount', {
      type: discountType.value,
      value: discountValue.value,
    });
    applyBasket(data);
    discountValue.value = '';
    showDiscount.value = false;
  } catch (e: any) {
    basketError.value = e?.response?.data?.message ?? 'Could not apply that discount.';
  } finally {
    basketBusy.value = false;
  }
}

async function removeDiscount() {
  basketBusy.value = true;

  try {
    const { data } = await axios.delete('/kiosk/basket/discount');
    applyBasket(data);
  } finally {
    basketBusy.value = false;
  }
}

// --- Open orders ------------------------------------------------------------
// Shopping lists customers built for themselves on the catalogue tablet.
const showOpenOrders = ref(false);
const openOrders = ref<any[]>([]);
const openOrdersLoading = ref(false);
const openOrdersError = ref('');
const collectedNotice = ref('');

async function loadOpenOrders() {
  openOrdersLoading.value = true;
  openOrdersError.value = '';

  try {
    const { data } = await axios.get('/kiosk/open-orders');
    openOrders.value = data.data ?? [];
  } catch {
    openOrdersError.value = 'Could not load open orders.';
  } finally {
    openOrdersLoading.value = false;
  }
}

function openOpenOrders() {
  showOpenOrders.value = true;
  loadOpenOrders();
}

async function discardOpenOrder(id: number) {
  try {
    await axios.delete(`/kiosk/open-orders/${id}`);
    openOrders.value = openOrders.value.filter((o) => o.id !== id);
  } catch {
    openOrdersError.value = 'Could not delete that order.';
  }
}

/** Moves an open order into the live basket so it can be paid for. */
async function collectOpenOrder(id: number) {
  openOrdersError.value = '';

  try {
    const { data } = await axios.post(`/kiosk/open-orders/${id}/checkout`);
    applyBasket(data);
    openOrders.value = openOrders.value.filter((o) => o.id !== id);
    showOpenOrders.value = false;

    // Nothing was held while the order sat waiting, so some of it may have
    // sold in the meantime — staff need to be told which, by name.
    collectedNotice.value = data.unavailable?.length
      ? `${data.reference} loaded. No longer available: ${data.unavailable.join(', ')}.`
      : `${data.reference} loaded into the basket.`;
  } catch {
    openOrdersError.value = 'Could not load that order into the basket.';
  }
}

/** Locks the tablet when it's left unattended — needs today's PIN to reopen. */
function lockKiosk() {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/kiosk/lock';
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
  form.innerHTML = `<input type="hidden" name="_token" value="${token}">`;
  document.body.appendChild(form);
  form.submit();
}

// Left unattended at the counter, the till locks itself. Suppressed while a
// payment is in flight — locking out from under a customer mid-tap would
// strand a live PaymentIntent on the reader.
useIdleTimer(5 * 60 * 1000, lockKiosk, () => screen.value !== 'paying');

function formatPence(pence: number): string {
  return '£' + (pence / 100).toFixed(2);
}

function openLetterPicker() {
  showLetterPicker.value = true;
}

async function addToBasket(card: SearchResult) {
  basketBusy.value = true;
  basketError.value = '';

  try {
    const { data } = await axios.post('/kiosk/basket', { card_inventory_id: card.id });
    applyBasket(data);

    // Drop the now-reserved card but keep the list and scroll position, so
    // picking several off the same letter, filter or featured run doesn't
    // mean starting the browse again each time.
    removeFromResults(card.id);
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
    applyBasket(data);
  } finally {
    basketBusy.value = false;
  }
}

async function checkout() {
  // basketEmpty, not basket.length — same trap clearBasket() already had.
  // "Pay now" enables on basketEmpty, so a basket of nothing but manual
  // lines (a supplies sale, a deposit) lit the button up and then fell out
  // of this guard silently: the button did nothing at all.
  if (basketEmpty.value) return;

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
  showReceipt.value = false;
  receiptEmail.value = '';
  receiptSentTo.value = '';
  receiptError.value = '';
  // Next customer starts from the featured view, not the last one's search.
  resetStock();
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
  // basketEmpty, not basket.length — a basket holding only manual items is
  // still a basket, and before the load-on-mount below this guard was what
  // made "Clear basket" look broken after a refresh.
  if (basketEmpty.value) return;

  basketBusy.value = true;

  try {
    const { data } = await axios.delete('/kiosk/basket');
    applyBasket(data);
  } finally {
    basketBusy.value = false;
  }

  // Back to the featured view. Keeps any set/rarity filter — that's a
  // browsing preference, not basket state.
  resetStock();
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
            <!-- The catalogue tablet's queue of customer-built orders. -->
            <button type="button" @click="openOpenOrders" title="Open orders" aria-label="Open orders"
              class="relative shrink-0 h-[64px] px-4 rounded-[10px] border border-[#3d2f6e] text-[#a3a3a3] hover:border-[#c9a84c] hover:text-[#c9a84c] transition-colors flex items-center gap-2">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                <path d="M3 6h18M16 10a4 4 0 0 1-8 0" />
              </svg>
              <span class="text-[13px] uppercase tracking-[0.1em] font-semibold">Orders</span>
            </button>

            <!-- Locks the tablet when it's left unattended; reopening needs
                 today's PIN from the admin topbar. -->
            <button type="button" @click="lockKiosk" title="Lock kiosk" aria-label="Lock kiosk"
              class="shrink-0 w-[64px] h-[64px] rounded-[10px] border border-[#3d2f6e] text-[#a3a3a3] hover:border-[#c9a84c] hover:text-[#c9a84c] transition-colors flex items-center justify-center">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
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

          <p v-if="isFeatured" class="text-[#a3a3a3] text-[13px] mt-3 shrink-0">
            Fresh picks from the latest sets — or search, browse A-Z, or filter.
          </p>

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
            <span v-if="gradedFilter"
              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-[#c9a84c] bg-[rgba(201,168,76,0.1)] text-[#c9a84c] text-[13px]">
              {{ gradedFilter === 'only' ? 'Graded only' : 'No graded' }}
              <button type="button" @click="clearGraded" class="hover:text-white text-[16px] leading-none">×</button>
            </span>
          </div>

          <div class="flex-1 overflow-y-auto mt-4 min-h-0" @scroll="onScroll">
            <p v-if="searching" class="text-[#a3a3a3] text-[15px] px-2">Searching…</p>
            <p v-else-if="!listMode && hasSearched && results.length === 0" class="text-[#a3a3a3] text-[15px] px-2">No matches in stock.</p>
            <p v-else-if="listMode && !browseLoading && displayResults.length === 0" class="text-[#a3a3a3] text-[15px] px-2">
              <template v-if="isFeatured">Nothing in stock right now.</template>
              <template v-else>Nothing in stock matches that{{ hasFilters ? ' — try removing a filter.' : '.' }}</template>
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

            <p v-if="listMode && browseLoading" class="text-[#a3a3a3] text-[13px] text-center py-4">Loading more…</p>
          </div>
        </div>

        <!-- Basket -->
        <div class="flex-1 flex flex-col bg-[#13101e] border border-[rgba(124,58,237,0.3)] rounded-[12px] p-5 min-h-0">
          <div class="flex items-center justify-between mb-3">
            <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px]">Basket</p>
            <button type="button" :disabled="basketEmpty || basketBusy" @click="clearBasket"
              class="text-[#a3a3a3] hover:text-red-400 text-[13px] underline disabled:opacity-30 disabled:no-underline">
              Clear basket
            </button>
          </div>

          <p v-if="basketError" class="text-red-400 text-[13px] mb-2">{{ basketError }}</p>

          <div class="flex-1 overflow-y-auto space-y-2 min-h-0">
            <p v-if="basketEmpty" class="text-[#71717a] text-[14px]">Nothing yet — tap a card to add it.</p>

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

            <!-- Manual lines: no card, no picture, just a label and a price. -->
            <div v-for="line in customLines" :key="line.id"
              class="flex items-center gap-3 p-2.5 rounded-[8px] border border-dashed border-[#3d2f6e] bg-[#1a1628]">
              <div class="w-[36px] h-[50px] rounded-[4px] shrink-0 flex items-center justify-center text-[#71717a] text-[18px] border border-[#3d2f6e]">+</div>
              <div class="flex-1 min-w-0">
                <p class="text-white text-[14px] truncate">{{ line.label }}</p>
                <p class="text-[#a3a3a3] text-[11px]">Manual item</p>
              </div>
              <p class="text-[#c9a84c] text-[14px] shrink-0">{{ formatPence(line.price_pence) }}</p>
              <button type="button" :disabled="basketBusy" @click="removeCustomLine(line.id)"
                class="text-[#a3a3a3] hover:text-red-400 text-[20px] leading-none px-1 shrink-0">×</button>
            </div>
          </div>

          <div class="flex gap-2 mt-3 shrink-0">
            <button type="button" @click="showCustomItem = true"
              class="flex-1 h-[40px] rounded-[6px] border border-[#3d2f6e] text-white text-[13px] hover:border-[#c9a84c] transition-colors">
              + Manual item
            </button>
            <button type="button" @click="showDiscount = true" :disabled="basketEmpty"
              class="flex-1 h-[40px] rounded-[6px] border text-[13px] transition-colors disabled:opacity-30"
              :class="discount
                ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
                : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
              {{ discount ? discountLabel : 'Discount' }}
            </button>
          </div>

          <div class="border-t border-[#3d2f6e] pt-4 mt-4 shrink-0">
            <!-- Subtotal only appears once there's a discount to explain. -->
            <template v-if="discountPence > 0">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[#a3a3a3] text-[14px]">Subtotal</p>
                <p class="text-[#a3a3a3] text-[14px]">{{ formatPence(subtotalPence) }}</p>
              </div>
              <div class="flex items-center justify-between mb-3">
                <p class="text-[#c9a84c] text-[14px] flex items-center gap-2">
                  {{ discountLabel }}
                  <button type="button" :disabled="basketBusy" @click="removeDiscount"
                    class="text-[#a3a3a3] hover:text-red-400 text-[16px] leading-none">×</button>
                </p>
                <p class="text-[#c9a84c] text-[14px]">−{{ formatPence(discountPence) }}</p>
              </div>
            </template>

            <div class="flex items-center justify-between mb-4">
              <p class="text-white text-[16px]">Total</p>
              <p class="text-[#c9a84c] text-[24px] font-bold">{{ formatPence(totalPence) }}</p>
            </div>
            <button type="button" :disabled="basketEmpty" @click="checkout"
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

      <!-- Receipts are asked for, not assumed: offered here, never required. -->
      <p v-if="receiptSentTo" class="text-[#22c55e] text-[15px] mt-6">
        Receipt on its way to {{ receiptSentTo }}.
      </p>

      <div v-else-if="!showReceipt" class="mt-6">
        <button type="button" @click="showReceipt = true"
          class="px-6 h-[48px] rounded-[6px] border border-[#3d2f6e] text-white text-[15px] hover:border-[#c9a84c] transition-colors">
          Email me a receipt
        </button>
      </div>

      <form v-else @submit.prevent="sendReceipt" class="mt-6 w-full max-w-sm">
        <input v-model="receiptEmail" type="email" inputmode="email" autocomplete="email" placeholder="you@example.com"
          class="w-full h-[56px] bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] text-white text-[17px] text-center outline-none placeholder:opacity-40 placeholder:text-white focus:ring-0" />

        <p v-if="receiptError" class="text-red-400 text-[14px] mt-3">{{ receiptError }}</p>

        <div class="flex gap-3 mt-3">
          <button type="button" @click="showReceipt = false; receiptError = ''"
            class="flex-1 h-[48px] rounded-[6px] border border-[#3d2f6e] text-[#a3a3a3] text-[14px] uppercase hover:border-[#c9a84c] hover:text-white transition-colors">
            No thanks
          </button>
          <button type="submit" :disabled="!receiptEmail.trim() || receiptSending"
            class="flex-1 h-[48px] rounded-[6px] text-[#0d0b14] font-bold uppercase text-[14px] disabled:opacity-40"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            {{ receiptSending ? 'Sending…' : 'Send' }}
          </button>
        </div>
      </form>

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
          <button v-for="letter in LETTERS" :key="letter" type="button"
            @click="selectLetter(letter); showLetterPicker = false"
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

    <!-- Manual item -->
    <div v-if="showCustomItem" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
      @click="showCustomItem = false">
      <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-6 max-w-sm w-full" @click.stop>
        <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px] text-center mb-5">Add a manual item</p>

        <label class="block text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] mb-2">Description</label>
        <input v-model="customLabel" type="text" maxlength="80" placeholder="e.g. Sleeves, deposit"
          class="w-full h-[52px] bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] text-white text-[16px] px-4 mb-4 outline-none placeholder:opacity-40 placeholder:text-white focus:ring-0" />

        <label class="block text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] mb-2">Amount (£)</label>
        <input v-model="customAmount" type="number" step="0.01" min="0.01" inputmode="decimal" placeholder="0.00"
          class="w-full h-[52px] bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] text-white text-[16px] px-4 mb-5 outline-none placeholder:opacity-40 placeholder:text-white focus:ring-0" />

        <div class="flex gap-3">
          <button type="button" @click="showCustomItem = false"
            class="flex-1 h-[48px] rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[13px] hover:border-[#c9a84c] transition-colors">
            Cancel
          </button>
          <button type="button" :disabled="!customLabel.trim() || !customAmount || basketBusy" @click="addCustomItem"
            class="flex-1 h-[48px] rounded-[6px] text-[#0d0b14] font-bold uppercase text-[13px] disabled:opacity-40"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            Add
          </button>
        </div>
      </div>
    </div>

    <!-- Discount -->
    <div v-if="showDiscount" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
      @click="showDiscount = false">
      <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] p-6 max-w-sm w-full" @click.stop>
        <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px] text-center mb-5">Discount</p>

        <div class="flex gap-2 mb-4">
          <button type="button" @click="discountType = 'percent'"
            class="flex-1 h-[48px] rounded-[8px] border text-[15px] transition-colors"
            :class="discountType === 'percent'
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            Percent (%)
          </button>
          <button type="button" @click="discountType = 'fixed'"
            class="flex-1 h-[48px] rounded-[8px] border text-[15px] transition-colors"
            :class="discountType === 'fixed'
              ? 'border-[#c9a84c] text-[#c9a84c] bg-[rgba(201,168,76,0.1)]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            Amount (£)
          </button>
        </div>

        <input v-model="discountValue" type="number" step="0.01" min="0.01"
          :max="discountType === 'percent' ? 100 : undefined" inputmode="decimal"
          :placeholder="discountType === 'percent' ? 'e.g. 10' : 'e.g. 5.00'"
          class="w-full h-[52px] bg-[#1a1628] border border-[#3d2f6e] rounded-[8px] text-white text-[16px] px-4 mb-5 outline-none placeholder:opacity-40 placeholder:text-white focus:ring-0" />

        <div class="flex gap-3">
          <button type="button" @click="showDiscount = false"
            class="flex-1 h-[48px] rounded-[6px] border border-[#3d2f6e] text-white font-semibold uppercase text-[13px] hover:border-[#c9a84c] transition-colors">
            Cancel
          </button>
          <button type="button" :disabled="!discountValue || basketBusy" @click="applyDiscount"
            class="flex-1 h-[48px] rounded-[6px] text-[#0d0b14] font-bold uppercase text-[13px] disabled:opacity-40"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            Apply
          </button>
        </div>
      </div>
    </div>

    <!-- Open orders: the catalogue tablet's queue -->
    <div v-if="showOpenOrders" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-8"
      @click="showOpenOrders = false">
      <div class="bg-[#13101e] border border-[rgba(124,58,237,0.4)] rounded-[16px] w-full max-w-2xl max-h-[85vh] flex flex-col"
        @click.stop>
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#3d2f6e] shrink-0">
          <p class="font-['Cinzel',sans-serif] font-bold text-white text-[20px]">Open orders</p>
          <button type="button" @click="showOpenOrders = false" aria-label="Close"
            class="w-10 h-10 text-[#a3a3a3] hover:text-white text-[26px] leading-none">&times;</button>
        </div>

        <div class="flex-1 overflow-y-auto overscroll-contain min-h-0 px-6 py-5">
          <p v-if="openOrdersError" class="text-red-400 text-[14px] mb-4">{{ openOrdersError }}</p>

          <p v-if="openOrdersLoading" class="text-[#a3a3a3] text-[15px] text-center py-10">Loading…</p>

          <p v-else-if="openOrders.length === 0" class="text-[#a3a3a3] text-[15px] text-center py-10">
            No open orders waiting.
          </p>

          <div v-else v-for="order in openOrders" :key="order.id"
            class="border border-[#3d2f6e] rounded-[10px] p-4 mb-3 last:mb-0">
            <div class="flex items-center justify-between gap-4 mb-3">
              <div class="min-w-0">
                <div class="flex items-baseline gap-3">
                  <p class="font-['Cinzel',sans-serif] font-bold text-[#c9a84c] text-[34px] leading-none">
                    {{ order.short_reference }}
                  </p>
                  <p class="text-[#6b6480] text-[12px] tracking-[0.08em]">{{ order.reference }}</p>
                </div>
                <p class="text-[#a3a3a3] text-[13px] mt-1">
                  {{ order.item_count }} {{ order.item_count === 1 ? 'card' : 'cards' }}
                </p>
              </div>
              <p class="text-white text-[24px] font-bold shrink-0">{{ formatPence(order.total_pence) }}</p>
            </div>

            <ul class="mb-4">
              <li v-for="(item, i) in order.items" :key="i"
                class="flex items-center justify-between gap-3 text-[14px] py-1">
                <span class="text-white/80 truncate">{{ item.card_name }}</span>
                <span class="text-[#a3a3a3] shrink-0">{{ formatPence(item.unit_price_pence) }}</span>
              </li>
            </ul>

            <div class="flex gap-3">
              <button type="button" @click="discardOpenOrder(order.id)"
                class="h-[48px] px-5 rounded-[6px] border border-[#3d2f6e] text-[#a3a3a3] text-[14px] uppercase tracking-[0.08em] hover:border-red-400 hover:text-red-400 transition-colors">
                Delete
              </button>
              <button type="button" @click="collectOpenOrder(order.id)"
                class="flex-1 h-[48px] rounded-[6px] text-[#0d0b14] font-bold uppercase tracking-[0.08em] text-[14px]"
                style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
                Checkout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- What happened to the order just pulled in, including anything that
         sold while it was waiting. -->
    <div v-if="collectedNotice"
      class="fixed left-1/2 -translate-x-1/2 bottom-8 z-[60] max-w-xl w-[90%] bg-[#1a1628] border border-[#c9a84c] rounded-[10px] px-5 py-4 flex items-start gap-4">
      <p class="flex-1 text-white text-[15px] leading-relaxed">{{ collectedNotice }}</p>
      <button type="button" @click="collectedNotice = ''" aria-label="Dismiss"
        class="shrink-0 w-8 h-8 text-[#a3a3a3] hover:text-white text-[22px] leading-none">&times;</button>
    </div>
  </div>
</template>
