<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Footer from '@/Components/Layout/Footer.vue';
import Nav from '@/Components/Layout/Nav.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/affiliate/signup');
};
</script>

<template>
  <Head title="Become an Affiliate" />

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
        class="content-stretch flex flex-col gap-[40px] items-start pb-[120px] pt-[80px] px-8 lg:px-[64px] relative max-w-[600px] mx-auto">
        <div
          class="[word-break:break-word] content-stretch flex flex-col gap-[12px] items-center text-center mx-auto relative shrink-0 w-full">
          <p class="font-['Cinzel',sans-serif] font-bold leading-[0] relative shrink-0 text-[48px] text-white">
            <span class="leading-[normal]">Become an</span>
            <span class="leading-[normal] text-[#c9a84c]"> affiliate</span>
          </p>
          <p class="font-['Jost',sans-serif] font-normal leading-[normal] relative shrink-0 text-[#a3a3a3] text-[18px]">
            Get your own affiliate code — no store required. Sign up below, then wait for
            approval before your dashboard unlocks.
          </p>
        </div>

        <div
          class="bg-[#13101e] content-stretch drop-shadow-[0px_0px_9px_rgba(124,58,237,0.2)] flex flex-col gap-[24px] items-start p-[40px] relative rounded-[16px] shrink-0 w-full">
          <div aria-hidden
            class="absolute border border-[rgba(124,58,237,0.4)] border-solid inset-0 pointer-events-none rounded-[16px]" />

          <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
            <label for="name"
              class="font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase">
              Name</label>
            <div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
              <div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]" />
              <div class="flex items-center p-[14px] size-full">
                <input id="name" type="text" v-model="form.name"
                  class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
              </div>
            </div>
            <p v-if="form.errors.name" class="text-[11px] text-red-400">{{ form.errors.name }}</p>
          </div>

          <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
            <label for="email"
              class="font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase">
              Email address</label>
            <div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
              <div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]" />
              <div class="flex items-center p-[14px] size-full">
                <input id="email" type="email" v-model="form.email"
                  class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
              </div>
            </div>
            <p v-if="form.errors.email" class="text-[11px] text-red-400">{{ form.errors.email }}</p>
          </div>

          <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
            <label for="password"
              class="font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase">
              Password</label>
            <div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
              <div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]" />
              <div class="flex items-center p-[14px] size-full">
                <input id="password" type="password" v-model="form.password"
                  class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
              </div>
            </div>
            <p v-if="form.errors.password" class="text-[11px] text-red-400">{{ form.errors.password }}</p>
          </div>

          <div class="content-stretch flex flex-col gap-[8px] items-start relative shrink-0 w-full">
            <label for="password_confirmation"
              class="font-['Jost',sans-serif] font-semibold leading-[normal] relative shrink-0 text-[13px] text-[rgba(255,255,255,0.35)] uppercase">
              Confirm password</label>
            <div class="bg-[#1a1628] drop-shadow-[0px_0px_5px_rgba(124,58,237,0.15)] h-[48px] relative rounded-[6px] shrink-0 w-full">
              <div aria-hidden="true" class="absolute border border-[#3d2f6e] border-solid inset-0 pointer-events-none rounded-[6px]" />
              <div class="flex items-center p-[14px] size-full">
                <input id="password_confirmation" type="password" v-model="form.password_confirmation"
                  class="w-full bg-transparent border-none outline-none text-[15px] text-white font-['Jost',sans-serif] placeholder:opacity-40 placeholder:text-white focus:ring-0 focus:outline-none" />
              </div>
            </div>
          </div>

          <button type="submit" @click="submit" :disabled="form.processing"
            class="content-stretch drop-shadow-[0px_0px_9px_rgba(201,168,76,0.25)] flex h-[56px] items-center justify-center py-[16px] relative rounded-[4px] shrink-0 w-full"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            <p class="font-['Jost',sans-serif] font-bold leading-[normal] relative shrink-0 text-[#0d0b14] text-[16px] uppercase whitespace-nowrap">
              {{ form.processing ? 'Signing up…' : 'Sign up' }}
            </p>
          </button>

          <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] mx-auto">
            Already an affiliate? <Link href="/login" class="text-[#c9a84c] underline">Log in</Link>
          </p>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>
