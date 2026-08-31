<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { X } from 'lucide-vue-next';

const emit = defineEmits<{ close: []; authenticated: [] }>();

const mode = ref<'login' | 'register'>('login');
const submitting = ref(false);
const errors = ref<Record<string, string[]>>({});

const loginForm = ref({ email: '', password: '' });
const registerForm = ref({ name: '', email: '', password: '', password_confirmation: '' });

async function submitLogin() {
  submitting.value = true;
  errors.value = {};

  try {
    await axios.post('/login', loginForm.value, { headers: { Accept: 'application/json' } });
    emit('authenticated');
  } catch (e: any) {
    errors.value = e?.response?.data?.errors ?? { email: ['Incorrect email or password.'] };
  } finally {
    submitting.value = false;
  }
}

async function submitRegister() {
  submitting.value = true;
  errors.value = {};

  try {
    await axios.post('/register', registerForm.value, { headers: { Accept: 'application/json' } });
    emit('authenticated');
  } catch (e: any) {
    errors.value = e?.response?.data?.errors ?? {};
  } finally {
    submitting.value = false;
  }
}

function continueWithGoogle() {
  window.location.href = '/auth/google/redirect';
}
</script>

<template>
  <div class="fixed inset-0 z-[100] flex items-center justify-center px-4" @click.self="emit('close')">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" />

    <div class="relative w-full max-w-md bg-[#13101e] border border-[rgba(220,193,117,0.2)] rounded-[14px] p-8 shadow-2xl">
      <button type="button" @click="emit('close')"
        class="absolute top-4 right-4 text-[#71717a] hover:text-white transition-colors">
        <X :size="20" />
      </button>

      <h2 class="font-['Cinzel',sans-serif] font-bold text-2xl text-white mb-1">
        {{ mode === 'login' ? 'Welcome back' : 'Create your account' }}
      </h2>
      <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] mb-6">
        {{ mode === 'login' ? 'Log in to buy a pack and manage your wallet.' : 'Just a few details — you can start ripping packs right away.' }}
      </p>

      <button type="button" @click="continueWithGoogle"
        class="w-full flex items-center justify-center gap-3 py-3 mb-5 bg-white text-[#1f1f1f] text-sm font-['Jost',sans-serif] font-semibold rounded-[6px] hover:bg-white/90 transition-colors">
        <svg viewBox="0 0 24 24" class="size-4" aria-hidden="true">
          <path fill="#4285F4" d="M23.52 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.47a5.54 5.54 0 0 1-2.4 3.63v3h3.88c2.27-2.09 3.57-5.17 3.57-8.82Z" />
          <path fill="#34A853" d="M12 24c3.24 0 5.96-1.07 7.95-2.91l-3.88-3c-1.07.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.26v3.11A11.99 11.99 0 0 0 12 24Z" />
          <path fill="#FBBC05" d="M5.27 14.28A7.2 7.2 0 0 1 4.89 12c0-.79.14-1.56.38-2.28V6.61H1.26A11.99 11.99 0 0 0 0 12c0 1.94.46 3.77 1.26 5.39l4.01-3.11Z" />
          <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.69 1.26 6.61l4.01 3.11C6.22 6.86 8.87 4.75 12 4.75Z" />
        </svg>
        Continue with Google
      </button>

      <div class="flex items-center gap-3 mb-5">
        <div class="flex-1 h-px bg-[rgba(220,193,117,0.15)]" />
        <span class="font-['Jost',sans-serif] text-[10px] uppercase tracking-wide text-[#71717a]">or</span>
        <div class="flex-1 h-px bg-[rgba(220,193,117,0.15)]" />
      </div>

      <form v-if="mode === 'login'" @submit.prevent="submitLogin" class="flex flex-col gap-4">
        <div>
          <input v-model="loginForm.email" type="email" required placeholder="Email address"
            class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-3 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
          <p v-if="errors.email" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ errors.email[0] }}</p>
        </div>
        <div>
          <input v-model="loginForm.password" type="password" required placeholder="Password"
            class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-3 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
        </div>
        <button type="submit" :disabled="submitting"
          class="w-full py-3 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[6px] disabled:opacity-50 transition-opacity">
          {{ submitting ? 'Logging in…' : 'Log in' }}
        </button>
      </form>

      <form v-else @submit.prevent="submitRegister" class="flex flex-col gap-4">
        <div>
          <input v-model="registerForm.name" type="text" required placeholder="Full name"
            class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-3 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
          <p v-if="errors.name" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ errors.name[0] }}</p>
        </div>
        <div>
          <input v-model="registerForm.email" type="email" required placeholder="Email address"
            class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-3 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
          <p v-if="errors.email" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ errors.email[0] }}</p>
        </div>
        <div>
          <input v-model="registerForm.password" type="password" required placeholder="Password"
            class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-3 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
          <p v-if="errors.password" class="text-red-400 text-xs mt-1 font-['Jost',sans-serif]">{{ errors.password[0] }}</p>
        </div>
        <div>
          <input v-model="registerForm.password_confirmation" type="password" required placeholder="Confirm password"
            class="w-full bg-[#0d0b14] border border-[rgba(220,193,117,0.15)] rounded-[6px] px-4 py-3 text-white text-sm font-['Jost',sans-serif] focus:outline-none focus:border-[#DCC175]/50" />
        </div>
        <button type="submit" :disabled="submitting"
          class="w-full py-3 bg-gradient-to-r from-[#DCC175] to-[#e8d49a] text-black text-xs tracking-[0.2em] uppercase font-bold rounded-[6px] disabled:opacity-50 transition-opacity">
          {{ submitting ? 'Creating account…' : 'Create account' }}
        </button>
      </form>

      <p class="text-center font-['Jost',sans-serif] text-xs text-[#71717a] mt-5">
        <template v-if="mode === 'login'">
          New here?
          <button type="button" @click="mode = 'register'" class="text-[#DCC175] hover:underline">Create an account</button>
        </template>
        <template v-else>
          Already have an account?
          <button type="button" @click="mode = 'login'" class="text-[#DCC175] hover:underline">Log in</button>
        </template>
      </p>
    </div>
  </div>
</template>
