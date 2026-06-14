<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Header from '@/Components/Layout/Header.vue';

const form = useForm( {
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  customer_postcode: '',
  description: '',
  images: [] as File[],
} );

const onFilesChange = ( e: Event ) => {
  const target = e.target as HTMLInputElement;
  if ( !target.files ) return;
  form.images = Array.from( target.files );
};

const submit = () => {
  form.post( '/sell', {
    forceFormData: true,
  } );
};
</script>

<template>
  <div class="min-h-screen bg-arcane-bg text-arcane-text flex flex-col">
    <Header />

    <main class="flex-1">
      <div class="max-w-4xl mx-auto px-6 py-8">
        <div class="card-panel p-6">
          <h1 class="font-display text-2xl mb-2">
            Sell your cards to Arcane
          </h1>
          <p class="text-arcane-muted text-sm mb-6">
            Upload photos of the cards or sealed product you want to sell and tell us
            what’s in the lot. We’ll review and email you an offer.
          </p>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium mb-1">Name</label>
                <input v-model="form.customer_name" type="text"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none"
                  required />
                <div v-if=" form.errors.customer_name " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.customer_name }}
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium mb-1">Email</label>
                <input v-model="form.customer_email" type="email"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none"
                  required />
                <div v-if=" form.errors.customer_email " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.customer_email }}
                </div>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium mb-1">Phone (optional)</label>
                <input v-model="form.customer_phone" type="text"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none" />
              </div>
              <div>
                <label class="block text-xs font-medium mb-1">Postcode (optional)</label>
                <input v-model="form.customer_postcode" type="text"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium mb-1">What are you selling?</label>
              <textarea v-model="form.description" rows="4"
                class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm focus:ring-2 focus:ring-arcane-accent focus:outline-none"
                placeholder="e.g. 3 binders of Pokémon EX-era cards, 2 sealed ETBs, mixed bulk…" required />
              <div v-if=" form.errors.description " class="text-[11px] text-red-400 mt-1">
                {{ form.errors.description }}
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium mb-1">Photos (up to 8)</label>
              <input type="file" accept="image/*" multiple @change="onFilesChange"
                class="block w-full text-sm text-arcane-muted" required />
              <div v-if=" form.errors.images " class="text-[11px] text-red-400 mt-1">
                {{ form.errors.images }}
              </div>
              <div v-if=" form.errors['images.0'] " class="text-[11px] text-red-400 mt-1">
                {{ form.errors['images.0'] }}
              </div>
            </div>

            <div class="outline-root w-full mt-4">
              <button type="submit" class="btn-primary outline-inner w-full justify-center"
                :disabled="form.processing">
                <span v-if=" form.processing ">Sending…</span>
                <span v-else>Submit</span>
                </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>