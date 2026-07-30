<template>
  <section class="px-8 lg:px-16 py-[12px] lg:py-[42px]">

    <div class="mb-16 max-w-2xl">
      <h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none"
        :style="{ fontFamily: 'Cinzel, serif', fontWeight: 700 }">
        Flexible Selling <HoloText>Tiers</HoloText>
      </h2>
      <p class="text-base leading-relaxed text-white/70">
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
          <span class="text-[11px] font-bold tracking-[0.12em] uppercase text-blue-200">Sapphire &mdash; Start Strong</span>
        </div>

        <p class="text-sm text-white/70 leading-relaxed mb-6">
          Perfect for launching your first mystery batch.
        </p>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span>
            <span class="text-sm text-white">125 packs</span>
          </div>
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Rate</span>
            <span class="text-2xl font-semibold rate-sapphire">£8.00<span class="text-sm text-white/50 font-normal"> /
                pack</span></span>
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

        <p class="text-sm text-white/70 leading-relaxed mb-6">
          More value for sellers looking to scale.
        </p>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span>
            <span class="text-sm text-white">250 packs</span>
          </div>
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Rate</span>
            <span class="text-2xl font-semibold rate-ruby">£7.50<span class="text-sm text-white/50 font-normal"> /
                pack</span></span>
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
          <span class="text-[11px] font-bold tracking-[0.12em] uppercase" style="color: rgb(232,240,255)">Diamond &mdash; Built to Scale</span>
        </div>

        <p class="text-sm text-white/70 leading-relaxed mb-6">
          Our best value for high-volume sellers collections.
        </p>

        <div class="space-y-3 border-t border-white/10 pt-6">
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Batch Size</span>
            <span class="text-sm text-white">500 packs</span>
          </div>
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-white/50 tracking-[0.15em] uppercase">Rate</span>
            <span class="text-2xl font-semibold rate-diamond">£7.00<span class="text-sm text-white/50 font-normal"> /
                pack</span></span>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>
<script setup lang="ts">
import HoloText from './HoloText.vue';
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
</style>