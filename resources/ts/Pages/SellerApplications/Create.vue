<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import Header from '@/Components/Layout/Header.vue';

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
</script>

<template>
  <div class="min-h-screen bg-arcane-bg text-arcane-text flex flex-col">
    <Header />

    <main class="flex-1">
      <div class="max-w-4xl mx-auto px-6 py-8">
        <div class="card-panel p-6">
          <h1 class="font-display text-3xl mb-2">
            Apply to become an Arcane seller
          </h1>
          <p class="text-arcane-muted text-sm mb-6">
            Tell us about your store and we’ll review your application to stock Arcane mystery packs.
          </p>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium mb-1">Business name</label>
                <input v-model="form.business_name" type="text"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" required />
                <div v-if=" form.errors.business_name " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.business_name }}
                </div>
              </div>

              <div>
                <label class="block text-xs font-medium mb-1">Contact name</label>
                <input v-model="form.contact_name" type="text"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" required />
                <div v-if=" form.errors.contact_name " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.contact_name }}
                </div>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium mb-1">Email</label>
                <input v-model="form.contact_email" type="email"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" required />
                <div v-if=" form.errors.contact_email " class="text-[11px] text-red-400 mt-1">
                  {{ form.errors.contact_email }}
                </div>
              </div>

              <div>
                <label class="block text-xs font-medium mb-1">Phone</label>
                <input v-model="form.phone" type="text"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium mb-1">Address line 1</label>
              <input v-model="form.address_line_1" type="text"
                class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" required />
              <div v-if=" form.errors.address_line_1 " class="text-[11px] text-red-400 mt-1">
                {{ form.errors.address_line_1 }}
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium mb-1">Address line 2</label>
              <input v-model="form.address_line_2" type="text"
                class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" />
            </div>

            <div class="grid md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-medium mb-1">City</label>
                <input v-model="form.city" type="text"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" required />
              </div>

              <div>
                <label class="block text-xs font-medium mb-1">Postcode</label>
                <input v-model="form.postcode" type="text"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" required />
              </div>

              <div>
                <label class="block text-xs font-medium mb-1">Country</label>
                <input v-model="form.country" type="text" maxlength="2"
                  class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm uppercase"
                  required />
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium mb-1">VAT number</label>
              <input v-model="form.vat_number" type="text"
                class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm" />
            </div>

            <div>
              <label class="block text-xs font-medium mb-1">Tell us about your shop</label>
              <textarea v-model="form.about" rows="5"
                class="w-full rounded border border-arcane-border bg-arcane-surface px-3 py-2 text-sm"
                placeholder="What do you currently sell? Do you run events? What audience do you serve?" />
            </div>

            <button type="submit" class="btn-primary w-full justify-center" :disabled="form.processing">
              <span v-if=" form.processing ">Submitting…</span>
              <span v-else>Submit application</span>
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>