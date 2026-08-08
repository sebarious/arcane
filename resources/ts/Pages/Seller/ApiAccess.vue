<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

interface StoreOption {
  id: number;
  name: string;
}

interface StoreInfo {
  id: number;
  slug: string;
  name: string;
}

interface ApiAccess {
  access_granted: boolean;
  enabled: boolean;
  token: string | null;
}

interface ApiLimits {
  rate_limit_per_minute: number;
  daily_request_limit: number;
}

interface DailyUsage {
  date: string;
  count: number;
}

interface ApiUsage {
  used_today: number;
  last_30_days: DailyUsage[];
}

interface Props {
  stores: StoreOption[];
  store: StoreInfo;
  api: ApiAccess;
  limits: ApiLimits;
  usage: ApiUsage;
}

const props = defineProps<Props>();

// Meter: today's usage against the store's daily cap. Same accent as the rest
// of the page while there's headroom, stepping through the app's existing
// warning/danger colors as the store approaches or hits the limit.
const usageRatio = computed(() => (
  props.limits.daily_request_limit > 0 ? props.usage.used_today / props.limits.daily_request_limit : 0
));
const meterPercent = computed(() => Math.min(100, usageRatio.value * 100));
const meterFillClass = computed(() => {
  if (usageRatio.value >= 1) return 'bg-red-400';
  if (usageRatio.value >= 0.8) return 'bg-yellow-400';
  return 'bg-[#c9a84c]';
});
const meterTextClass = computed(() => {
  if (usageRatio.value >= 1) return 'text-red-400';
  if (usageRatio.value >= 0.8) return 'text-yellow-400';
  return 'text-white';
});

// 30-day bar chart — a single series (this store's own call volume), so one
// hue and no legend needed; the card heading already says what's plotted.
const chartMax = computed(() => Math.max(1, ...props.usage.last_30_days.map((d) => d.count)));
const hasActivity = computed(() => props.usage.last_30_days.some((d) => d.count > 0));
const hoveredDay = ref<number | null>(null);

function barHeightPercent(count: number): number {
  if (count <= 0) return 0;
  return Math.max(4, (count / chartMax.value) * 100);
}

function formatDay(dateStr: string): string {
  return new Date(`${dateStr}T00:00:00Z`).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', timeZone: 'UTC' });
}

function switchStore(storeId: number) {
  router.get('/seller/api-access', { store: storeId });
}

const tokenCopied = ref(false);
const tokenVisible = ref(false);
const busy = ref(false);

function toggleEnabled() {
  busy.value = true;
  // Store's route key is its slug, not its id — see Store::getRouteKeyName().
  router.post(`/seller/api-access/${props.store.slug}`, {
    enabled: !props.api.enabled,
  }, {
    preserveScroll: true,
    onFinish: () => { busy.value = false; },
  });
}

function regenerateToken() {
  if (!confirm('Regenerate your API token? Anything still using the old one will stop working immediately.')) return;
  busy.value = true;
  router.post(`/seller/api-access/${props.store.slug}/regenerate`, {}, {
    preserveScroll: true,
    onFinish: () => { busy.value = false; },
  });
}

function copyToken() {
  if (!props.api.token) return;
  navigator.clipboard.writeText(props.api.token);
  tokenCopied.value = true;
  setTimeout(() => { tokenCopied.value = false; }, 2000);
}
</script>

<template>
  <Head title="API access" />

  <SellerLayout title="API access" subtitle="Pull batch data and mark cards sold from your own tools.">
    <div v-if="stores.length > 1" class="mb-6">
      <select :value="store.id" @change="switchStore(Number(($event.target as HTMLSelectElement).value))"
        class="bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-3 py-2 text-sm text-white font-['Jost',sans-serif]">
        <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
    </div>

    <div class="max-w-3xl bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
      <template v-if="!api.access_granted">
        <p class="font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-3">Not yet available</p>
        <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] leading-relaxed max-w-xl">
          You don't have API access on this store yet. Reach out and we'll get it set up —
          once granted, you'll be able to switch it on here yourself and grab your token.
          Contact us at
          <a href="mailto:support@arcanepacks.com" class="text-[#c9a84c] underline">support@arcanepacks.com</a>
          to request it.
        </p>
      </template>

      <template v-else>
        <div class="flex items-center justify-between gap-4">
          <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] max-w-lg">
            Use your token to pull an active batch's packs and mark cards sold from your own tools.
          </p>
          <button type="button" @click="toggleEnabled" :disabled="busy"
            class="shrink-0 px-4 py-2 rounded-[4px] text-xs font-['Jost',sans-serif] font-semibold uppercase tracking-wide border transition-colors disabled:opacity-50"
            :class="api.enabled
              ? 'border-[rgba(34,197,94,0.4)] text-[#22c55e] hover:border-[#22c55e]'
              : 'border-[#3d2f6e] text-white hover:border-[#c9a84c]'">
            {{ api.enabled ? 'Enabled — turn off' : 'Disabled — turn on' }}
          </button>
        </div>

        <div v-if="api.enabled" class="mt-5">
          <p class="font-['Jost',sans-serif] text-xs text-[rgba(255,255,255,0.35)] uppercase tracking-wide mb-2">API token</p>
          <div class="flex items-center gap-3 bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3">
            <p class="font-mono text-sm text-white break-all flex-1 min-w-0">
              {{ tokenVisible ? api.token : '•'.repeat(40) }}
            </p>
            <button type="button" @click="tokenVisible = !tokenVisible"
              class="shrink-0 text-xs font-['Jost',sans-serif] font-semibold uppercase px-3 py-1.5 rounded-[4px] border border-[#3d2f6e] text-white hover:border-[#c9a84c] transition-colors">
              {{ tokenVisible ? 'Hide' : 'Show' }}
            </button>
            <button type="button" @click="copyToken"
              class="shrink-0 text-xs font-['Jost',sans-serif] font-semibold uppercase px-3 py-1.5 rounded-[4px] border border-[#3d2f6e] text-white hover:border-[#c9a84c] transition-colors">
              {{ tokenCopied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
          <button type="button" @click="regenerateToken" :disabled="busy"
            class="mt-3 text-xs font-['Jost',sans-serif] font-semibold uppercase tracking-wide text-red-400 hover:underline disabled:opacity-50">
            Regenerate token
          </button>

          <div class="mt-6 pt-5 border-t border-[rgba(220,193,117,0.1)]">
            <p class="font-['Jost',sans-serif] text-xs text-[rgba(255,255,255,0.35)] uppercase tracking-wide mb-2">Endpoints</p>
            <p class="font-['Jost',sans-serif] text-xs text-[#a3a3a3] leading-relaxed">
              Send your token as <span class="font-mono text-white">Authorization: Bearer &lt;token&gt;</span>.
            </p>
            <div class="mt-3 flex flex-col gap-2 font-mono text-xs">
              <p class="text-[#a3a3a3]"><span class="text-[#22c55e]">GET</span>&nbsp;&nbsp;/api/v1/batches/{reference}</p>
              <p class="text-[#a3a3a3]"><span class="text-[#c9a84c]">POST</span>&nbsp;/api/v1/batches/{reference}/packs/{id}/sold</p>
            </div>
          </div>

          <div class="mt-6 pt-5 border-t border-[rgba(220,193,117,0.1)]">
            <div class="flex items-center justify-between mb-2">
              <p class="font-['Jost',sans-serif] text-xs text-[rgba(255,255,255,0.35)] uppercase tracking-wide">Today's usage</p>
              <p class="font-['Jost',sans-serif] text-xs font-semibold" :class="meterTextClass">
                {{ usage.used_today.toLocaleString() }} / {{ limits.daily_request_limit.toLocaleString() }}
              </p>
            </div>
            <div class="h-2 rounded-full bg-[#1a1628] border border-[#3d2f6e] overflow-hidden">
              <div class="h-full rounded-full transition-[width]" :class="meterFillClass" :style="{ width: meterPercent + '%' }" />
            </div>
            <p class="font-['Jost',sans-serif] text-xs text-[#71717a] mt-2">
              Resets at midnight UTC. Rate limited separately to {{ limits.rate_limit_per_minute }} requests per minute
              — either cap returns a 429 response.
            </p>
          </div>

          <div class="mt-6 pt-5 border-t border-[rgba(220,193,117,0.1)]">
            <p class="font-['Jost',sans-serif] text-xs text-[rgba(255,255,255,0.35)] uppercase tracking-wide mb-3">Last 30 days</p>

            <p v-if="!hasActivity" class="font-['Jost',sans-serif] text-xs text-[#71717a]">
              No API activity in the last 30 days yet.
            </p>

            <div v-else>
              <div class="flex items-end gap-[2px] h-24">
                <div v-for="(day, i) in usage.last_30_days" :key="day.date"
                  class="relative flex-1 h-full flex items-end"
                  @mouseenter="hoveredDay = i" @mouseleave="hoveredDay = null">
                  <div
                    class="w-full rounded-t-[4px] bg-[#c9a84c] transition-[opacity] min-h-[2px]"
                    :class="hoveredDay === i ? 'opacity-100' : 'opacity-80'"
                    :style="{ height: barHeightPercent(day.count) + '%' }" />
                  <div v-if="hoveredDay === i"
                    class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 z-10 whitespace-nowrap
                           bg-[#0d0b14] border border-[#3d2f6e] rounded-[6px] px-2.5 py-1.5 pointer-events-none">
                    <p class="font-['Jost',sans-serif] text-[11px] text-white font-semibold leading-tight">{{ day.count.toLocaleString() }} calls</p>
                    <p class="font-['Jost',sans-serif] text-[10px] text-[#71717a] leading-tight">{{ formatDay(day.date) }}</p>
                  </div>
                </div>
              </div>
              <div class="flex items-center justify-between mt-2 font-['Jost',sans-serif] text-[11px] text-[#71717a]">
                <span>{{ formatDay(usage.last_30_days[0].date) }}</span>
                <span>Today</span>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </SellerLayout>
</template>
