<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

interface StoreDetails {
  name: string;
  description: string | null;
  location: string | null;
  platforms: string[];
  social_links: Record<string, string>;
}

const props = defineProps<{
  affiliateCode: string | null;
  bonusPercentage: number;
  submitted: boolean;
  store: StoreDetails | null;
}>();

const bonusLabel = computed( () => `${ Math.round( props.bonusPercentage * 100 ) }%` );

const affiliateCopied = ref( false );
const copyAffiliateCode = () => {
  if ( ! props.affiliateCode ) return;
  navigator.clipboard.writeText( props.affiliateCode );
  affiliateCopied.value = true;
  setTimeout( () => { affiliateCopied.value = false; }, 2000 );
};

const PLATFORM_FIELDS: { key: string; label: string }[] = [
  { key: 'physical_store', label: 'Physical store' },
  { key: 'ebay', label: 'eBay' },
  { key: 'cardmarket', label: 'Cardmarket' },
  { key: 'whatnot', label: 'Whatnot' },
  { key: 'instagram', label: 'Instagram' },
  { key: 'tiktok_shop', label: 'TikTok Shop' },
  { key: 'website', label: 'Website' },
];

const SOCIAL_FIELDS: { key: string; label: string; placeholder: string }[] = [
  { key: 'website', label: 'Website', placeholder: 'https://yourstore.com' },
  { key: 'instagram', label: 'Instagram', placeholder: 'https://instagram.com/yourstore' },
  { key: 'tiktok', label: 'TikTok', placeholder: 'https://tiktok.com/@yourstore' },
  { key: 'youtube', label: 'YouTube', placeholder: 'https://youtube.com/@yourstore' },
  { key: 'x', label: 'X / Twitter', placeholder: 'https://x.com/yourstore' },
  { key: 'facebook', label: 'Facebook', placeholder: 'https://facebook.com/yourstore' },
  { key: 'discord', label: 'Discord', placeholder: 'https://discord.gg/invite' },
];

const form = useForm({
  description: props.store?.description ?? '',
  location: props.store?.location ?? '',
  platforms: [...( props.store?.platforms ?? [] )] as string[],
  social_links: SOCIAL_FIELDS.reduce(
    (acc, f) => ({ ...acc, [f.key]: props.store?.social_links[f.key] ?? '' }),
    {} as Record<string, string>,
  ),
});

function submit() {
  form.post('/seller/pending', {
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Complete your onboarding" />

  <main class="bg-[#0d0b14] overflow-x-hidden">
    <div class="relative shrink-0">
      <div
        class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full">
        <div class="h-[49px] relative shrink-0">
          <Nav />
        </div>
      </div>
    </div>

    <!-- Submitted: awaiting admin review -->
    <div v-if="submitted" class="relative shrink-0 w-full">
      <div
        class="content-stretch flex flex-col gap-[40px] items-center justify-center pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center relative shrink-0 text-center mx-auto max-w-[560px]">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Almost</span>
            <span class="leading-[normal] text-[#c9a84c]"> there</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]">
            Your dashboard unlocks as soon as our team finishes reviewing your onboarding details.
            We'll email you the moment your storefront goes live. Questions in the meantime?
            Reach us at <a href="mailto:support@arcanepacks.com" class="text-[#c9a84c] underline">support@arcanepacks.com</a>.
          </p>
        </div>

        <div v-if="affiliateCode" class="mx-auto max-w-[560px] w-full flex flex-col items-center gap-[12px]">
          <div
            class="bg-[#13101e] border border-[rgba(124,58,237,0.35)] rounded-[10px] p-[16px] flex items-center gap-[16px] w-full">
            <div class="flex-1 min-w-0">
              <p class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide">
                Your affiliate code
              </p>
              <p class="font-['Cinzel',sans-serif] font-bold text-[20px] text-[#c9a84c] tracking-wide">
                {{ affiliateCode }}
              </p>
            </div>
            <button type="button" @click="copyAffiliateCode"
              class="shrink-0 text-xs font-['Jost',sans-serif] font-semibold uppercase tracking-wide px-4 py-2 rounded-[4px] border border-[#3d2f6e] text-white hover:border-[#c9a84c] transition-colors">
              {{ affiliateCopied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
          <p class="font-['Jost',sans-serif] font-normal leading-relaxed text-[#a3a3a3] text-[14px] text-center">
            You don't have to wait to start using it. Share this code with your customers —
            when they quote it on our <span class="text-white">Sell to Us</span> flow, they get
            <span class="text-[#c9a84c] font-semibold">{{ bonusLabel }} more</span> on their offer,
            and your store earns that same {{ bonusLabel }} back as store credit — automatically
            applied to your invoice(s) once you're live.
          </p>
        </div>
      </div>
    </div>

    <!-- Not yet submitted: the onboarding form -->
    <div v-else class="relative shrink-0 w-full px-8 lg:px-[64px] pb-[120px] pt-[40px]">
      <div class="mx-auto max-w-[900px]">
        <div class="mb-8 text-center">
          <p class="font-['Cinzel',sans-serif] font-bold text-[36px] text-white">
            Complete your <span class="text-[#c9a84c]">onboarding</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal text-[#a3a3a3] text-[16px] mt-2">
            Tell us about {{ store?.name }} and we'll get your storefront reviewed and live.
          </p>
        </div>

        <div v-if="Object.keys(form.errors).length" class="mb-6 bg-[rgba(239,68,68,0.1)] border border-[rgba(239,68,68,0.35)] rounded-[8px] p-4">
          <p class="font-['Jost',sans-serif] font-semibold text-sm text-red-400">
            Couldn't submit — please fix the highlighted field(s) below.
          </p>
        </div>

        <div class="grid gap-6">
          <!-- Details -->
          <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
            <div class="mb-5">
              <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">
                Bio <span class="text-[#c9a84c]">*</span>
              </label>
              <textarea v-model="form.description" rows="4"
                placeholder="A little about you — no more than a short paragraph."
                class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
              <p v-if="form.errors.description" class="text-xs text-red-400 mt-1">{{ form.errors.description }}</p>
            </div>

            <div class="mb-6">
              <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">
                Public location <span class="text-[#c9a84c]">*</span>
              </label>
              <input v-model="form.location" type="text" placeholder="A city, town, physical store, or &quot;Online only&quot;"
                class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
              <p v-if="form.errors.location" class="text-xs text-red-400 mt-1">{{ form.errors.location }}</p>
            </div>

            <p class="font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-3">Platforms you use</p>
            <div class="grid sm:grid-cols-2 gap-2 mb-6">
              <label v-for="field in PLATFORM_FIELDS" :key="field.key"
                class="flex items-center gap-2 px-3 py-2 rounded-[6px] border border-[#3d2f6e] cursor-pointer hover:border-[#c9a84c] transition-colors">
                <input type="checkbox" :value="field.key" v-model="form.platforms"
                  class="accent-[#c9a84c]" />
                <span class="font-['Jost',sans-serif] text-sm text-white">{{ field.label }}</span>
              </label>
            </div>

            <p class="font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-3">Social links</p>
            <div class="grid sm:grid-cols-2 gap-4 mb-6">
              <div v-for="field in SOCIAL_FIELDS" :key="field.key">
                <label class="block font-['Jost',sans-serif] text-xs text-[#a3a3a3] mb-1.5">{{ field.label }}</label>
                <input v-model="form.social_links[field.key]" type="url" :placeholder="field.placeholder"
                  class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-3 py-2 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
              </div>
            </div>

            <button type="button" @click="submit" :disabled="form.processing"
              class="px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14] disabled:opacity-50"
              style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
              {{ form.processing ? 'Submitting…' : 'Submit for review' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>
