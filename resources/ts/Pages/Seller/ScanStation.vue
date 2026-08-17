<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import SellerLayout from '@/Layouts/SellerLayout.vue';
import { CheckCircle2, AlertTriangle, XCircle, ScanLine } from 'lucide-vue-next';

interface ScanResult {
  status: 'sold' | 'already_sold' | 'not_dispatched' | 'not_found' | 'wrong_store' | 'error';
  card?: { name: string; set: string | null; image: string | null; rarity_band: string | null };
  batch?: { reference: string };
  store?: { name: string };
  scannedAt: number;
}

const inputEl = ref<HTMLInputElement | null>(null);
const code = ref('');
const submitting = ref(false);
const lastResult = ref<ScanResult | null>(null);
const history = ref<ScanResult[]>([]);

function focusInput() {
  nextTick(() => inputEl.value?.focus());
}

onMounted(focusInput);

// Scanners "type" a whole code in a few milliseconds, far faster than a human
// ever could — so a short pause with no new characters reliably means the
// scan finished, and we submit without waiting for an Enter keystroke (which
// not every scanner is configured to send). Enter still submits instantly
// via the form's @submit below, for scanners that do send one.
const SCAN_PAUSE_MS = 120;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(code, (value) => {
  if (debounceTimer) clearTimeout(debounceTimer);
  if (! value.trim()) return;

  debounceTimer = setTimeout(() => {
    submitScan();
  }, SCAN_PAUSE_MS);
});

onBeforeUnmount(() => {
  if (debounceTimer) clearTimeout(debounceTimer);
});

// Kiosk-style: clicking anywhere on the page (that isn't already a focusable
// control) should hand focus straight back to the scan input, since a
// barcode scanner only "types" into whatever currently has focus.
function refocusOnBackgroundClick(event: MouseEvent) {
  const target = event.target as HTMLElement;
  if (target === document.body || target.closest('main')) {
    focusInput();
  }
}

async function submitScan() {
  if (debounceTimer) clearTimeout(debounceTimer);

  if (submitting.value) {
    // A scan is already in flight — retry shortly rather than dropping this
    // one, in case a second pack got scanned before the first request returned.
    debounceTimer = setTimeout(submitScan, SCAN_PAUSE_MS);
    return;
  }

  const value = code.value.trim();
  if (! value) {
    focusInput();
    return;
  }
  code.value = '';

  submitting.value = true;

  try {
    const { data } = await axios.post('/seller/scan', { code: value });
    lastResult.value = { ...data, scannedAt: Date.now() };
  } catch (error: any) {
    const status = error?.response?.data?.status ?? 'error';
    lastResult.value = { status, scannedAt: Date.now() };
  } finally {
    if (lastResult.value) {
      history.value.unshift(lastResult.value);
      history.value = history.value.slice(0, 20);
    }
    submitting.value = false;
    focusInput();
  }
}

function statusLabel(status: ScanResult['status']): string {
  return {
    sold: 'Marked sold',
    already_sold: 'Already sold',
    not_dispatched: "Batch hasn't shipped yet",
    not_found: 'Code not recognized',
    wrong_store: "Belongs to a different store",
    error: 'Something went wrong',
  }[status];
}
</script>

<template>
  <Head title="Scan station" />

  <SellerLayout title="Scan station" subtitle="Scan a pack's QR code with a barcode scanner to mark it sold — no phone needed.">
    <div class="max-w-2xl" @click="refocusOnBackgroundClick">
      <form @submit.prevent="submitScan" class="flex flex-col gap-2">
        <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)]">
          Scan a pack
        </label>
        <div class="relative">
          <ScanLine class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-[#7b4fe9]" />
          <input ref="inputEl" v-model="code" type="text" autocomplete="off" autofocus
            placeholder="Point the scanner at a pack's QR code…"
            class="w-full bg-[#1a1628] border-2 border-[#3d2f6e] rounded-[8px] pl-12 pr-4 py-4 text-base text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
        </div>
        <p class="text-xs text-[#71717a] font-['Jost',sans-serif]">
          Keep this tab open and focused — every scan submits automatically.
        </p>
      </form>

      <!-- Latest result -->
      <div v-if="lastResult" :key="lastResult.scannedAt"
        class="mt-6 rounded-[10px] p-5 border flex items-start gap-4"
        :class="{
          'bg-[rgba(34,197,94,0.08)] border-[rgba(34,197,94,0.35)]': lastResult.status === 'sold',
          'bg-[rgba(250,204,21,0.08)] border-[rgba(250,204,21,0.35)]': ['already_sold', 'not_dispatched'].includes(lastResult.status),
          'bg-[rgba(248,113,113,0.08)] border-[rgba(248,113,113,0.35)]': ['not_found', 'wrong_store', 'error'].includes(lastResult.status),
        }">
        <CheckCircle2 v-if="lastResult.status === 'sold'" class="size-6 text-green-400 shrink-0" />
        <AlertTriangle v-else-if="['already_sold', 'not_dispatched'].includes(lastResult.status)" class="size-6 text-yellow-400 shrink-0" />
        <XCircle v-else class="size-6 text-red-400 shrink-0" />

        <div class="min-w-0">
          <p class="font-['Cinzel',sans-serif] font-bold text-white">{{ statusLabel(lastResult.status) }}</p>
          <template v-if="lastResult.card">
            <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] mt-1">
              {{ lastResult.card.name }}<span v-if="lastResult.card.set"> — {{ lastResult.card.set }}</span>
            </p>
            <p class="font-['Jost',sans-serif] text-xs text-[#71717a] mt-0.5">
              Batch {{ lastResult.batch?.reference }} · {{ lastResult.store?.name }}
            </p>
          </template>
        </div>
      </div>

      <!-- Session history -->
      <div v-if="history.length > 1" class="mt-8">
        <p class="font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-3">
          This session
        </p>
        <ul class="flex flex-col gap-1.5">
          <li v-for="entry in history.slice(1)" :key="entry.scannedAt"
            class="flex items-center justify-between gap-3 text-sm font-['Jost',sans-serif] text-[#a3a3a3] bg-[#13101e] border border-[rgba(220,193,117,0.08)] rounded-[6px] px-3 py-2">
            <span class="truncate">{{ entry.card?.name ?? statusLabel(entry.status) }}</span>
            <span class="shrink-0 text-xs"
              :class="{
                'text-green-400': entry.status === 'sold',
                'text-yellow-400': ['already_sold', 'not_dispatched'].includes(entry.status),
                'text-red-400': ['not_found', 'wrong_store', 'error'].includes(entry.status),
              }">
              {{ statusLabel(entry.status) }}
            </span>
          </li>
        </ul>
      </div>
    </div>
  </SellerLayout>
</template>
