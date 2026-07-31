<template>
  <section class="px-8 lg:px-16 py-[12px] lg:py-[42px]">

    <div class="mb-16 max-w-2xl">
      <h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none"
        :style="{ fontFamily: 'Cinzel, serif', fontWeight: 700 }">
        Flexible Selling <HoloText>Tiers</HoloText>
      </h2>
      <p class="text-base leading-relaxed text-white/80">
        Built to Grow With You. Arcane offers three transparent pricing tiers designed to suit all sellers and buyers.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <!-- Sapphire -->
      <div
        class="card-sapphire p-8 border bg-[#0e0e1d]/60 relative overflow-hidden group rounded transition-colors duration-500">
        <div
          class="absolute inset-0 bg-gradient-to-br from-blue-900/0 to-blue-900/0 group-hover:from-blue-900/10 group-hover:to-transparent transition-all duration-500">
        </div>

        <div class="tier-chip-sapphire inline-flex items-center gap-2 px-3 py-1.5 rounded-md mb-6">
          <span class="text-lg leading-none">💎</span>
          <span class="text-[11px] font-bold tracking-[0.12em] uppercase text-blue-200">Sapphire &mdash; Start
            Strong</span>
        </div>

        <p class="text-sm text-white/80 leading-relaxed mb-6">
          Perfect for launching your first mystery batch.
        </p>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span>
            <span class="text-sm text-white">125 packs</span>
          </div>
        </div>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-common"></span>Common</span>
              <span class="text-white/50">113 <span class="text-white/80">· 90.4%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.common.badge}]`]" style="width:90.4%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-rare"></span>Rare</span>
              <span class="text-white/50">6 <span class="text-white/80">· 4.8%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.rare.badge}]`]" style="width:4.8%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-super"></span>Super</span>
              <span class="text-white/50">3 <span class="text-white/80">· 2.4%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.super.badge}]`]" style="width:2.4%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span>
              <span class="text-white/50">2 <span class="text-white/80">· 1.6%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.legendary.badge}]`]" style="width:1.6%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span>
              <span class="text-white/50">1 <span class="text-white/80">· 0.8%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.mythic.badge}]`]" style="width:0.8%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ruby -->
      <div
        class="card-ruby p-8 border bg-[#0e0e1d]/60 relative overflow-hidden group rounded transition-colors duration-500">
        <div
          class="absolute inset-0 bg-gradient-to-br from-red-900/0 to-red-900/0 group-hover:from-red-900/10 group-hover:to-transparent transition-all duration-500">
        </div>

        <div class="tier-chip-ruby inline-flex items-center gap-2 px-3 py-1.5 rounded-md mb-6">
          <span class="text-lg leading-none">❤️</span>
          <span class="text-[11px] font-bold tracking-[0.12em] uppercase text-red-200">Ruby &mdash; Ready to Grow</span>
        </div>

        <p class="text-sm text-white/80 leading-relaxed mb-6">
          More value for sellers looking to scale.
        </p>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span>
            <span class="text-sm text-white">250 packs</span>
          </div>
        </div>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-common"></span>Common</span>
              <span class="text-white/50">200 <span class="text-white/80">· 80.0%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.common.badge}]`]" style="width:80.0%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-rare"></span>Rare</span>
              <span class="text-white/50">36 <span class="text-white/80">· 14.4%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.rare.badge}]`]" style="width:14.4%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-super"></span>Super</span>
              <span class="text-white/50">8 <span class="text-white/80">· 3.2%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.super.badge}]`]" style="width:3.2%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span>
              <span class="text-white/50">5 <span class="text-white/80">· 2.0%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.legendary.badge}]`]" style="width:2.0%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span>
              <span class="text-white/50">1 <span class="text-white/80">· 0.4%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.mythic.badge}]`]" style="width:0.4%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Diamond -->
      <div
        class="card-diamond p-8 border bg-[#0e0e1d]/60 relative overflow-hidden group rounded transition-colors duration-500">
        <div
          class="absolute inset-0 bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/10 group-hover:to-transparent transition-all duration-500">
        </div>

        <div class="tier-chip-diamond inline-flex items-center gap-2 px-3 py-1.5 rounded-md mb-6">
          <span class="text-lg leading-none">💎</span>
          <span class="text-[11px] font-bold tracking-[0.12em] uppercase" style="color: rgb(232,240,255)">Diamond
            &mdash; Built to Scale</span>
        </div>

        <p class="text-sm text-white/80 leading-relaxed mb-6">
          Our best value for high-volume sellers collections.
        </p>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span>
            <span class="text-sm text-white">500 packs</span>
          </div>
        </div>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-common"></span>Common</span>
              <span class="text-white/50">400 <span class="text-white/80">· 80.0%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.common.badge}]`]" style="width:80.0%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-rare"></span>Rare</span>
              <span class="text-white/50">73 <span class="text-white/80">· 14.6%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.rare.badge}]`]" style="width:14.6%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-super"></span>Super</span>
              <span class="text-white/50">15 <span class="text-white/80">· 3.0%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.super.badge}]`]" style="width:3.0%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span>
              <span class="text-white/50">10 <span class="text-white/80">· 2.0%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.legendary.badge}]`]" style="width:2.0%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span <span class="w-2 h-2 rounded-full"
                  :class="`bg-[${RARITY_COLORS.mythic.badge}]`"></span>Mythic</span>
              <span class="text-white/50">2 <span class="text-white/80">· 0.4%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div :class="['h-full rounded-full', `bg-[${RARITY_COLORS.mythic.badge}]`]" style="width:0.4%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mt-6">

      <!-- Common -->
      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(163,163,163,0.65); --shimmer: rgba(163,163,163,0.1);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Common</div>
          <div class="text-2xl font-black text-white">£1–£4</div>
        </div>
      </div>

      <!-- Rare -->
      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(59,130,246,0.75); --shimmer: rgba(59,130,246,0.1);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Rare</div>
          <div class="text-2xl font-black text-white">£5–£11</div>
        </div>
      </div>

      <!-- Super -->
      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(45,212,191,0.75); --shimmer: rgba(45,212,191,0.1);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Super</div>
          <div class="text-2xl font-black text-white">£11–£50</div>
        </div>
      </div>

      <!-- Legendary -->
      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(123,79,233,0.65); --shimmer: rgba(123,79,233,0.1);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Legendary</div>
          <div class="text-2xl font-black text-white">£50–£200</div>
        </div>
      </div>

      <!-- Mythic -->
      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(201,168,76,0.75); --shimmer: rgba(201,168,76,0.2);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Mythic</div>
          <div class="text-2xl font-black text-white">£200+</div>
        </div>
      </div>

    </div>
  </section>
</template>
<script setup lang="ts">
import HoloText from './HoloText.vue';
import { RARITY_COLORS } from '@/utils';

</script>
<style>
body {
  background: oklch(0.145 0 0);
}

.gradient-text {
  background-image: linear-gradient(90deg, #4c1d95, #7c3aed, #a855f7, #c084fc, #ddd6fe, #a855f7, #7c3aed, #4c1d95);
  background-size: 300% 100%;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  color: transparent;
  display: inline-block;
  animation: gradientShift 6s ease infinite;
}
@keyframes gradientShift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* Tier accent colors — pulled from the hero's Sapphire/Ruby/Diamond pills */
.tier-chip-sapphire {
  background: linear-gradient(135deg, rgb(12, 31, 110) 0%, rgb(26, 77, 181) 100%);
  border: 1px solid rgba(96, 165, 250, 0.5);
  box-shadow: rgba(0, 0, 0, 0.6) 0px 4px 24px, rgba(59, 130, 246, 0.5) 0px 0px 18px;
}
.tier-chip-ruby {
  background: linear-gradient(135deg, rgb(110, 12, 12) 0%, rgb(185, 28, 28) 100%);
  border: 1px solid rgba(248, 113, 113, 0.5);
  box-shadow: rgba(0, 0, 0, 0.6) 0px 4px 24px, rgba(220, 38, 38, 0.5) 0px 0px 18px;
}
.tier-chip-diamond {
  background: rgba(255, 255, 255, 0.18);
  border: 1px solid rgba(210, 230, 255, 0.55);
  box-shadow: rgba(0, 0, 0, 0.6) 0px 4px 24px, rgba(200, 220, 255, 0.5) 0px 0px 18px;
}

.card-sapphire {
  border-color: rgba(96, 165, 250, 0.25);
}
.card-sapphire:hover {
  border-color: rgba(96, 165, 250, 0.5);
}
.card-ruby {
  border-color: rgba(248, 113, 113, 0.25);
}
.card-ruby:hover {
  border-color: rgba(248, 113, 113, 0.5);
}
.card-diamond {
  border-color: rgba(210, 230, 255, 0.3);
}
.card-diamond:hover {
  border-color: rgba(210, 230, 255, 0.55);
}

.rate-sapphire {
  color: rgb(147, 197, 253);
}
.rate-ruby {
  color: rgb(252, 165, 165);
}
.rate-diamond {
  color: rgb(232, 240, 255);
}

.price-card {
  background: rgba(14, 14, 29, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  transition: border-color 0.4s ease, box-shadow 0.4s ease;
}
.price-card:hover {
  box-shadow: 0 0 32px -8px var(--glow);
}
.price-icon {
  background: var(--shimmer);
  box-shadow: 0 0 20px 0 var(--glow);
}
.price-dot {
  background: var(--badge);
}
.price-name {
  color: var(--badge);
}
</style>