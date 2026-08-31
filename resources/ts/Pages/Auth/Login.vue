<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
const page = usePage<{ props: { flash?: { status?: string; }; }; }>()
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

const mode = ref<'login' | 'register'>( 'login' );

const form = useForm( {
  email: '',
  password: '',
  remember: false,
} );

const registerForm = useForm( {
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
} );

const submit = () => {
  if ( mode.value === 'register' ) {
    registerForm.post( '/register' );
    return;
  }

  form.post( '/login' );
};

function continueWithGoogle() {
  window.location.href = '/auth/google/redirect';
}

// Both useForm() instances have their own `email`/`password` fields with
// different sibling fields (remember vs name/password_confirmation) — these
// let the same inputs bind to whichever form is active for the current mode.
const emailModel = computed( {
  get: () => mode.value === 'login' ? form.email : registerForm.email,
  set: ( v: string ) => { if ( mode.value === 'login' ) form.email = v; else registerForm.email = v; },
} );

const passwordModel = computed( {
  get: () => mode.value === 'login' ? form.password : registerForm.password,
  set: ( v: string ) => { if ( mode.value === 'login' ) form.password = v; else registerForm.password = v; },
} );

const emailError = computed( () => mode.value === 'login' ? form.errors.email : registerForm.errors.email );
const passwordError = computed( () => mode.value === 'login' ? form.errors.password : registerForm.errors.password );
const processing = computed( () => mode.value === 'login' ? form.processing : registerForm.processing );

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
  <Head title="Seller Login" />

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
        class="content-stretch flex flex-col gap-[56px] items-start pb-[120px] pt-[80px] px-8 lg:px-[64px] relative max-w-[600px] mx-auto">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center text-center mx-auto relative shrink-0">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <template v-if="mode === 'login'">
              <span class="leading-[normal]">Log</span>
              <span class="leading-[normal] text-[#c9a84c]"> in</span>
            </template>
            <template v-else>
              <span class="leading-[normal]">Create</span>
              <span class="leading-[normal] text-[#c9a84c]"> account</span>
            </template>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]">
            {{ mode === 'login' ? 'Fill out the details below to access your account.' : 'Just a few details — buy packs and manage your wallet right away.' }}
          </p>
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

            <button type="button" @click="continueWithGoogle"
              class="w-full flex items-center justify-center gap-3 py-3 bg-white text-[#1f1f1f] text-sm font-['Jost',sans-serif] font-semibold rounded-[6px] hover:bg-white/90 transition-colors relative">
              <svg viewBox="0 0 24 24" class="size-4" aria-hidden="true">
                <path fill="#4285F4" d="M23.52 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.47a5.54 5.54 0 0 1-2.4 3.63v3h3.88c2.27-2.09 3.57-5.17 3.57-8.82Z" />
                <path fill="#34A853" d="M12 24c3.24 0 5.96-1.07 7.95-2.91l-3.88-3c-1.07.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.26v3.11A11.99 11.99 0 0 0 12 24Z" />
                <path fill="#FBBC05" d="M5.27 14.28A7.2 7.2 0 0 1 4.89 12c0-.79.14-1.56.38-2.28V6.61H1.26A11.99 11.99 0 0 0 0 12c0 1.94.46 3.77 1.26 5.39l4.01-3.11Z" />
                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.69 1.26 6.61l4.01 3.11C6.22 6.86 8.87 4.75 12 4.75Z" />
              </svg>
              Continue with Google
            </button>

            <div class="flex items-center gap-3 w-full relative">
              <div class="flex-1 h-px bg-[rgba(220,193,117,0.15)]" />
              <span class="font-['Jost',sans-serif] text-[10px] uppercase tracking-wide text-[#71717a]">or</span>
              <div class="flex-1 h-px bg-[rgba(220,193,117,0.15)]" />
            </div>

            <div v-if="mode === 'register'" class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex items-center relative shrink-0">
                <label for="name"
                  class="[word-break:break-word] font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase whitespace-nowrap">
                  Full name</label>
              </div>
              <div
                class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
                <div aria-hidden="true"
                  class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]">
                </div>
                <div class="flex flex-row items-center size-full">
                  <div class="content-stretch flex items-center p-[14px] relative size-full">
                    <input id="name" type="text" v-model="registerForm.name"
                      class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                  </div>
                </div>
                <div v-if="registerForm.errors.name" class="text-[11px] text-red-400 mt-1">
                  {{ registerForm.errors.name }}
                </div>
              </div>
            </div>

            <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
              <div class="content-stretch flex items-center relative shrink-0">
                <label for="email"
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
                    <input id="email" type="email" v-model="emailModel"
                      class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                  </div>
                </div>
                <div v-if="emailError" class="text-[11px] text-red-400 mt-1">
                  {{ emailError }}
                </div>
              </div>
            </div>

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
                    <input id="password" type="password" v-model="passwordModel"
                      class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                  </div>
                </div>
                <div v-if="passwordError" class="text-[11px] text-red-400 mt-1">
                  {{ passwordError }}
                </div>
              </div>
            </div>

            <div v-if="mode === 'register'" class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
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
                    <input id="password_confirmation" type="password" v-model="registerForm.password_confirmation"
                      class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] font-normal leading-[normal] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="mode === 'login'" class="w-full">
              <div class="flex items-center justify-between text-xs text-white/60">
                <label class="inline-flex items-center gap-2">
                  <input v-model="form.remember" type="checkbox"
                    class="rounded border-arcane-border bg-arcane-surface" />
                  <span>Remember me</span>
                </label>
                <Link href="/forgot-password" class="text-white/60 hover:text-white transition duration-150 ease-in-out">
                Forgot password?
                </Link>
              </div>
            </div>

            <div class="content-stretch flex flex-col gap-[16px] items-start relative shrink-0 w-full">
              <button type="submit" @click="submit" :disabled="processing"
                class="content-stretch drop-shadow-[0px_0px_9px_rgba(201,168,76,0.25)] flex h-[56px] items-start justify-center py-[16px] relative rounded-[4px] shrink-0 w-full"
                style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);"
                data-name="Frame">
                <p
                  class="[word-break:break-word] font-['Jost',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#0d0b14] text-[16px] uppercase whitespace-nowrap">
                  <span v-if="processing">{{ mode === 'login' ? 'Signing in...' : 'Creating account...' }}</span>
                  <span v-else>{{ mode === 'login' ? 'Sign in' : 'Create account' }}</span>
                </p>
              </button>

              <p class="w-full text-center font-['Jost',sans-serif] text-[13px] text-[#a3a3a3]">
                <template v-if="mode === 'login'">
                  New here?
                  <button type="button" @click="mode = 'register'" class="text-[#c9a84c] hover:underline">Create an account</button>
                </template>
                <template v-else>
                  Already have an account?
                  <button type="button" @click="mode = 'login'" class="text-[#c9a84c] hover:underline">Log in</button>
                </template>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>