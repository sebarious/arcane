<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Gift, Wallet, UserRound, LogOut, Menu, X, ExternalLink } from 'lucide-vue-next';
import arcaneLogo from '@/Assets/Link___Arcane.png';

interface Props {
  title: string;
  subtitle?: string;
}

defineProps<Props>();

const page = usePage();

const currentRouteName = () => (page.props.route as any)?.name as string | undefined;

const NAV_LINKS = [
  { label: 'Buy packs', href: '/rips', match: 'rips.index', icon: Gift },
  { label: 'My rips', href: '/rips/my', match: 'rips.my', icon: Gift },
  { label: 'Wallet', href: '/rips/wallet', match: 'rips.wallet', icon: Wallet },
  { label: 'Profile', href: '/rips/profile', match: 'rips.profile', icon: UserRound },
];

const isActive = (match: string) => (currentRouteName() ?? '') === match || (currentRouteName() ?? '').startsWith(`${match}.`);

const mobileOpen = ref(false);
</script>

<template>
  <div class="min-h-screen bg-[#0d0b14] text-white flex">
    <aside class="hidden lg:flex flex-col w-[248px] shrink-0 h-screen sticky top-0 bg-[#0a0810] border-r border-[rgba(220,193,117,0.1)]">
      <div class="px-6 py-6 border-b border-[rgba(220,193,117,0.08)]">
        <Link href="/" class="flex items-center gap-2">
          <img :src="arcaneLogo" alt="Arcane" class="h-8 w-auto" />
        </Link>
        <p class="font-['Cinzel',sans-serif] font-bold text-[11px] tracking-[0.25em] text-[#DCC175] uppercase mt-3">
          Digital Rips
        </p>
      </div>

      <nav class="flex-1 px-3 py-5 flex flex-col gap-1">
        <Link v-for="link in NAV_LINKS" :key="link.href" :href="link.href"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-[6px] text-sm font-[\'Jost\',sans-serif] font-medium transition-colors',
            isActive(link.match)
              ? 'bg-[rgba(220,193,117,0.12)] text-white border border-[rgba(220,193,117,0.3)]'
              : 'text-[#a3a3a3] hover:text-white hover:bg-[rgba(255,255,255,0.03)] border border-transparent',
          ]">
          <component :is="link.icon" class="size-4 shrink-0" />
          {{ link.label }}
        </Link>
      </nav>

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
          isActive(link.match) ? 'text-white bg-[rgba(220,193,117,0.12)]' : 'text-[#a3a3a3]']">
        <component :is="link.icon" class="size-5 shrink-0" />
        {{ link.label }}
      </Link>
      <Link href="/logout" method="get" as="button" class="mt-6 flex items-center gap-3 px-3 py-3 text-sm text-[#71717a]">
        <LogOut class="size-4" /> Log out
      </Link>
    </div>

    <div class="flex-1 min-w-0 pt-20 lg:pt-0">
      <header class="border-b border-[rgba(220,193,117,0.08)] px-6 lg:px-10 py-6">
        <h1 class="font-['Cinzel',sans-serif] font-bold text-2xl lg:text-[28px] text-white">{{ title }}</h1>
        <p v-if="subtitle" class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] mt-1">{{ subtitle }}</p>
      </header>

      <main class="px-6 lg:px-10 py-8">
        <slot />
      </main>
    </div>
  </div>
</template>
