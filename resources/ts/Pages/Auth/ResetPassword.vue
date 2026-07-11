<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

interface Props {
  token: string;
  email: string | null;
}

const props = defineProps<Props>();

const form = useForm( {
  token: props.token,
  email: props.email ?? '',
  password: '',
  password_confirmation: '',
} );

const success = ref( false );

const submit = () => {
  form.post( '/reset-password', {
    onSuccess: () => {
      form.reset();
      success.value = true;
    },
  } );
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

    <div class="relative shrink-0 w-full" v-motion="generalMotion">
      <div
        class="content-stretch flex flex-col gap-[56px] items-start pb-[120px] pt-[80px] px-8 lg:px-[64px] relative max-w-[600px] mx-auto">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center text-center mx-auto relative shrink-0">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Reset</span>
            <span class="leading-[normal] text-[#c9a84c]"> Password</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]">
            Fill out the details below to reset your password.</p>
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
        <div
          class="content-stretch space-y-8 lg:space-y-0 lg:flex lg:gap-[32px] lg:items-start relative lg:shrink-0 w-full max-w-[600px] mx-auto">
          <div
            class="bg-[#13101e] content-stretch drop-shadow-[0px_0px_9px_rgba(124,58,237,0.2)] flex flex-col gap-[24px] items-start p-[40px] relative rounded-[16px] shrink-0 flex-1">
            <div aria-hidden
              class="absolute border border-[rgba(124,58,237,0.4)] border-solid inset-0 pointer-events-none rounded-[16px]" />

            <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex items-center relative shrink-0">
                <label for="password"
                  class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                  Password</label>
              </div>
              <div
                class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                <div aria-hidden="true"
                  class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                </div>
                <div class="flex flex-row items-center size-full">
                  <div class="content-stretch flex items-center p-[14px] relative size-full">
                    <input id="password" type="password" v-model="form.password"
                      class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                  </div>
                </div>
                <div v-if=" form.errors.password " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.password }}
                </div>
              </div>
            </div>

            <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex items-center relative shrink-0">
                <label for="password_confirmation"
                  class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                  Confirm password</label>
              </div>
              <div
                class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                <div aria-hidden="true"
                  class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                </div>
                <div class="flex flex-row items-center size-full">
                  <div class="content-stretch flex items-center p-[14px] relative size-full">
                    <input id="password_confirmation" type="password" v-model="form.password_confirmation"
                      class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                  </div>
                </div>
                <div v-if=" form.errors.password_confirmation " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.password_confirmation }}
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
                  <span v-if=" form.processing ">Resetting...</span>
                  <span v-else>Reset Password</span>
                </p>
              </button>
            </div>

            <template v-if=" success ">
              <div class="text-[11px] text-emerald-400 mt-1">
                Your password has been reset successfully.
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>