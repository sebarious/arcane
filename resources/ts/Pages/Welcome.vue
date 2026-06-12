<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const products = [
    {
        key: 'sapphire',
        name: 'Sapphire',
        packs: 100,
        pricePerPack: '£8.00',
        blurb: 'Perfect entry point. 100 mystery singles with real chase potential.',
    },
    {
        key: 'ruby',
        name: 'Ruby',
        packs: 250,
        pricePerPack: '£7.00',
        blurb: 'Our flagship bundle. Deep pool of rares, supers and legendaries.',
    },
    {
        key: 'diamond',
        name: 'Diamond',
        packs: 500,
        pricePerPack: '£6.00',
        blurb: 'High-volume mystery singles for serious stores and events.',
    },
];
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <!-- Top nav -->
        <header class="border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2">
                <span class="font-display text-xl tracking-[0.3em] text-arcane-accent">
                    ARCANE
                </span>
                </Link>
                <nav class="flex items-center gap-4 text-sm text-arcane-muted">
                    <Link href="/stores" class="hover:text-arcane-accent">Stores</Link>
                    <Link href="/apply" class="hover:text-arcane-accent">Become a seller</Link>
                    <Link href="/login" class="btn-ghost text-xs">Sign in</Link>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main class="flex-1">
            <section class="border-b border-arcane-border/60 bg-gradient-to-b from-arcane-bg to-arcane-surface/80">
                <div class="max-w-6xl mx-auto px-6 py-16 md:py-20 grid md:grid-cols-2 gap-10 items-center">
                    <div>
                        <p class="text-arcane-accent2 uppercase tracking-[0.3em] text-xs mb-3">
                            Mystery singles packs for card shops
                        </p>
                        <h1 class="font-display text-4xl md:text-5xl leading-tight mb-4">
                            <span class="text-arcane-accent">One card.</span><br>
                            <span>Infinite chase.</span>
                        </h1>
                        <p class="text-arcane-muted text-base md:text-lg mb-6 max-w-md">
                            Arcane turns authenticated, near-mint Pokémon singles into sealed
                            mystery packs—each with a single toploaded hit. Live card lists
                            show what’s still in the pool at your local shop.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <Link href="/stores" class="btn-primary">
                            Browse participating stores
                            </Link>
                            <Link href="/apply" class="btn-ghost">
                            Bring Arcane to your shop
                            </Link>
                        </div>
                    </div>

                    <div class="card-panel p-5 md:p-6">
                        <h2 class="font-display text-lg mb-4">
                            Product lineup
                        </h2>
                        <div class="space-y-3">
                            <div v-for=" product in products " :key="product.key"
                                class="flex items-center justify-between gap-3 border border-arcane-border/60 rounded-lg px-3 py-2.5 bg-arcane-surface/80">
                                <div>
                                    <div class="text-sm font-semibold">
                                        {{ product.name }} batch
                                    </div>
                                    <div class="text-xs text-arcane-muted">
                                        {{ product.packs }} single-card packs · {{ product.blurb }}
                                    </div>
                                </div>
                                <div class="text-right text-xs">
                                    <div class="text-arcane-muted">
                                        From
                                    </div>
                                    <div class="font-semibold">
                                        {{ product.pricePerPack }} <span class="text-arcane-muted">/ pack</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-arcane-muted text-[11px] mt-3">
                            Packs are built using live market data and a defined rarity structure
                            (Common, Rare, Super, Legendary, Mythic) so batches stay consistent
                            and profitable.
                        </p>
                    </div>
                </div>
            </section>

            <!-- How it works -->
            <section class="max-w-6xl mx-auto px-6 py-12 md:py-16 space-y-8">
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="card-panel p-4">
                        <h3 class="text-sm font-semibold mb-2">1. We source & authenticate</h3>
                        <p class="text-xs text-arcane-muted">
                            We buy Pokémon singles from trusted sellers and shows, scan them
                            into our library, and assign live market values.
                        </p>
                    </div>
                    <div class="card-panel p-4">
                        <h3 class="text-sm font-semibold mb-2">2. Batches are generated</h3>
                        <p class="text-xs text-arcane-muted">
                            Our engine builds Sapphire, Ruby, or Diamond batches with a defined
                            mix of commons through mythics and a target profit margin.
                        </p>
                    </div>
                    <div class="card-panel p-4">
                        <h3 class="text-sm font-semibold mb-2">3. Live card lists</h3>
                        <p class="text-xs text-arcane-muted">
                            Each store has a live card list showing which hits are still in
                            unopened Arcane packs. QR codes delist cards the moment a pack is sold.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>