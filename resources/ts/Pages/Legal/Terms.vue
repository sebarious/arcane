<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';

defineProps<{ contactEmail: string | null }>();

const sections = [
  {
    title: '1. About these terms',
    body: [
      'These terms govern your use of Arcane (arcanepacks.com), including buying mystery packs from stores listed on our storefront, submitting cards to us for sale via the "Sell to Us" service, and participating in the affiliate program described below.',
      'By using the site you agree to these terms. If you don\'t agree, please don\'t use the site.',
    ],
  },
  {
    title: '2. Mystery packs',
    body: [
      'Each pack contains one authenticated, near-mint (or better) toploaded card, sealed into the pack by us. The specific card you receive is randomised and cannot be chosen, exchanged, or guaranteed in advance.',
      'Before purchase, a live "card pool" is shown for each batch listing every remaining card and its estimated pull odds by rarity band. This pool updates as packs are sold, but is not a guarantee that any specific card will remain available by the time you complete a purchase.',
      'Because packs are a randomised product, we do not offer refunds or exchanges on the basis that you didn\'t receive the card you hoped for. Faulty, damaged, or misdescribed items are covered under section 5 (Returns) below.',
    ],
  },
  {
    title: '3. Batch Merge and Buy-Back Promise',
    body: [
      'Stores may, at their discretion, merge the remaining unsold packs of one batch into another batch of the same store, restoring more balanced pull odds. Merging does not affect any pack you have already purchased.',
      'Where advertised, our Buy-Back Promise allows a store to have us buy back their remaining eligible unsold cards at a percentage of live market value. This is an arrangement between a store and Arcane and does not create any right for individual customers.',
    ],
  },
  {
    title: '4. Orders and payment',
    body: [
      'All prices are shown in GBP and include any taxes unless stated otherwise. Payment is taken at the time of order. We reserve the right to cancel and refund an order if a card pool listing was inaccurate, out of stock, or mispriced due to an error.',
    ],
  },
  {
    title: '5. Returns',
    body: [
      'If a pack or card arrives damaged, faulty, or not as described (for example, not sealed correctly or not matching its listed condition), contact us and we\'ll arrange a replacement, credit, or refund.',
      'This does not affect your statutory rights as a consumer under UK law.',
    ],
  },
  {
    title: '6. Selling cards to us ("Sell to Us")',
    body: [
      'We currently buy Full Art, Illustration Rare, or higher-tier English-language Pokémon cards, in near-mint condition or better, up to the price ceiling shown on the Sell to Us page at the time of your submission.',
      'The offer shown when you search for a card is indicative, based on live market pricing at that moment. Your final offer is confirmed once we\'ve received and inspected your cards — if a card doesn\'t match the condition or description used to generate the indicative offer, we\'ll contact you with a revised offer before proceeding.',
      'By submitting cards to us, you confirm that you are the legal owner of those cards and have the right to sell them.',
      'We reserve the right to decline any submission or any individual card within a submission, at our discretion.',
    ],
  },
  {
    title: '7. Affiliate program',
    body: [
      'Stores listed on Arcane are issued a unique affiliate code, shown on their public profile page. Anyone — including a store\'s own customers — can quote a valid affiliate code when submitting cards via "Sell to Us" to receive a bonus uplift on their offer, shown on the affiliate program page and applied automatically at submission.',
      'The affiliate bonus percentage may change from time to time; the rate applied is the one in effect at the time you submit your cards. We reserve the right to suspend or revoke an affiliate code, or the program itself, at any time.',
      'The affiliate program does not currently involve any direct payment to stores for referrals — it is a discount passed to the customer quoting the code.',
    ],
  },
  {
    title: '8. Intellectual property',
    body: [
      'Arcane is an independent business and is not affiliated with, endorsed by, or sponsored by The Pokémon Company, Nintendo, Creatures Inc., or Game Freak. Pokémon and all associated card names, artwork, and trademarks are the property of their respective owners.',
    ],
  },
  {
    title: '9. Liability',
    body: [
      'Nothing in these terms limits our liability where it would be unlawful to do so (for example, for death or personal injury caused by negligence, or fraud). Subject to that, our liability to you is limited to the amount you paid for the relevant order or submission.',
    ],
  },
  {
    title: '10. Changes to these terms',
    body: [
      'We may update these terms from time to time to reflect changes to our services or for legal or regulatory reasons. The version in effect at the time of your order or submission applies.',
    ],
  },
  {
    title: '11. Governing law',
    body: [
      'These terms are governed by the laws of England and Wales, and any disputes will be subject to the exclusive jurisdiction of the courts of England and Wales.',
    ],
  },
];
</script>

<template>
  <Head title="Terms & Conditions" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0">
      <div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full">
        <div class="h-[49px] relative shrink-0 w-full">
          <Nav />
        </div>
      </div>
    </div>

    <div class="px-8 lg:px-[64px] pt-[60px] pb-[120px] max-w-3xl mx-auto">
      <p class="font-['Cinzel',sans-serif] font-bold text-[40px] lg:text-[48px] text-white leading-tight">
        Terms <span class="text-[#c9a84c]">&amp; Conditions</span>
      </p>
      <p class="font-['Jost',sans-serif] text-[#a3a3a3] text-[14px] mt-[8px]">
        Last updated {{ new Date().toLocaleDateString( 'en-GB', { year: 'numeric', month: 'long' } ) }}
      </p>

      <div class="flex flex-col gap-[36px] mt-[48px]">
        <section v-for=" section in sections " :key=" section.title ">
          <h2 class="font-['Cinzel',sans-serif] font-bold text-[20px] text-[#c9a84c] mb-[12px]">
            {{ section.title }}
          </h2>
          <p v-for=" (para, i) in section.body " :key=" i "
            class="font-['Jost',sans-serif] text-[15px] leading-relaxed text-[#d8d3e0] mb-[10px] last:mb-0">
            {{ para }}
          </p>
        </section>

        <section>
          <h2 class="font-['Cinzel',sans-serif] font-bold text-[20px] text-[#c9a84c] mb-[12px]">
            12. Contact us
          </h2>
          <p class="font-['Jost',sans-serif] text-[15px] leading-relaxed text-[#d8d3e0]">
            Questions about these terms? Email us at
            <a v-if=" contactEmail " :href=" `mailto:${ contactEmail }` " class="text-[#c9a84c] hover:underline">{{ contactEmail }}</a>.
          </p>
        </section>
      </div>
    </div>
  </main>

  <Footer />
</template>
