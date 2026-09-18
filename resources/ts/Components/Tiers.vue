<template>
  <section class="px-8 lg:px-16 py-[12px] lg:py-[42px]">

    <div class="mb-16 max-w-2xl">
      <h2 class="text-3xl lg:text-5xl xl:text-6xl text-white tracking-tight leading-none"
        :style="{ fontFamily: 'Cinzel, serif', fontWeight: 700 }">
        Flexible Selling <HoloText>Tiers</HoloText>
      </h2>
      <p class="text-base leading-relaxed text-white/80">
        Built to Grow With You. Arcane offers four transparent pricing tiers designed to suit all sellers and buyers.
      </p>
    </div>

    <!-- <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(163,163,163,0.65); --shimmer: rgba(163,163,163,0.1);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Common</div>
          <div class="text-2xl font-black text-white">£1–£4.50</div>
        </div>
      </div>

      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(59,130,246,0.75); --shimmer: rgba(59,130,246,0.1);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Rare</div>
          <div class="text-2xl font-black text-white">£4.50–£10.50</div>
        </div>
      </div>

      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(45,212,191,0.75); --shimmer: rgba(45,212,191,0.1);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Super</div>
          <div class="text-2xl font-black text-white">£10.50–£50</div>
        </div>
      </div>

      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(123,79,233,0.65); --shimmer: rgba(123,79,233,0.1);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Legendary</div>
          <div class="text-2xl font-black text-white">£50-£150</div>
        </div>
      </div>

      <div class="price-card rounded-xl p-6 flex flex-col gap-4"
        style="--glow: rgba(201,168,76,0.75); --shimmer: rgba(201,168,76,0.2);">
        <div class="price-icon w-12 h-12 rounded-xl flex items-center justify-center">
          <span class="price-dot w-4 h-4 rounded-full"></span>
        </div>
        <div>
          <div class="price-name text-sm font-bold uppercase tracking-wider mb-1">Mythic</div>
          <div class="text-2xl font-black text-white">£150-£400</div>
        </div>
      </div>

    </div> -->

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
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
                  class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span>
              <span class="text-white/50">1 <span class="text-white/80">· 0.8%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-mythic" style="width:0.8%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span>
              <span class="text-white/50">2 <span class="text-white/80">· 1.6%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-legendary" style="width:1.6%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-super"></span>Super</span>
              <span class="text-white/50">3 <span class="text-white/80">· 2.4%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-super" style="width:2.4%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-rare"></span>Rare</span>
              <span class="text-white/50">5 <span class="text-white/80">· 4.0%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-rare" style="width:4%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-common"></span>Common</span>
              <span class="text-white/50">114 <span class="text-white/80">· 91.20%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-common" style="width:91.20%"></div>
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
                  class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span>
              <span class="text-white/50">2 <span class="text-white/80">· 0.8%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-mythic" style="width:0.8%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span>
              <span class="text-white/50">3 <span class="text-white/80">· 1.2%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-legendary" style="width:1.2%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-super"></span>Super</span>
              <span class="text-white/50">5 <span class="text-white/80">· 2.0%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-super" style="width:2.0%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-rare"></span>Rare</span>
              <span class="text-white/50">20 <span class="text-white/80">· 8%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-rare" style="width:8%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-common"></span>Common</span>
              <span class="text-white/50">200 <span class="text-white/80">· 86.4%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-common" style="width:86.4%"></div>
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
                  class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span>
              <span class="text-white/50">3 <span class="text-white/80">· 0.6%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-mythic" style="width:0.6%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span>
              <span class="text-white/50">6 <span class="text-white/80">· 1.2%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-legendary" style="width:1.2%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-super"></span>Super</span>
              <span class="text-white/50">8 <span class="text-white/80">· 1.6%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-super" style="width:1.6%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-rare"></span>Rare</span>
              <span class="text-white/50">63 <span class="text-white/80">· 12.6%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-rare" style="width:12.6%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-common"></span>Common</span>
              <span class="text-white/50">420 <span class="text-white/80">· 84%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-common" style="width:84%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Premium -->
      <div class="card-premium p-8 relative overflow-hidden group rounded">
        <div
          class="absolute inset-0 bg-gradient-to-br from-pink-500/0 to-amber-500/0 group-hover:from-pink-500/10 group-hover:to-amber-500/5 transition-all duration-500">
        </div>

        <span
          class="premium-flag absolute top-0 right-0 px-3 py-1 text-[10px] font-bold tracking-[0.18em] uppercase text-white rounded-bl">
          New
        </span>

        <div class="tier-chip-premium inline-flex items-center gap-2 px-3 py-1.5 rounded-md mb-6">
          <span class="text-lg leading-none">👑</span>
          <span class="text-[11px] font-bold tracking-[0.12em] uppercase text-white">Premium &mdash; The Ultimate
            Chase</span>
        </div>

        <p class="text-sm text-white/80 leading-relaxed mb-6">
          No commons at all — every pack is a hit, headlined by a single chase card.
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
                  class="w-2 h-2 rounded-full dot-chase"></span>Chase</span>
              <span class="text-white/50">1 <span class="text-white/80">· 0.2%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-chase" style="width:0.2%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-mythic"></span>Mythic</span>
              <span class="text-white/50">5 <span class="text-white/80">· 1.0%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-mythic" style="width:1%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-legendary"></span>Legendary</span>
              <span class="text-white/50">24 <span class="text-white/80">· 4.8%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-legendary" style="width:4.8%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-super"></span>Super</span>
              <span class="text-white/50">110 <span class="text-white/80">· 22%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-super" style="width:22%"></div>
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-2 text-white/80"><span
                  class="w-2 h-2 rounded-full dot-rare"></span>Rare</span>
              <span class="text-white/50">360 <span class="text-white/80">· 72%</span></span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bar-rare" style="width:72%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
<script setup lang="ts">
import HoloText from './HoloText.vue';
// RARITY_COLORS import removed — the bars below use static CSS classes
// (dot-*/bar-*) instead of Tailwind classes composed from a runtime value,
// which Tailwind's build can't detect and which risked a hard crash if the
// import ever resolved differently than expected. See chat notes.
</script>
<style>
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
.tier-chip-premium {
  background: linear-gradient(120deg, #f59e0b, #ec4899, #a855f7, #f59e0b);
  background-size: 300% 100%;
  border: 1px solid rgba(236, 72, 153, 0.55);
  box-shadow: rgba(0, 0, 0, 0.6) 0px 4px 24px, rgba(236, 72, 153, 0.5) 0px 0px 18px;
  animation: gradientShift 5s ease infinite;
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

/* Premium is deliberately louder than the other three — it carries the only
   chase slot, so it gets a moving gradient border (two backgrounds: the card
   fill clipped to padding-box, the gradient to border-box) and a breathing
   glow, rather than the flat border-colour treatment above. */
.card-premium {
  border: 1px solid transparent;
  background:
    linear-gradient(rgba(14, 14, 29, 0.92), rgba(14, 14, 29, 0.92)) padding-box,
    linear-gradient(120deg, #f59e0b, #ec4899, #a855f7, #f59e0b) border-box;
  background-size: 100% 100%, 300% 100%;
  animation: premium-border 6s linear infinite, premium-glow 3.5s ease-in-out infinite;
}
@keyframes premium-border {
  from { background-position: 0% 0%, 0% 50%; }
  to { background-position: 0% 0%, 300% 50%; }
}
@keyframes premium-glow {
  0%, 100% {
    box-shadow: 0 0 34px -10px rgba(236, 72, 153, 0.45), 0 0 70px -24px rgba(245, 158, 11, 0.3);
  }
  50% {
    box-shadow: 0 0 52px -8px rgba(236, 72, 153, 0.7), 0 0 96px -18px rgba(245, 158, 11, 0.45);
  }
}
.premium-flag {
  background: linear-gradient(120deg, #f59e0b, #ec4899, #a855f7, #f59e0b);
  background-size: 300% 100%;
  animation: gradientShift 5s ease infinite;
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

/* Rarity color scale — shared by the dots in the distribution rows and the bars below them */
.dot-common,
.bar-common {
  background: #a3a3a3;
}
.dot-rare,
.bar-rare {
  background: #3b82f6;
}
.dot-super,
.bar-super {
  background: #2dd4bf;
}
.dot-legendary,
.bar-legendary {
  background: #7b4fe9;
}
.dot-mythic,
.bar-mythic {
  background: #c9a84c;
}
.dot-chase {
  background: #ec4899;
}
/* The one chase slot per Premium batch — a moving gradient rather than the
   flat fills above, to match the card's own border treatment. */
.bar-chase {
  background: linear-gradient(90deg, #f59e0b, #ec4899, #a855f7, #f59e0b);
  background-size: 300% 100%;
  animation: gradientShift 3s linear infinite;
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