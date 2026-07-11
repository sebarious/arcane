<template>
  <footer class="px-8 lg:px-16 py-8 border-t border-[#DCC175]/8">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-2.5">
        <div>
          <img :src="arcaneLogo" alt="Arcane" class="h-8 w-auto" />
        </div>
        <span class="text-xs tracking-[0.25em] text-[#DCC175]/60 uppercase"
          :style="{ fontFamily: 'Cinzel, serif', fontWeight: 600 }">
          Arcane
        </span>
      </div>
      <span class="text-[10px] text-[#DCC175]/50 tracking-widest text-center" :style="{ fontFamily: 'Jost, sans-serif' }">
        © {{ new Date().getFullYear() }} Arcane. All cards authenticated. All packs sealed fresh.
      </span>
    </div>
  </footer>
</template>

<script setup lang="ts">
import arcaneLogo from '@/Assets/Link___Arcane.png';

const socials = ['Instagram', 'TikTok', 'Discord'];
</script>