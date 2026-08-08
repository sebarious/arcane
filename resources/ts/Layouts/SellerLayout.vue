<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  LayoutDashboard, PackageSearch, FileText, Wallet, UserRound,
  Plus, LogOut, Menu, X, ExternalLink, Copy, Check, ScanLine, KeyRound, Link2,
} from 'lucide-vue-next';
import arcaneLogo from '@/Assets/Link___Arcane.png';

interface Props {
  title: string;
  subtitle?: string;
}

defineProps<Props>();

const page = usePage();

const currentRouteName = () => (page.props.route as any)?.name as string | undefined;

const NAV_LINKS = [
  { label: 'Dashboard', href: '/seller', match: 'seller.dashboard', icon: LayoutDashboard },
  { label: 'Scan pack', href: '/seller/scan', match: 'seller.scan', icon: ScanLine },
  { label: 'Batches', href: '/seller/batches', match: 'seller.batches', icon: PackageSearch },
  { label: 'Invoices', href: '/seller/invoices', match: 'seller.invoices', icon: FileText },
  { label: 'Wallet', href: '/seller/wallet', match: 'seller.wallet', icon: Wallet },
  { label: 'Store profile', href: '/seller/profile', match: 'seller.profile', icon: UserRound },
  { label: 'API access', href: '/seller/api-access', match: 'seller.api-access', icon: KeyRound },
];

const isActive = (match: string) => (currentRouteName() ?? '').startsWith(match);

const batchRequestsEnabled = () => (page.props.batchRequestsEnabled as boolean | undefined) ?? true;

const mobileOpen = ref(false);

const walletPence = () => (page.props.sellerWallet as number | null) ?? 0;

function formatPence(pence: number): string {
  return '£' + (pence / 100).toFixed(2);
}

const affiliateCode = () => (page.props.sellerAffiliateCode as string | null) ?? null;
const storeSlug = () => (page.props.sellerStoreSlug as string | null) ?? null;

const affiliateCopied = ref(false);

function copyAffiliateCode() {
  const code = affiliateCode();
  if (!code) return;

  navigator.clipboard.writeText(code);
  affiliateCopied.value = true;
  setTimeout(() => { affiliateCopied.value = false; }, 2000);
}

const affiliateLinkCopied = ref(false);

function copyAffiliateLink() {
  const slug = storeSlug();
  if (!slug) return;

  navigator.clipboard.writeText(`${window.location.origin}/a/${slug}`);
  affiliateLinkCopied.value = true;
  setTimeout(() => { affiliateLinkCopied.value = false; }, 2000);
}
</script>

<template>
  <div class="min-h-screen bg-[#0d0b14] text-white flex">
    <!-- Sidebar (desktop) -->
    <aside
      class="hidden lg:flex flex-col w-[248px] shrink-0 h-screen sticky top-0 bg-[#0a0810] border-r border-[rgba(220,193,117,0.1)]">
      <div class="px-6 py-6 border-b border-[rgba(220,193,117,0.08)]">
        <Link href="/" class="flex items-center gap-2">
          <img :src="arcaneLogo" alt="Arcane" class="h-8 w-auto" />
        </Link>
        <p class="font-['Cinzel',sans-serif] font-bold text-[11px] tracking-[0.25em] text-[#7b4fe9] uppercase mt-3">
          Seller area
        </p>
      </div>

      <nav class="flex-1 px-3 py-5 flex flex-col gap-1">
        <Link v-for="link in NAV_LINKS" :key="link.href" :href="link.href"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-[6px] text-sm font-[\'Jost\',sans-serif] font-medium transition-colors',
            isActive(link.match)
              ? 'bg-[rgba(124,58,237,0.15)] text-white border border-[rgba(124,58,237,0.35)]'
              : 'text-[#a3a3a3] hover:text-white hover:bg-[rgba(255,255,255,0.03)] border border-transparent',
          ]">
          <component :is="link.icon" class="size-4 shrink-0" />
          {{ link.label }}
        </Link>
      </nav>

      <div class="px-3 pb-3">
        <Link v-if="batchRequestsEnabled()" href="/seller/request-batch"
          class="flex items-center justify-center gap-2 w-full px-3 py-2.5 rounded-[6px] text-[#0d0b14] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide"
          style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
          <Plus class="size-4" />
          Request batch
        </Link>
        <button v-else type="button" disabled
          title="Batch requests are temporarily unavailable"
          class="flex items-center justify-center gap-2 w-full px-3 py-2.5 rounded-[6px] bg-[rgba(255,255,255,0.05)] text-[#71717a] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide cursor-not-allowed">
          <Plus class="size-4" />
          Request batch
        </button>
      </div>

      <div class="px-3 pb-5 pt-2 border-t border-[rgba(220,193,117,0.08)] flex flex-col gap-1">
        <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-xs text-[#71717a] hover:text-white font-['Jost',sans-serif]">
          <ExternalLink class="size-3.5" />
          Visit main site
        </a>
        <Link href="/logout" method="get" as="button"
          class="flex items-center gap-3 px-3 py-2 rounded-[6px] text-xs text-[#71717a] hover:text-red-400 font-['Jost',sans-serif] text-left w-full">
          <LogOut class="size-3.5" />
          Log out
        </Link>
      </div>
    </aside>

    <!-- Mobile top bar -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-40 flex items-center justify-between px-5 py-4 bg-[#0a0810] border-b border-[rgba(220,193,117,0.1)]">
      <Link href="/"><img :src="arcaneLogo" alt="Arcane" class="h-8 w-auto" /></Link>
      <button @click="mobileOpen = !mobileOpen" class="text-[#DCC175]">
        <X v-if="mobileOpen" :size="22" />
        <Menu v-else :size="22" />
      </button>
    </div>
    <div v-if="mobileOpen" class="lg:hidden fixed inset-0 z-30 bg-[#0a0810] pt-20 px-5 flex flex-col gap-1">
      <Link v-for="link in NAV_LINKS" :key="link.href" :href="link.href" @click="mobileOpen = false"
        :class="['flex items-center gap-3 px-3 py-3 rounded-[6px] text-base font-[\'Jost\',sans-serif]',
          isActive(link.match) ? 'text-white bg-[rgba(124,58,237,0.15)]' : 'text-[#a3a3a3]']">
        <component :is="link.icon" class="size-5 shrink-0" />
        {{ link.label }}
      </Link>
      <Link v-if="batchRequestsEnabled()" href="/seller/request-batch" @click="mobileOpen = false"
        class="mt-4 flex items-center justify-center gap-2 w-full px-3 py-3 rounded-[6px] text-[#0d0b14] font-['Jost',sans-serif] font-bold uppercase tracking-wide"
        style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
        <Plus class="size-4" /> Request batch
      </Link>
      <button v-else type="button" disabled
        class="mt-4 flex items-center justify-center gap-2 w-full px-3 py-3 rounded-[6px] bg-[rgba(255,255,255,0.05)] text-[#71717a] font-['Jost',sans-serif] font-bold uppercase tracking-wide cursor-not-allowed">
        <Plus class="size-4" /> Request batch
      </button>
      <Link href="/logout" method="get" as="button" class="mt-6 flex items-center gap-3 px-3 py-3 text-sm text-[#71717a]">
        <LogOut class="size-4" /> Log out
      </Link>
    </div>

    <!-- Main content -->
    <div class="flex-1 min-w-0 pt-20 lg:pt-0">
      <header class="border-b border-[rgba(220,193,117,0.08)] px-6 lg:px-10 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="font-['Cinzel',sans-serif] font-bold text-2xl lg:text-[28px] text-white">{{ title }}</h1>
          <p v-if="subtitle" class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] mt-1">{{ subtitle }}</p>
        </div>

        <div class="flex items-center gap-3">
          <div v-if="affiliateCode()"
            class="flex items-stretch bg-[#13101e] border border-[rgba(201,168,76,0.25)] rounded-[8px] shrink-0 overflow-hidden">
            <button type="button" @click="copyAffiliateCode"
              class="flex items-center gap-3 px-4 py-2.5 hover:bg-[rgba(201,168,76,0.08)] transition-colors">
              <component :is="affiliateCopied ? Check : Copy" class="size-4 text-[#c9a84c]" />
              <div class="text-left">
                <p class="font-['Jost',sans-serif] text-[10px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide leading-none">Affiliate code</p>
                <p class="font-['Cinzel',sans-serif] font-bold text-sm text-[#c9a84c] leading-tight mt-0.5">{{ affiliateCopied ? 'Copied!' : affiliateCode() }}</p>
              </div>
            </button>
            <button v-if="storeSlug()" type="button" @click="copyAffiliateLink"
              title="Copy your Sell to Us link — prefills your affiliate code for whoever opens it"
              class="flex items-center justify-center px-3 border-l border-[rgba(201,168,76,0.25)] hover:bg-[rgba(201,168,76,0.08)] transition-colors">
              <component :is="affiliateLinkCopied ? Check : Link2" class="size-4 text-[#c9a84c]" />
            </button>
          </div>

          <Link href="/seller/wallet"
            class="flex items-center gap-3 bg-[#13101e] border border-[rgba(201,168,76,0.25)] rounded-[8px] px-4 py-2.5 shrink-0 hover:border-[rgba(201,168,76,0.5)] transition-colors">
            <Wallet class="size-4 text-[#c9a84c]" />
            <div>
              <p class="font-['Jost',sans-serif] text-[10px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide leading-none">Wallet</p>
              <p class="font-['Cinzel',sans-serif] font-bold text-sm text-[#c9a84c] leading-tight mt-0.5">{{ formatPence(walletPence()) }}</p>
            </div>
          </Link>
        </div>
      </header>

      <main class="px-6 lg:px-10 py-8">
        <slot />
      </main>
    </div>
  </div>
</template>
