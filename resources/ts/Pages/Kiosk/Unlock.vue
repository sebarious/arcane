<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ pin: '' });
const pinInput = ref<HTMLInputElement | null>(null);

function submit() {
  form.post('/kiosk/unlock', {
    onFinish: () => {
      form.reset('pin');
      pinInput.value?.focus();
    },
  });
}

/** On-screen keypad — the tablet is usually in a stand with no keyboard attached. */
function press(digit: string) {
  if (form.pin.length < 6) form.pin += digit;
}

function backspace() {
  form.pin = form.pin.slice(0, -1);
}
</script>

<template>
  <Head title="Unlock kiosk" />

  <div class="fixed inset-0 bg-[#0d0b14] flex items-center justify-center font-['Jost',sans-serif] select-none px-6">
    <div class="w-full max-w-sm">
      <p class="font-['Cinzel',sans-serif] font-bold text-white text-[26px] text-center">Kiosk locked</p>
      <p class="text-[#a3a3a3] text-[15px] text-center mt-2">
        Enter today's staff PIN. You'll find it in the admin panel, top right.
      </p>

      <form @submit.prevent="submit" class="mt-8">
        <input ref="pinInput" v-model="form.pin" inputmode="numeric" autocomplete="off" maxlength="6"
          placeholder="••••••"
          class="w-full h-[68px] bg-[#1a1628] border rounded-[10px] text-center text-white text-[30px] tracking-[0.4em] outline-none focus:ring-0"
          :class="form.errors.pin ? 'border-[#ef4444]' : 'border-[#3d2f6e]'" />

        <p v-if="form.errors.pin" class="text-[#ef4444] text-[14px] text-center mt-3">{{ form.errors.pin }}</p>

        <div class="grid grid-cols-3 gap-3 mt-6">
          <button v-for="digit in ['1','2','3','4','5','6','7','8','9']" :key="digit" type="button"
            @click="press(digit)"
            class="h-[60px] rounded-[10px] border border-[#3d2f6e] text-white text-[22px] font-semibold hover:border-[#c9a84c] transition-colors">
            {{ digit }}
          </button>
          <button type="button" @click="backspace"
            class="h-[60px] rounded-[10px] border border-[#3d2f6e] text-[#a3a3a3] text-[18px] hover:border-[#c9a84c] transition-colors">
            ←
          </button>
          <button type="button" @click="press('0')"
            class="h-[60px] rounded-[10px] border border-[#3d2f6e] text-white text-[22px] font-semibold hover:border-[#c9a84c] transition-colors">
            0
          </button>
          <button type="submit" :disabled="form.pin.length === 0 || form.processing"
            class="h-[60px] rounded-[10px] text-[#0d0b14] font-bold uppercase text-[14px] disabled:opacity-40"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            {{ form.processing ? '…' : 'Unlock' }}
          </button>
        </div>
      </form>

      <p class="text-[#71717a] text-[12px] text-center mt-8">
        The PIN changes every day. The kiosk re-locks itself overnight.
      </p>
    </div>
  </div>
</template>
