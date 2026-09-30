<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';

interface Props {
  rip: { id: number; pack_name: string };
  verification: { hash: string | null; seed: string | null };
  result: {
    available: boolean;
    reason?: string;
    hash_matches?: boolean;
    band_matches?: boolean;
    card_matches?: boolean;
    checked_at?: string;
  };
}

const props = defineProps<Props>();

const passed = props.result.available && props.result.hash_matches && props.result.band_matches && props.result.card_matches;

const hashCopied = ref(false);
const seedCopied = ref(false);

function copyTo(flag: typeof hashCopied) {
  return (value: string) => {
    navigator.clipboard.writeText(value);
    flag.value = true;
    setTimeout(() => { flag.value = false; }, 2000);
  };
}

const copyHash = copyTo(hashCopied);
const copySeed = copyTo(seedCopied);
</script>

<template>
  <Head :title="`Verify pack #${rip.id}`" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0 px-8 lg:px-[64px] py-[20px]">
      <Nav />
    </div>

    <div class="px-8 lg:px-[64px] pt-[40px] pb-[100px] max-w-3xl mx-auto">
      <p class="font-['Cinzel',sans-serif] font-bold text-[36px] lg:text-[44px] text-white leading-tight">
        Pack <span class="text-[#c9a84c]">verification</span>
      </p>
      <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[16px] mt-[10px]">
        {{ rip.pack_name }} — Rip #{{ rip.id }}
      </p>

      <div v-if="result.available" class="mt-[32px] rounded-[12px] p-[28px] border flex items-start gap-4"
        :class="passed
          ? 'bg-[rgba(34,197,94,0.08)] border-[rgba(34,197,94,0.3)]'
          : 'bg-[rgba(248,113,113,0.08)] border-[rgba(248,113,113,0.3)]'">
        <div>
          <p class="font-['Cinzel',sans-serif] font-bold text-[22px]" :class="passed ? 'text-green-400' : 'text-red-400'">
            {{ passed ? 'Verification passed' : 'Verification failed' }}
          </p>
          <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] mt-[8px] leading-relaxed">
            <template v-if="passed">
              The revealed seed hashes to the ID that was published the moment payment was confirmed — before the
              card was drawn — and replaying the exact same rarity-band and card selection from that seed reproduces
              exactly what you were dealt. Nothing was predetermined and nothing changed after the fact.
            </template>
            <template v-else>
              Something doesn't line up. Get in touch at
              <a href="mailto:support@arcanepacks.com" class="text-[#c9a84c] underline">support@arcanepacks.com</a>
              if you're seeing this.
            </template>
          </p>
          <div class="flex flex-wrap gap-[16px] mt-[14px] text-[12px] font-['Jost',sans-serif]">
            <span :class="result.hash_matches ? 'text-green-400' : 'text-red-400'">
              {{ result.hash_matches ? '✓' : '✗' }} Seed matches published hash
            </span>
            <span :class="result.band_matches ? 'text-green-400' : 'text-red-400'">
              {{ result.band_matches ? '✓' : '✗' }} Rarity band matches replay
            </span>
            <span :class="result.card_matches ? 'text-green-400' : 'text-red-400'">
              {{ result.card_matches ? '✓' : '✗' }} Card matches replay
            </span>
          </div>
        </div>
      </div>

      <div v-else class="mt-[32px] rounded-[12px] p-[28px] border bg-[rgba(250,204,21,0.08)] border-[rgba(250,204,21,0.3)]">
        <p class="font-['Cinzel',sans-serif] font-bold text-[20px] text-yellow-400">Not yet available</p>
        <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] mt-[8px]">{{ result.reason }}</p>
      </div>

      <div class="mt-[32px] flex flex-col gap-[16px]">
        <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[10px] p-[20px]">
          <p class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide">
            Verification hash — published the moment payment was confirmed, before the draw ran
          </p>
          <div class="flex items-center gap-3 mt-[8px]">
            <p class="font-mono text-[13px] text-[#c9a84c] break-all">{{ verification.hash }}</p>
            <button v-if="verification.hash" type="button" @click="copyHash(verification.hash!)"
              class="shrink-0 text-[11px] font-['Jost',sans-serif] font-semibold uppercase px-3 py-1.5 rounded-[4px] border border-[#3d2f6e] text-white hover:border-[#c9a84c] transition-colors">
              {{ hashCopied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
        </div>

        <div v-if="verification.seed" class="bg-[#13101e] border border-[rgba(124,58,237,0.35)] rounded-[10px] p-[20px]">
          <p class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide">
            Revealed seed
          </p>
          <div class="flex items-center gap-3 mt-[8px]">
            <p class="font-mono text-[13px] text-white break-all">{{ verification.seed }}</p>
            <button type="button" @click="copySeed(verification.seed!)"
              class="shrink-0 text-[11px] font-['Jost',sans-serif] font-semibold uppercase px-3 py-1.5 rounded-[4px] border border-[#3d2f6e] text-white hover:border-[#c9a84c] transition-colors">
              {{ seedCopied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
        </div>
        <p v-else class="font-['Jost',sans-serif] text-[13px] text-white/40">
          The seed is revealed once you open this pack — the hash above is all that's published until then.
        </p>
      </div>
    </div>

    <Footer />
  </main>
</template>
