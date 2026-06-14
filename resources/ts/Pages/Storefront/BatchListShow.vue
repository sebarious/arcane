<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Header from '@/Components/Layout/Header.vue'

type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic'

interface Store {
  id: number
  slug: string
  name: string
  city: string
  postcode: string
}

interface Batch {
  id: number
  reference: string
  type: string | null
  game: string | null
  game_label: string | null
  pack_count: number
  created_at: string | null
}

interface BandCard {
  name: string | null
  set: string | null
  number: string | null
  image: string | null
  band: Rarity | null
}

interface BandInfo {
  count: number
  cards: BandCard[]
}

interface Props {
  store: Store
  batch: Batch
  bands: Record<Rarity, BandInfo>
}

const props = defineProps<Props>()

const bandOrder: { key: Rarity; label: string }[] = [
  { key: 'mythic',    label: 'Mythic' },
  { key: 'legendary', label: 'Legendary' },
  { key: 'super',     label: 'Super' },
  { key: 'rare',      label: 'Rare' },
  { key: 'common',    label: 'Common' },
]

const pillClass = (band: Rarity | null): string => {
  if (!band) return 'bg-arcane-border text-arcane-muted'
  return {
    common:    'bg-arcane-common/20 text-arcane-common',
    rare:      'bg-arcane-rare/20 text-arcane-rare',
    super:     'bg-arcane-super/20 text-arcane-super',
    legendary: 'bg-arcane-legendary/20 text-arcane-legendary',
    mythic:    'bg-arcane-mythic/20 text-arcane-mythic',
  }[band]
}

const imageLoading = (band: Rarity): 'lazy' | 'eager' =>
  band === 'mythic' || band === 'legendary' ? 'eager' : 'lazy'
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Header />

    <main class="flex-1 bg-arcane-bg">
      <div class="max-w-6xl mx-auto px-6 py-8 space-y-8">
        <!-- Batch hero -->
        <section class="card-panel p-4 md:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="font-display text-2xl mb-1">
              Card list – {{ batch.reference }}
            </h1>
            <p class="text-arcane-muted text-sm">
              {{ store.name }} ·
              <span class="uppercase">{{ batch.type ?? '' }}</span>
              <span v-if="batch.game_label"> · {{ batch.game_label }}</span>
            </p>
            <p class="text-arcane-muted text-xs mt-2">
              Showing cards still in sealed Arcane packs for this batch.
            </p>
          </div>
          <div class="text-xs text-arcane-muted md:text-right">
            <p>Packs in this batch: <span class="text-arcane-text font-semibold">{{ batch.pack_count }}</span></p>
          </div>
        </section>

        <!-- Rarity sections -->
        <section class="space-y-6">
          <div
            v-for="band in bandOrder"
            :key="band.key"
            class="card-panel p-4 md:p-5"
          >
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <span class="rarity-pill" :class="pillClass(band.key)">
                  {{ band.label }}
                </span>
              </div>
              <span class="text-sm text-arcane-muted">
                {{ bands[band.key]?.count ?? 0 }} remaining
              </span>
            </div>

            <div
              v-if="(bands[band.key]?.count ?? 0) === 0"
              class="text-xs text-arcane-muted"
            >
              No cards of this band are currently left in sealed Arcane packs for this batch.
            </div>

            <div v-else>
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <div
                  v-for="card in bands[band.key].cards"
                  :key="(card.name || '') + (card.number || '') + (card.set || '')"
                  class="flex flex-col items-center text-center gap-1"
                >
                  <div
                    class="w-full max-w-[130px] aspect-[245/342] rounded-lg overflow-hidden border border-arcane-border/60 bg-arcane-surface/80 flex items-center justify-center"
                  >
                    <template v-if="card.image">
                      <img
                        :src="card.image"
                        alt=""
                        class="w-full h-full object-cover transition"
                        :loading="imageLoading(band.key)"
                      />
                    </template>
                    <template v-else>
                      <div class="text-[10px] text-arcane-muted px-2">
                        Image not available
                      </div>
                    </template>
                  </div>
                  <div class="w-full max-w-[130px]">
                    <div class="text-[11px] font-semibold truncate">
                      {{ card.name }}
                    </div>
                    <div class="text-[10px] text-arcane-muted truncate">
                      {{ card.set }} · {{ card.number }}
                    </div>
                  </div>
                </div>
              </div>
              <p
                class="text-[11px] text-arcane-muted mt-2"
                v-if="(bands[band.key]?.count ?? 0) > bands[band.key].cards.length"
              >
                Showing {{ bands[band.key].cards.length }} of {{ bands[band.key].count }}
                remaining {{ band.label.toLowerCase() }}.
              </p>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>
