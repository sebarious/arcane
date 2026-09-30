<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

defineProps<{
  pin: string;
  alreadyUnlocked: boolean;
}>();

const applying = ref(false);
const revealed = ref(false);

const userName = (usePage().props.auth as { user?: { name?: string } } | undefined)?.user?.name ?? '';

function apply() {
  applying.value = true;
  router.post('/kiosk/access/apply', {}, { onFinish: () => (applying.value = false) });
}

function signOut() {
  router.post('/admin/logout');
}
</script>

<template>
  <Head title="Kiosk access" />

  <div class="fixed inset-0 bg-[#0d0b14] flex items-center justify-center font-['Jost',sans-serif] px-5">
    <div class="w-full max-w-md">
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[26px] text-center">Kiosk access</p>
      <p v-if="userName" class="text-[#a3a3a3] text-[14px] text-center mt-2">Signed in as {{ userName }}</p>

      <!-- The button is the normal route; the PIN is here for reading out to
           someone setting up a second tablet. -->
      <button type="button" :disabled="applying" @click="apply"
        class="w-full h-[64px] mt-8 rounded-[8px] text-[#0d0b14] font-bold uppercase tracking-[0.08em] text-[16px] disabled:opacity-50"
        style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
        {{ applying ? 'Opening…' : alreadyUnlocked ? 'Open the kiosk' : 'Click to apply' }}
      </button>

      <p class="text-[#a3a3a3] text-[13px] text-center mt-3">
        {{ alreadyUnlocked
          ? 'This device is already unlocked for today.'
          : 'Unlocks this device for today and opens the till — no need to type the code.' }}
      </p>

      <div class="mt-10 p-5 rounded-[10px] border border-[#3d2f6e] bg-[#13101e]">
        <p class="text-[#a3a3a3] text-[12px] uppercase tracking-[0.16em] text-center">Today's PIN</p>

        <button type="button" @click="revealed = !revealed"
          class="w-full mt-3 font-['Cinzel',sans-serif] font-bold text-[38px] tracking-[0.3em] text-center text-[#c9a84c]">
          {{ revealed ? pin : '••••••' }}
        </button>

        <p class="text-[#71717a] text-[12px] text-center mt-3">
          {{ revealed ? 'Tap to hide.' : 'Tap to reveal — for unlocking another device by hand.' }}
        </p>
      </div>

      <p class="text-[#71717a] text-[12px] text-center mt-8">
        The PIN changes daily and every kiosk re-locks itself overnight.
      </p>

      <button type="button" @click="signOut"
        class="w-full h-[46px] mt-6 rounded-[6px] border border-[#3d2f6e] text-[#a3a3a3] text-[13px] uppercase tracking-[0.1em] hover:border-[#c9a84c] hover:text-white transition-colors">
        Sign out
      </button>
    </div>
  </div>
</template>
