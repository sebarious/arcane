<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { Head, useForm } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

const props = defineProps<{ affiliateCode?: string | null }>();

interface SearchResult {
  product_id: string;
  card_name: string;
  card_number: string | null;
  set_name: string | null;
  rarity: string | null;
  image_url: string | null;
  market_value_pence: number | null;
  band: string | null;
  unit_offer_pence: number | null;
}

interface SelectedCard extends SearchResult {
  quantity: number;
}

const MAX_ITEMS = 100;

const checklist = [
  'Prices are using our Live Market Pricing aggregation through PulseTCG.',
  'Cards must be in a near-mint condition or better.',
  'Cards must be sent, checked and price confirmed by a member of our team.',
  'Fast, secure payment on confirmation of the price agreed with you.',
  'No fees — you just box up and send your cards to us, we do the rest.',
  'Maximum of 100 cards per submission.',
];

const nameQuery = ref( '' );
const numberQuery = ref( '' );
const results = ref<SearchResult[]>( [] );
const searching = ref( false );
const searchError = ref( '' );
const hasSearched = ref( false );

const selected = ref<SelectedCard[]>( [] );

const affiliateCodeInput = ref( '' );
const affiliateApplying = ref( false );
const affiliateValid = ref<boolean | null>( null );
const affiliateError = ref( '' );
const affiliateStoreName = ref<string | null>( null );
const affiliateBonusPercentage = ref( 0 );

const bonusPercentLabel = computed( () => `${ Math.round( affiliateBonusPercentage.value * 100 ) }%` );

async function applyAffiliateCode() {
  const code = affiliateCodeInput.value.trim();
  if ( ! code ) return;

  affiliateApplying.value = true;
  affiliateError.value = '';

  try {
    const { data } = await axios.get( '/sell/verify-affiliate-code', { params: { code } } );
    if ( data.valid ) {
      affiliateValid.value = true;
      affiliateStoreName.value = data.store_name;
      affiliateBonusPercentage.value = data.bonus_percentage;
    } else {
      affiliateValid.value = false;
      affiliateStoreName.value = null;
      affiliateError.value = data.message ?? "That code isn't valid.";
    }
  } catch ( e ) {
    affiliateValid.value = false;
    affiliateStoreName.value = null;
    affiliateError.value = 'Could not verify that code — please try again.';
  } finally {
    affiliateApplying.value = false;
  }
}

function clearAffiliateCode() {
  affiliateCodeInput.value = '';
  affiliateValid.value = null;
  affiliateStoreName.value = null;
  affiliateError.value = '';
  affiliateBonusPercentage.value = 0;
}

// Arriving via a store's /a/{slug} link — prefill and validate straight away
// so the bonus is already showing, not just the code sitting in the box.
onMounted( () => {
  if ( props.affiliateCode ) {
    affiliateCodeInput.value = props.affiliateCode;
    applyAffiliateCode();
  }
} );

let debounceTimer: ReturnType<typeof setTimeout> | undefined;

function scheduleSearch() {
  if ( debounceTimer ) clearTimeout( debounceTimer );
  debounceTimer = setTimeout( runSearch, 400 );
}

async function runSearch() {
  const q = nameQuery.value.trim();
  const number = numberQuery.value.trim();

  if ( q.length < 2 && number.length === 0 ) {
    results.value = [];
    hasSearched.value = false;
    return;
  }

  searching.value = true;
  searchError.value = '';
  hasSearched.value = true;

  try {
    const { data } = await axios.get( '/sell/search-cards', { params: { q, number } } );
    results.value = data.data ?? [];
  } catch ( e ) {
    searchError.value = 'Search failed — please try again.';
    results.value = [];
  } finally {
    searching.value = false;
  }
}

const totalItemCount = computed( () =>
  selected.value.reduce( ( sum, c ) => sum + c.quantity, 0 )
);

function addCard( card: SearchResult ) {
  if ( totalItemCount.value >= MAX_ITEMS ) return;

  const existing = selected.value.find( ( c ) => c.product_id === card.product_id );
  if ( existing ) {
    existing.quantity += 1;
  } else {
    selected.value.push( { ...card, quantity: 1 } );
  }

  nameQuery.value = '';
  numberQuery.value = '';
  results.value = [];
  hasSearched.value = false;
}

function removeCard( productId: string ) {
  selected.value = selected.value.filter( ( c ) => c.product_id !== productId );
}

function formatPence( pence: number | null ): string {
  if ( pence === null ) return '—';
  return '£' + ( pence / 100 ).toFixed( 2 );
}

function boostedPence( pence: number | null ): number | null {
  if ( pence === null || ! affiliateValid.value ) return null;
  return Math.round( pence * ( 1 + affiliateBonusPercentage.value ) );
}

function baseLineOfferPence( card: SelectedCard ): number {
  return ( card.unit_offer_pence ?? 0 ) * card.quantity;
}

function lineOfferPence( card: SelectedCard ): number {
  const base = baseLineOfferPence( card );
  return affiliateValid.value ? Math.round( base * ( 1 + affiliateBonusPercentage.value ) ) : base;
}

const totalBaseOfferPence = computed( () =>
  selected.value.reduce( ( sum, c ) => sum + baseLineOfferPence( c ), 0 )
);

const totalOfferPence = computed( () =>
  selected.value.reduce( ( sum, c ) => sum + lineOfferPence( c ), 0 )
);

const form = useForm( {
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  customer_postcode: '',
  description: '',
  affiliate_code: '',
  items: [] as { product_id: string; quantity: number }[],
} );

const submit = () => {
  form.items = selected.value.map( ( c ) => ( { product_id: c.product_id, quantity: c.quantity } ) );
  form.affiliate_code = affiliateValid.value ? affiliateCodeInput.value.trim() : '';
  form.post( '/sell' );
};

function itemError( index: number ): string | undefined {
  return ( form.errors as Record<string, string> )[ `items.${ index }.product_id` ];
}
</script>

<template>
  <Head title="Sell to Us" />

  <main class="bg-[#0d0b14] overflow-x-hidden">
    <div class="relative shrink-0">
      <div
        class="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative size-full">
        <div class="h-[49px] relative shrink-0">
          <Nav />
        </div>
      </div>
    </div>

    <div class="relative shrink-0 w-full">
      <div
        class="content-stretch flex flex-col gap-[40px] items-start pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-start relative shrink-0">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Sell</span>
            <span class="leading-[normal] text-[#c9a84c]"> to us</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px] max-w-2xl">
            Search for your cards below, add a quantity, and see our offer instantly —
            based on live market pricing.</p>
        </div>

        <!-- Eligibility banner -->
        <div
          class="bg-[#2a1a1a] border border-[rgba(220,80,80,0.4)] rounded-[12px] p-[24px] relative shrink-0 w-full">
          <p class="font-['Jost',sans-serif] font-semibold leading-[normal] text-[15px] text-white">
            We only currently buy <span class="text-[#e8a0a0]">Full Art, Illustration Rare, or higher tier</span> cards.
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] text-[14px] text-[#c9a3a3] mt-[6px]">
            Please ensure all cards submitted meet this criteria. Any cards sent to us that do not meet this
            criteria will need you to arrange collection to be returned to you.
          </p>
        </div>

        <!-- Checklist -->
        <div class="grid sm:grid-cols-2 gap-[16px] w-full">
          <div v-for=" point in checklist " :key=" point "
            class="content-stretch flex items-start gap-[12px] bg-[#13101e] border border-[rgba(124,58,237,0.25)] rounded-[10px] p-[16px]">
            <svg class="shrink-0 mt-[2px]" width="18" height="18" viewBox="0 0 20 20" fill="none">
              <circle cx="10" cy="10" r="10" fill="#c9a84c" opacity="0.2" />
              <path d="M6 10.5L8.5 13L14 7" stroke="#c9a84c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <p class="font-['Jost',sans-serif] font-normal leading-[normal] text-[14px] text-[#d8d3e0]">
              {{ point }}
            </p>
          </div>
        </div>

        <div class="content-stretch space-y-8 lg:space-y-0 lg:flex lg:gap-[32px] lg:items-start relative lg:shrink-0 w-full">
          <div
            class="bg-[#13101e] drop-shadow-[0px_0px_9px_rgba(124,58,237,0.2)] flex flex-col gap-[24px] items-start p-[40px] relative rounded-[16px] shrink-0 flex-1">
            <div aria-hidden
              class="absolute border border-[rgba(124,58,237,0.4)] border-solid inset-0 pointer-events-none rounded-[16px]" />

            <!-- Affiliate code -->
            <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <label
                class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                Affiliate code <small class="normal-case">(optional — get {{ bonusPercentLabel || '5%' }} more)</small></label>

              <div class="flex gap-[12px] w-full max-w-md">
                <div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] flex-1">
                  <div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]" />
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input type="text" v-model=" affiliateCodeInput " @keyup.enter=" applyAffiliateCode "
                        :disabled=" affiliateValid === true "
                        placeholder="e.g. ARCANE123"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white uppercase font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white placeholder:normal-case focus:ring-0 focus:outline-none disabled:opacity-60" />
                    </div>
                  </div>
                </div>

                <button v-if=" affiliateValid !== true " type="button" @click=" applyAffiliateCode "
                  :disabled=" affiliateApplying || ! affiliateCodeInput.trim() "
                  class="shrink-0 px-5 h-[48px] rounded-[6px] border border-[#3d2f6e] text-white text-sm font-['Jost',sans-serif] font-semibold uppercase tracking-wide hover:border-[#c9a84c] transition-colors disabled:opacity-50">
                  {{ affiliateApplying ? 'Checking…' : 'Apply' }}
                </button>
                <button v-else type="button" @click=" clearAffiliateCode "
                  class="shrink-0 px-5 h-[48px] rounded-[6px] border border-[#3d2f6e] text-[#a3a3a3] text-sm font-['Jost',sans-serif] font-semibold uppercase tracking-wide hover:border-red-400 hover:text-red-400 transition-colors">
                  Remove
                </button>
              </div>

              <p v-if=" affiliateValid === true " class="text-[13px] text-[#7fd4a0] font-['Jost',sans-serif]">
                ✓ Applied — {{ affiliateStoreName }}'s code. You'll get {{ bonusPercentLabel }} more on every offer below.
              </p>
              <p v-else-if=" affiliateValid === false " class="text-[13px] text-red-400 font-['Jost',sans-serif]">
                {{ affiliateError }}
              </p>
            </div>

            <div class="w-full h-px bg-[rgba(124,58,237,0.2)] shrink-0" />

            <!-- Card search -->
            <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <label
                class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                Find your cards</label>

              <div class="flex flex-col sm:flex-row gap-[12px] w-full">
                <div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] flex-[3] w-full">
                  <div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]" />
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input type="text" v-model=" nameQuery " @input=" scheduleSearch "
                        placeholder="Card name, e.g. Charizard ex"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                </div>

                <div class="flex sm:hidden items-center gap-[10px] w-full">
                  <div class="flex-1 h-px bg-[#3d2f6e]" />
                  <span class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase">Or</span>
                  <div class="flex-1 h-px bg-[#3d2f6e]" />
                </div>

                <div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] flex-[1] w-full">
                  <div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]" />
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input type="text" v-model=" numberQuery " @input=" scheduleSearch "
                        placeholder="Number, e.g. 199/165"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Results -->
              <div v-if=" searching " class="text-[13px] text-[#a3a3a3] font-['Jost',sans-serif] mt-[4px]">
                Searching…
              </div>
              <div v-else-if=" searchError " class="text-[13px] text-red-400 font-['Jost',sans-serif] mt-[4px]">
                {{ searchError }}
              </div>
              <div v-else-if=" hasSearched && results.length === 0 " class="text-[13px] text-[#a3a3a3] font-['Jost',sans-serif] mt-[4px]">
                No matches found.
              </div>

              <div v-if=" results.length " class="flex flex-col gap-[8px] w-full max-h-[360px] overflow-y-auto mt-[4px]">
                <button v-for=" card in results " :key=" card.product_id " type="button"
                  @click=" addCard( card ) "
                  class="flex items-center gap-[12px] p-[10px] rounded-[8px] border text-left transition-colors border-[#3d2f6e] hover:border-[#c9a84c] bg-[#1a1628] cursor-pointer">
                  <img v-if=" card.image_url " :src=" card.image_url " class="w-[36px] h-[50px] object-cover rounded-[4px]" />
                  <div class="flex-1 min-w-0">
                    <p class="font-['Jost',sans-serif] text-[14px] text-white truncate">{{ card.card_name }}</p>
                    <p class="font-['Jost',sans-serif] text-[12px] text-[#a3a3a3] truncate">
                      {{ card.set_name }} · {{ card.card_number }} · {{ card.rarity }}
                    </p>
                  </div>
                  <div class="text-right shrink-0">
                    <p v-if=" affiliateValid " class="font-['Jost',sans-serif] text-[11px] text-[#71717a] line-through">
                      Offer {{ formatPence( card.unit_offer_pence ) }}
                    </p>
                    <p class="font-['Jost',sans-serif] text-[13px] text-[#c9a84c]">
                      Offer {{ formatPence( affiliateValid ? boostedPence( card.unit_offer_pence ) : card.unit_offer_pence ) }}
                      <span v-if=" affiliateValid " class="text-[10px] text-[#7fd4a0]">(+{{ bonusPercentLabel }})</span>
                    </p>
                    <p class="font-['Jost',sans-serif] text-[11px] text-[#71717a]">
                      Market {{ formatPence( card.market_value_pence ) }}
                    </p>
                  </div>
                </button>
              </div>
            </div>

            <!-- Selected cards -->
            <div v-if=" selected.length " class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <label
                class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                Your cards ({{ totalItemCount }}/{{ MAX_ITEMS }})</label>

              <div class="flex flex-col gap-[8px] w-full">
                <div v-for=" ( card, index ) in selected " :key=" card.product_id "
                  class="flex items-center gap-[12px] p-[12px] rounded-[8px] border border-[#3d2f6e] bg-[#1a1628]">
                  <img v-if=" card.image_url " :src=" card.image_url " class="w-[36px] h-[50px] object-cover rounded-[4px]" />
                  <div class="flex-1 min-w-0">
                    <p class="font-['Jost',sans-serif] text-[14px] text-white truncate">{{ card.card_name }}</p>
                    <p class="font-['Jost',sans-serif] text-[12px] text-[#a3a3a3] truncate">
                      {{ card.set_name }} · {{ card.card_number }}
                    </p>
                    <p v-if=" itemError( index ) " class="text-[11px] text-red-400 mt-[2px]">{{ itemError( index ) }}</p>
                  </div>
                  <input type="number" min="1" max="999" v-model.number=" card.quantity "
                    class="w-[56px] bg-[#0d0b14] border border-[#3d2f6e] rounded-[4px] text-center text-white text-[14px] py-[6px] font-['Jost',sans-serif]" />
                  <div class="w-[92px] text-right shrink-0">
                    <p v-if=" affiliateValid " class="font-['Jost',sans-serif] text-[11px] text-[#71717a] line-through leading-tight">
                      {{ formatPence( baseLineOfferPence( card ) ) }}
                    </p>
                    <p class="font-['Jost',sans-serif] text-[14px] text-[#c9a84c] leading-tight">
                      {{ formatPence( lineOfferPence( card ) ) }}
                      <span v-if=" affiliateValid " class="text-[10px] text-[#7fd4a0]">(+{{ bonusPercentLabel }})</span>
                    </p>
                  </div>
                  <button type="button" @click=" removeCard( card.product_id ) "
                    class="text-[#a3a3a3] hover:text-red-400 shrink-0 text-[18px] leading-none px-[4px]">
                    ×
                  </button>
                </div>
              </div>

              <div class="flex items-center justify-between w-full pt-[8px] border-t border-[#3d2f6e]">
                <p class="font-['Jost',sans-serif] text-[15px] text-white">Total offer</p>
                <div class="text-right">
                  <p v-if=" affiliateValid " class="font-['Jost',sans-serif] text-[13px] text-[#71717a] line-through">
                    {{ formatPence( totalBaseOfferPence ) }}
                  </p>
                  <p class="font-['Jost',sans-serif] font-bold text-[20px] text-[#c9a84c]">
                    {{ formatPence( totalOfferPence ) }}
                    <span v-if=" affiliateValid " class="text-[12px] font-normal text-[#7fd4a0]">(+{{ bonusPercentLabel }})</span>
                  </p>
                </div>
              </div>
              <div v-if=" form.errors.items " class="text-[11px] text-red-400">{{ form.errors.items }}</div>
            </div>

            <div class="w-full h-px bg-[rgba(124,58,237,0.2)] shrink-0" />

            <!-- Customer details -->
            <div class="content-stretch flex flex-col sm:flex-row gap-[24px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative w-full">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="customer_name"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Name</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="customer_name" type="text" v-model="form.customer_name"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.customer_name " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.customer_name }}
                  </div>
                </div>
              </div>
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative w-full">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="customer_email"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Email address</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="customer_email" type="email" v-model="form.customer_email"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.customer_email " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.customer_email }}
                  </div>
                </div>
              </div>
            </div>

            <div class="content-stretch flex flex-col sm:flex-row gap-[24px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative w-full">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="customer_phone"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Phone number <small>(optional)</small></label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="customer_phone" type="text" v-model="form.customer_phone"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.customer_phone " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.customer_phone }}
                  </div>
                </div>
              </div>
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative w-full">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="customer_postcode"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Postcode <small>(optional)</small></label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="customer_postcode" type="text" v-model="form.customer_postcode"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.customer_postcode " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.customer_postcode }}
                  </div>
                </div>
              </div>
            </div>

            <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex items-center relative shrink-0">
                <label for="description"
                  class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                  Anything else we should know? <small>(optional)</small></label>
              </div>

              <div
                class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] relative rounded-[6px] shrink-0 w-full">
                <div aria-hidden="true"
                  class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                </div>
                <div class="flex flex-row items-center size-full">
                  <div class="content-stretch flex items-center p-[14px] relative size-full">
                    <textarea id="description" v-model="form.description" rows="3"
                      class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"
                      placeholder="Any condition notes or other details…"></textarea>
                  </div>
                </div>
                <div v-if=" form.errors.description " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.description }}
                </div>
              </div>
            </div>

            <div class="content-stretch flex flex-col gap-[16px] items-start relative shrink-0 w-full">
              <button type="submit" @click="submit" :disabled="form.processing || selected.length === 0"
                class="content-stretch drop-shadow-[0px_0px_9px_rgba(201,168,76,0.25)] flex h-[56px] items-start justify-center py-[16px] relative rounded-[4px] shrink-0 w-full disabled:opacity-50"
                style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);"
                data-name="Frame">
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#0d0b14] text-[16px] uppercase whitespace-nowrap">
                  <span v-if=" form.processing ">Submitting…</span>
                  <span v-else-if=" selected.length === 0 ">Add a card to continue</span>
                  <span v-else>Submit ({{ formatPence( totalOfferPence ) }})</span>
                </p>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>
