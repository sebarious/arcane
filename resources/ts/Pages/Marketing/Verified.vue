<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';

const keyPoints = [
  {
    title: 'Committed before generation',
    body: 'Every batch gets a Verification ID — a SHA-256 hash — published the moment it\'s created. That\'s before a single card has been picked, so the outcome can\'t be chosen after the fact.',
  },
  {
    title: 'Up to 150 randomised draws',
    body: 'The randomiser doesn\'t stop at the first shuffle. It draws candidate packs over and over, all from the same seed, until one lands correctly shaped.',
  },
  {
    title: 'Balanced across every rarity',
    body: 'Each pack tier has a fixed number of common, rare, super, legendary and mythic slots — and cards are spread evenly across price points within each one.',
  },
  {
    title: 'The maths is open',
    body: 'The exact replay logic is public. We run it automatically on every batch page, and you\'re welcome to run it yourself with nothing more than a SHA-256 calculator.',
  },
];

const steps = [
  {
    title: 'Commit',
    body: 'The moment a batch is created, we generate a secret seed, hash it with SHA-256, and publish that hash — the Verification ID — straight away. Nobody, including us, can change what happens next without it showing up as a mismatch later.',
  },
  {
    title: 'Draw',
    body: 'That seed feeds a deterministic randomiser that shuffles the eligible card pool and draws a candidate batch. If the draw doesn\'t fit the batch\'s rarity slots, breaks a duplicate limit, or bunches up at one end of the price range, it\'s thrown out and drawn again — up to 150 times — until one fits properly.',
  },
  {
    title: 'Reveal',
    body: 'As soon as the batch is generated, the seed itself is published alongside it. Anyone can hash that seed and check it matches the ID we committed to before generation ever ran.',
  },
];
</script>

<template>
  <Head title="Verified — Provably Fair Randomiser" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0">
      <div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full">
        <div class="h-[49px] relative shrink-0 w-full">
          <Nav />
        </div>
      </div>
    </div>

    <!-- Hero -->
    <div class="px-8 lg:px-[64px] pt-[60px] pb-[40px] max-w-4xl mx-auto">
      <div class="inline-flex items-center gap-[6px] px-[12px] py-[6px] rounded-[4px] bg-[rgba(34,197,94,0.08)] border border-[rgba(34,197,94,0.25)] mb-[20px]">
        <p class="font-['Jost',sans-serif] font-semibold text-[11px] uppercase text-[#22c55e]">Randomised · SHA-256 · Verifiable</p>
      </div>
      <p class="font-['Cinzel',sans-serif] font-bold text-[40px] lg:text-[56px] text-white leading-tight">
        Every pack is <span class="text-[#c9a84c]">provably fair</span>
      </p>
      <p class="font-['Jost',sans-serif] font-normal text-[#a3a3a3] text-[18px] mt-[16px] max-w-2xl leading-relaxed">
        No card is ever hand-picked for a pack, and no outcome is ever decided after the
        fact. Which card lands in which pack is settled by a randomiser we commit to
        publicly before a batch is generated — and you don't have to take our word for
        it, because the same check we run on ourselves is one you can run too.
      </p>
    </div>

    <!-- Key points -->
    <div class="px-8 lg:px-[64px] pb-[60px] max-w-4xl mx-auto">
      <div class="grid sm:grid-cols-2 gap-[20px]">
        <div v-for=" point in keyPoints " :key=" point.title "
          class="bg-[#13101e] border border-[rgba(201,168,76,0.2)] rounded-[12px] p-[24px]">
          <p class="font-['Cinzel',sans-serif] font-bold text-[17px] text-white mb-[8px]">
            {{ point.title }}
          </p>
          <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] leading-relaxed">
            {{ point.body }}
          </p>
        </div>
      </div>
    </div>

    <!-- How it works -->
    <div class="px-8 lg:px-[64px] pb-[60px] max-w-4xl mx-auto">
      <p class="font-['Cinzel',sans-serif] font-bold text-[28px] lg:text-[34px] text-white leading-tight mb-[8px]">
        How the <span class="text-[#c9a84c]">randomiser</span> works
      </p>
      <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[15px] mb-[28px] max-w-2xl leading-relaxed">
        It's built as a commit-reveal scheme — a standard, well-understood way to prove
        randomness wasn't tampered with, using nothing more exotic than SHA-256.
      </p>

      <div class="grid sm:grid-cols-3 gap-[20px]">
        <div v-for=" (step, i) in steps " :key=" step.title "
          class="bg-[#13101e] border border-[rgba(201,168,76,0.2)] rounded-[12px] p-[24px] relative">
          <span class="font-['Cinzel',sans-serif] font-bold text-[#c9a84c]/40 text-[32px] leading-none">
            0{{ i + 1 }}
          </span>
          <p class="font-['Cinzel',sans-serif] font-bold text-[17px] text-white mt-[12px] mb-[8px]">
            {{ step.title }}
          </p>
          <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] leading-relaxed">
            {{ step.body }}
          </p>
        </div>
      </div>
    </div>

    <!-- Fairness deep dive -->
    <div class="px-8 lg:px-[64px] pb-[60px] max-w-4xl mx-auto">
      <div class="grid sm:grid-cols-2 gap-[20px]">
        <div class="bg-[#1a1628] border border-[rgba(124,58,237,0.35)] rounded-[16px] p-[28px]">
          <p class="font-['Cinzel',sans-serif] font-bold text-[19px] text-white mb-[10px]">
            Why it draws hundreds of times
          </p>
          <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] leading-relaxed">
            A pack tier isn't just "any 125 random cards" — it has a published shape:
            a set number of commons, rares, supers, legendaries and a mythic chase card,
            with a cap on how many copies of any one card can appear. A single shuffle
            won't reliably land on a combination that satisfies all of that at once, so
            the randomiser keeps drawing — deterministically, from the same committed
            seed — until it finds one that does. Every one of those draws is just the
            same public SHA-256 process run again with the next counter, so replaying
            it later reproduces the exact same search, attempt for attempt.
          </p>
        </div>
        <div class="bg-[#1a1628] border border-[rgba(124,58,237,0.35)] rounded-[16px] p-[28px]">
          <p class="font-['Cinzel',sans-serif] font-bold text-[19px] text-white mb-[10px]">
            Balanced across every rarity
          </p>
          <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] leading-relaxed">
            Within each rarity, cards are also drawn evenly across low, mid and high
            price points for that rarity — so a "rare" slot isn't quietly filled with
            whichever rare happens to be cheapest, and a lucky pull isn't limited to
            one narrow price band. Mythics — the rarest chase cards — are the one
            deliberate exception: they're left as a genuinely high-variance pool, on
            purpose, because that's the thrill of a mythic pull.
          </p>
        </div>
      </div>
    </div>

    <!-- Check it yourself CTA -->
    <div class="px-8 lg:px-[64px] pb-[120px] max-w-4xl mx-auto">
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[16px] p-[32px]">
        <p class="font-['Cinzel',sans-serif] font-bold text-[20px] text-white mb-[10px]">
          Check it yourself
        </p>
        <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] leading-relaxed mb-[20px] max-w-2xl">
          Every batch page carries its own "Provably fair — verify" link. Open one and
          you'll see the committed Verification ID, the revealed seed once the batch
          has been generated, and a live pass/fail check confirming the seed hashes to
          that ID and that replaying the draw reproduces that batch's exact card order —
          run automatically, on our own server, the moment the page loads.
        </p>
        <div class="flex gap-[12px] shrink-0">
          <Link href="/stores"
            class="px-6 py-3 rounded-[4px] border border-[#3d2f6e] text-white text-sm font-['Jost',sans-serif] font-semibold uppercase tracking-wide hover:border-[#c9a84c] transition-colors">
            Find a store
          </Link>
          <Link href="/card-lists"
            class="px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14]"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            Browse card lists
          </Link>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>
