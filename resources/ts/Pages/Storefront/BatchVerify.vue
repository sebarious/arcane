<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';

interface Props {
  store: { slug: string; name: string };
  batch: { id: number; reference: string };
  verification: { id: string | null; seed: string | null };
  result: {
    available: boolean;
    reason?: string;
    hash_matches?: boolean;
    selection_matches?: boolean;
    checked_at?: string;
  };
}

const props = defineProps<Props>();

const passed = props.result.available && props.result.hash_matches && props.result.selection_matches;

const idCopied = ref(false);
const seedCopied = ref(false);

function copyTo(flag: typeof idCopied) {
  return (value: string) => {
    navigator.clipboard.writeText(value);
    flag.value = true;
    setTimeout(() => { flag.value = false; }, 2000);
  };
}

const copyId = copyTo(idCopied);
const copySeed = copyTo(seedCopied);
</script>

<template>
  <Head :title="`Verify ${batch.reference}`" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0">
      <div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full">
        <div class="h-[49px] relative shrink-0 w-full">
          <Nav />
        </div>
      </div>
    </div>

    <div class="px-8 lg:px-[64px] pt-[40px] pb-[100px] max-w-3xl mx-auto">
      <p class="font-['Jost',sans-serif] font-medium text-[14px] text-[#7b4fe9] mb-6">
        <a :href="`/${store.slug}/${batch.id}`" class="hover:underline">← Back to {{ batch.reference }}</a>
      </p>

      <p class="font-['Cinzel',sans-serif] font-bold text-[36px] lg:text-[44px] text-white leading-tight">
        Batch <span class="text-[#c9a84c]">verification</span>
      </p>
      <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[16px] mt-[10px]">
        {{ store.name }} — {{ batch.reference }}
      </p>

      <!-- Verdict -->
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
              The revealed seed hashes to the Verification ID that was published before this
              batch was generated, and replaying the exact same selection algorithm from that
              seed reproduces this batch's card order exactly. Nothing was predetermined and
              nothing was changed after the fact.
            </template>
            <template v-else>
              Something doesn't line up — either the revealed seed doesn't hash to the
              published Verification ID, or replaying the selection doesn't reproduce this
              batch's actual card order. Get in touch at
              <a href="mailto:support@arcanepacks.com" class="text-[#c9a84c] underline">support@arcanepacks.com</a>
              if you're seeing this.
            </template>
          </p>
          <div class="flex gap-[16px] mt-[14px] text-[12px] font-['Jost',sans-serif]">
            <span :class="result.hash_matches ? 'text-green-400' : 'text-red-400'">
              {{ result.hash_matches ? '✓' : '✗' }} Seed matches published ID
            </span>
            <span :class="result.selection_matches ? 'text-green-400' : 'text-red-400'">
              {{ result.selection_matches ? '✓' : '✗' }} Card order matches replay
            </span>
          </div>
        </div>
      </div>

      <div v-else class="mt-[32px] rounded-[12px] p-[28px] border bg-[rgba(250,204,21,0.08)] border-[rgba(250,204,21,0.3)]">
        <p class="font-['Cinzel',sans-serif] font-bold text-[20px] text-yellow-400">Not yet available</p>
        <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] mt-[8px]">{{ result.reason }}</p>
      </div>

      <!-- ID / seed -->
      <div class="mt-[32px] flex flex-col gap-[16px]">
        <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[10px] p-[20px]">
          <p class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide">
            Verification ID — published when this batch was created, before generation ran
          </p>
          <div class="flex items-center gap-3 mt-[8px]">
            <p class="font-mono text-[13px] text-[#c9a84c] break-all">{{ verification.id }}</p>
            <button v-if="verification.id" type="button" @click="copyId(verification.id!)"
              class="shrink-0 text-[11px] font-['Jost',sans-serif] font-semibold uppercase px-3 py-1.5 rounded-[4px] border border-[#3d2f6e] text-white hover:border-[#c9a84c] transition-colors">
              {{ idCopied ? 'Copied!' : 'Copy' }}
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
      </div>

      <!-- How to check it yourself -->
      <div class="mt-[32px] bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[10px] p-[24px]">
        <p class="font-['Cinzel',sans-serif] font-bold text-[16px] text-white mb-[12px]">Check it yourself</p>
        <ol class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] leading-relaxed list-decimal list-inside flex flex-col gap-[8px]">
          <li>
            Paste the revealed seed into any SHA-256 calculator. The result should exactly
            match the Verification ID above — that ID was published the moment this batch
            was created, before it was generated, so the seed couldn't have been chosen
            afterward to engineer a particular outcome.
          </li>
          <li>
            The deeper check — that the seed actually produced this batch's exact card
            order, not just that it wasn't swapped — needs replaying the selection against
            the full stock pool available at the time, which we don't publish (it'd mean
            showing everyone our live inventory). The "Card order matches replay" result
            above is us running that exact replay on our own server automatically, the
            moment this page loads.
          </li>
        </ol>
      </div>
    </div>
  </main>

  <Footer />
</template>
