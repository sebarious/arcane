<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import SellerLayout from '@/Layouts/SellerLayout.vue';

interface StoreOption {
  id: number;
  name: string;
}

interface StoreProfile {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  location: string | null;
  logo: string | null;
  social_links: Record<string, string>;
}

interface Props {
  stores: StoreOption[];
  store: StoreProfile;
}

const props = defineProps<Props>();

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
  description: props.store.description ?? '',
  location: props.store.location ?? '',
  logo: null as File | null,
  social_links: SOCIAL_FIELDS.reduce((acc, f) => ({ ...acc, [f.key]: props.store.social_links[f.key] ?? '' }), {} as Record<string, string>),
});

const logoPreview = ref<string | null>(props.store.logo);

function onLogoChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null;
  form.logo = file;
  if (file) logoPreview.value = URL.createObjectURL(file);
}

function submit() {
  form.post(`/seller/profile/${props.store.id}`, {
    forceFormData: true,
    preserveScroll: true,
  });
}

function switchStore(storeId: number) {
  router.get('/seller/profile', { store: storeId });
}
</script>

<template>
  <Head title="Store profile" />

  <SellerLayout title="Store profile" subtitle="This is what customers see on your public Arcane page.">
    <div v-if="stores.length > 1" class="mb-6">
      <select :value="store.id" @change="switchStore(Number(($event.target as HTMLSelectElement).value))"
        class="bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-3 py-2 text-sm text-white font-['Jost',sans-serif]">
        <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Logo -->
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6 h-fit">
        <p class="font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-4">Profile image</p>
        <div class="size-32 rounded-full bg-black mx-auto mb-4 overflow-hidden border border-[#3d2f6e]">
          <img v-if="logoPreview" :src="logoPreview" :alt="store.name" class="w-full h-full object-cover" />
        </div>
        <label class="block w-full text-center px-4 py-2 rounded-[6px] border border-[#3d2f6e] text-sm text-white font-['Jost',sans-serif] cursor-pointer hover:border-[#c9a84c] transition-colors">
          Choose image
          <input type="file" accept="image/*" class="hidden" @change="onLogoChange" />
        </label>
        <p v-if="form.errors.logo" class="text-xs text-red-400 mt-2">{{ form.errors.logo }}</p>
        <p class="font-['Jost',sans-serif] text-[11px] text-[#71717a] mt-3 text-center">Recommended: square, at least 300×300px.</p>
      </div>

      <!-- Details -->
      <div class="lg:col-span-2 bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
        <div class="mb-5">
          <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Bio</label>
          <textarea v-model="form.description" rows="4"
            placeholder="Tell customers a bit about your store…"
            class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
          <p v-if="form.errors.description" class="text-xs text-red-400 mt-1">{{ form.errors.description }}</p>
        </div>

        <div class="mb-6">
          <label class="block font-['Jost',sans-serif] font-semibold text-xs uppercase tracking-wide text-[rgba(255,255,255,0.35)] mb-2">Location</label>
          <input v-model="form.location" type="text" placeholder="e.g. Leeds, Bristol, Online only"
            class="w-full bg-[#1a1628] border border-[#3d2f6e] rounded-[6px] px-4 py-3 text-sm text-white font-['Jost',sans-serif] focus:outline-none focus:border-[#c9a84c]" />
          <p v-if="form.errors.location" class="text-xs text-red-400 mt-1">{{ form.errors.location }}</p>
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
          {{ form.processing ? 'Saving…' : 'Save changes' }}
        </button>
      </div>
    </div>
  </SellerLayout>
</template>
