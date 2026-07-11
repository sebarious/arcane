<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';
import arcaneLogo from '@/Assets/Link___Arcane.png';
import { Check } from 'lucide-vue-next'

const form = useForm( {
  business_name: '',
  contact_name: '',
  contact_email: '',
  phone: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  postcode: '',
  country: 'GB',
  vat_number: '',
  about: '',
} );

const submit = () => {
  form.post( '/apply' );
};

const generalMotion = {
  initial: { opacity: 0, y: 18 },
  enter: {
    opacity: 1,
    y: 0,
    transition: { delay: 350, duration: 900 },
  },
};
</script>

<template>
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
        class="content-stretch flex flex-col gap-[56px] items-start pb-[120px] pt-[80px] px-8 lg:px-[64px] relative size-full">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-start relative shrink-0 whitespace-nowrap">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Apply</span>
            <span class="leading-[normal] text-[#c9a84c]"> Now</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]">
            It's time for you to be a part of the Arcane.</p>
        </div>
        <div class="-translate-y-1/2 absolute right-[-220px] size-[720px] top-[calc(50%-0.5px)]">
          <div class="absolute inset-[-22.22%]">
            <svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 1040 1040">
              <g filter="url(#filter0_f_145_2261)" id="Ellipse" opacity="0.18">
                <circle cx="520" cy="520" fill="url(#paint0_radial_145_2261)" r="360" />
              </g>
              <defs>
                <filter colorInterpolationFilters="sRGB" filterUnits="userSpaceOnUse" height="1040"
                  id="filter0_f_145_2261" width="1040" x="0" y="0">
                  <feFlood floodOpacity="0" result="BackgroundImageFix" />
                  <feBlend in="SourceGraphic" in2="BackgroundImageFix" mode="normal" result="shape" />
                  <feGaussianBlur result="effect1_foregroundBlur_145_2261" stdDeviation="80" />
                </filter>
                <radialGradient cx="0" cy="0" gradientTransform="translate(520 520) rotate(-90) scale(509.112)"
                  gradientUnits="userSpaceOnUse" id="paint0_radial_145_2261" r="1">
                  <stop stopColor="#7C3AED" />
                  <stop offset="1" stopOpacity="0" />
                </radialGradient>
              </defs>
            </svg>
          </div>
        </div>
        <div class="content-stretch space-y-8 lg:space-y-0 lg:flex lg:gap-[32px] lg:items-start relative lg:shrink-0 w-full">
          <div
            class="bg-[#13101e] content-stretch drop-shadow-[0px_0px_9px_rgba(124,58,237,0.2)] flex flex-col gap-[24px] items-start p-[40px] relative rounded-[16px] shrink-0 flex-1">
            <div aria-hidden
              class="absolute border border-[rgba(124,58,237,0.4)] border-solid inset-0 pointer-events-none rounded-[16px]" />
            <div class="content-stretch flex gap-[24px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="business_name"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Business Name</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="business_name" type="text" v-model="form.business_name"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.business_name " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.business_name }}
                  </div>
                </div>
              </div>
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="contact_name"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Contact Name</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="contact_name" type="text" v-model="form.contact_name"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.contact_name " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.contact_name }}
                  </div>
                </div>
              </div>
            </div>

            <div class="content-stretch flex gap-[24px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="contact_email"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Contact Email</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="contact_email" type="email" v-model="form.contact_email"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.contact_email " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.contact_email }}
                  </div>
                </div>
              </div>
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="phone"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Phone number</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="phone" type="text" v-model="form.phone"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.phone " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.phone }}
                  </div>
                </div>
              </div>
            </div>

            <div class="content-stretch flex gap-[24px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="address_line_1"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Address Line 1</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="address_line_1" v-model="form.address_line_1"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.address_line_1 " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.address_line_1 }}
                  </div>
                </div>
              </div>
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="address_line_2"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Address Line 2</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="address_line_2" type="text" v-model="form.address_line_2"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.address_line_2 " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.address_line_2 }}
                  </div>
                </div>
              </div>
            </div>

            <div class="content-stretch flex gap-[24px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="city"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    City</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="city" v-model="form.city"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.city " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.city }}
                  </div>
                </div>
              </div>
              <div class="content-stretch flex flex-[1_0_0] flex-col gap-[8px] items-start min-w-px relative">
                <div class="content-stretch flex items-center relative shrink-0">
                  <label for="postcode"
                    class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                    Postcode</label>
                </div>
                <div
                  class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                  <div aria-hidden="true"
                    class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                  </div>
                  <div class="flex flex-row items-center size-full">
                    <div class="content-stretch flex items-center p-[14px] relative size-full">
                      <input id="postcode" type="text" v-model="form.postcode"
                        class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                    </div>
                  </div>
                  <div v-if=" form.errors.postcode " class="text-[11px] text-red-400 mt-1">
                    {{ form.errors.postcode }}
                  </div>
                </div>
              </div>
            </div>

            <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex items-center relative shrink-0">
                <label for="about"
                  class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                  Tell us about your store</label>
              </div>

              <div
                class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] relative rounded-[6px] shrink-0 w-full">
                <div aria-hidden="true"
                  class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                </div>
                <div class="flex flex-row items-center size-full">
                  <div class="content-stretch flex items-center p-[14px] relative size-full">
                    <textarea id="about" v-model="form.about" rows="4"
                      class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none"
                      placeholder="Describe your store, how many packs you typically stock, and any social media channels where you sell..."></textarea>
                  </div>
                </div>
                <div v-if=" form.errors.about " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.about }}
                </div>
              </div>
            </div>

            <div class="content-stretch flex flex-col gap-[16px] items-start relative shrink-0 w-full">
              <button type="submit" @click="submit" :disabled="form.processing"
                class="content-stretch drop-shadow-[0px_0px_9px_rgba(201,168,76,0.25)] flex h-[56px] items-start justify-center py-[16px] relative rounded-[4px] shrink-0 w-full"
                style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);"
                data-name="Frame">
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#0d0b14] text-[16px] uppercase whitespace-nowrap">
                  <span v-if=" form.processing ">Submitting…</span>
                  <span v-else>Send application</span>
                </p>
              </button>
            </div>
          </div>
          <div
            class="bg-gradient-to-r content-stretch drop-shadow-[0px_0px_10px_rgba(124,58,237,0.2)] flex flex-col from-[#1a0d2e] gap-[40px] items-start p-[40px] relative rounded-[12px] shrink-0 to-[#0d0b14]">
            <div aria-hidden
              class="absolute border border-[rgba(124,58,237,0.4)] border-solid inset-0 pointer-events-none rounded-[12px]" />
            <div class="content-stretch flex flex-col gap-[24px] items-start relative shrink-0 w-full">
              <div class="h-[35px] relative shrink-0 w-[32px]">
                <img alt="Arcane" class="absolute inset-0 max-w-none object-cover pointer-events-none size-full"
                  :src="arcaneLogo" loading="lazy" />
              </div>
              <p
                class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[28px] text-white">
                Why Sell With Arcane?</p>
              <div class="content-stretch flex flex-col gap-[20px] items-start relative shrink-0 w-full">
                <div class="content-stretch flex gap-[12px] items-center relative shrink-0 w-full">
                  <Check class="text-[#c9a84c]" />
                  <p
                    class="[word-break:break-word] flex-[1_0_0] font-['Jost',sans-serif] font-normal leading-[normal] min-w-px relative text-[#a3a3a3] text-[16px]">
                    Free to join - no setup or listing fees</p>
                </div>
                <div class="content-stretch flex gap-[12px] items-center relative shrink-0 w-full">
                  <Check class="text-[#c9a84c]" />
                  <p
                    class="[word-break:break-word] flex-[1_0_0] font-['Jost',sans-serif] font-normal leading-[normal] min-w-px relative text-[#a3a3a3] text-[16px]">
                    Real-time pull tracking and analytics</p>
                </div>
                <div class="content-stretch flex gap-[12px] items-center relative shrink-0 w-full">
                  <Check class="text-[#c9a84c]" />
                  <p
                    class="[word-break:break-word] flex-[1_0_0] font-['Jost',sans-serif] font-normal leading-[normal] min-w-px relative text-[#a3a3a3] text-[16px]">
                    Reach thousands of UK collectors</p>
                </div>
                <div class="content-stretch flex gap-[12px] items-center relative shrink-0 w-full">
                  <Check class="text-[#c9a84c]" />
                  <p
                    class="[word-break:break-word] flex-[1_0_0] font-['Jost',sans-serif] font-normal leading-[normal] min-w-px relative text-[#a3a3a3] text-[16px]">
                    Automated pack sequencing - no manual work</p>
                </div>
                <div class="content-stretch flex gap-[12px] items-center relative shrink-0 w-full">
                  <Check class="text-[#c9a84c]" />
                  <p
                    class="[word-break:break-word] flex-[1_0_0] font-['Jost',sans-serif] font-normal leading-[normal] min-w-px relative text-[#a3a3a3] text-[16px]">
                    Dedicated seller support team</p>
                </div>
              </div>
            </div>
            <div class="h-0 relative shrink-0 w-full" data-name="Line">
              <div class="absolute inset-[-1px_0_0_0]">
                <svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 448 1">
                  <line id="Line" stroke="var(--stroke-0, rgba(220, 193, 117, 0.15))" strokeOpacity="0.101961" x2="448"
                    y1="0.5" y2="0.5" />
                </svg>
              </div>
            </div>
            <div class="content-stretch flex flex-col gap-[24px] items-start relative shrink-0 w-full">
              <p
                class="[word-break:break-word] font-['Cinzel',sans-serif] font-bold leading-[normal] relative shrink-0 text-[20px] text-white whitespace-nowrap">
                Already a seller?</p>
              <a href="/login"
                class="content-stretch flex items-start justify-center py-[14px] relative rounded-[4px] shrink-0 w-full">
                <div aria-hidden
                  class="absolute border border-[#7b4fe9] border-solid inset-0 pointer-events-none rounded-[4px]" />
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#7b4fe9] text-[12px] uppercase whitespace-nowrap">
                  Sign In to Seller Dashboard</p>
              </a>
              <p
                class="[word-break:break-word] font-['Jost',sans-serif] font-normal leading-[0] min-w-full relative shrink-0 text-[14px] text-[rgba(255,255,255,0.35)] w-[min-content]">
                <span class="leading-[normal]">Questions? Email us at </span>
                <a href="mailto:support@arcanepacks.com"
                  class="leading-[normal] text-[#c9a84c]">support@arcanepacks.com</a>
              </p>
            </div>
            <div
              class="absolute bottom-[-110.82px] flex items-center justify-center right-[-198.14px] size-[498.145px]">
              <div class="-rotate-12 flex-none">
                <div class="opacity-8 relative shadow-[0px_0px_28px_0px_rgba(201,168,76,0.2)] size-[420px]">
                  <img alt="Arcane" class="absolute inset-0 max-w-none object-contain pointer-events-none size-full"
                    loading="lazy"
                    :src="arcaneLogo" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>