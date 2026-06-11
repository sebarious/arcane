<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { Rarity } from '@/types/global'

interface RarityRow {
    band: Rarity
    label: string
    odds: string
}

interface Props {
    rarities?: RarityRow[]
}

withDefaults(defineProps<Props>(), {
    rarities: () => [
        { band: 'common',    label: 'Common',    odds: '~60%' },
        { band: 'rare',      label: 'Rare',      odds: '~25%' },
        { band: 'super',     label: 'Super',     odds: '~10%' },
        { band: 'legendary', label: 'Legendary', odds: '~4%'  },
        { band: 'mythic',    label: 'Mythic',    odds: '~1%'  },
    ],
})

const pillClass = (band: Rarity): string => ({
    common:    'bg-arcane-common/20    text-arcane-common',
    rare:      'bg-arcane-rare/20      text-arcane-rare',
    super:     'bg-arcane-super/20     text-arcane-super',
    legendary: 'bg-arcane-legendary/20 text-arcane-legendary',
    mythic:    'bg-arcane-mythic/20    text-arcane-mythic',
}[band])
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-arcane-border/60">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="font-display text-2xl tracking-widest text-arcane-accent">
                    ARCANE
                </div>
                <nav class="flex items-center gap-3">
                    <Link href="/stores" class="btn-ghost">Find a store</Link>
                    <Link href="/apply"  class="btn-ghost">Become a seller</Link>
                    <Link href="/login"  class="btn-primary">Sign in</Link>
                </nav>
            </div>
        </header>

        <main class="flex-1 flex items-center">
            <div class="max-w-6xl mx-auto px-6 py-24 grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-arcane-accent2 uppercase tracking-[0.3em] text-xs mb-4">
                        Mystery card packs
                    </p>
                    <h1 class="font-display text-5xl md:text-6xl leading-tight mb-6">
                        Every pack hides<br />
                        <span class="text-arcane-accent">something legendary.</span>
                    </h1>
                    <p class="text-arcane-muted text-lg mb-8 max-w-md">
                        Authenticated, near-mint Pokémon cards — sealed into mystery packs
                        and stocked at the UK's best card shops. One card per pack. Hunt the mythic.
                    </p>
                    <div class="flex gap-3">
                        <Link href="/stores" class="btn-primary">Browse stores</Link>
                        <Link href="/apply"  class="btn-ghost">Stock Arcane in your shop</Link>
                    </div>
                </div>

                <div class="card-panel p-8">
                    <h3 class="font-display text-xl mb-6">The five rarities</h3>
                    <ul class="space-y-3">
                        <li
                            v-for="row in rarities"
                            :key="row.band"
                            class="flex items-center justify-between"
                        >
                            <span class="rarity-pill" :class="pillClass(row.band)">
                                {{ row.label }}
                            </span>
                            <span class="text-arcane-muted text-sm">{{ row.odds }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </main>

        <footer class="border-t border-arcane-border/60 py-6 text-center text-arcane-muted text-sm">
            © {{ new Date().getFullYear() }} Arcane · Mystery card packs · UK
        </footer>
    </div>
</template>
