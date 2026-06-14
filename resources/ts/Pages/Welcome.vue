<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Header from '@/Components/Layout/Header.vue';

const products = [
    {
        key: 'sapphire',
        name: 'Sapphire',
        packs: 100,
        pricePerPack: '£8.00'
    },
    {
        key: 'ruby',
        name: 'Ruby',
        packs: 250,
        pricePerPack: '£7.00'
    },
    {
        key: 'diamond',
        name: 'Diamond',
        packs: 500,
        pricePerPack: '£6.00'
    },
];
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <Header />

        <!-- Hero -->
        <main class="flex-1">
            <div class="futuristic-grid">
                <section
                    class="border-b border-arcane-border/60 bg-gradient-to-b from-arcane-bg/90 to-arcane-surface/80">
                    <div class="max-w-6xl mx-auto px-6 py-16 md:py-20 grid md:grid-cols-2 gap-10 items-center">
                        <div>
                            <div class="outline-root mb-3">
                                <p
                                    class="text-arcane-accent2 uppercase tracking-[0.3em] text-xs outline-inner secondary">
                                    Mystery singles packs for card shops
                                </p>
                            </div>
                            <h1 class="font-display text-4xl md:text-5xl leading-tight mb-4">
                                <span>One card.</span><br>
                                <span>Infinite chase.</span>
                            </h1>
                            <p class="text-arcane-muted text-base md:text-lg mb-6 max-w-md">
                                Arcane turns authenticated, near-mint Pokémon singles into sealed
                                mystery packs—each with a single toploaded hit. Live card lists
                                show what’s still in the pool at your local shop.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <span class="outline-root">
                                    <Link href="/stores" class="btn-primary outline-inner">
                                        <span>Browse participating stores</span>
                                    </Link>
                                </span>
                                <Link href="/apply" class="btn-ghost">
                                Bring Arcane to your shop
                                </Link>
                            </div>
                        </div>

                        <div class="outline-root">
                            <div class="card-panel outline-inner">
                                <span></span>
                                <div class=" p-5 md:p-6">

                                    <div class="outline-root absolute left-1/2 transform -translate-x-1/2 top-20">
                                        <h2
                                            class="text-arcane-accent2 uppercase tracking-[0.3em] text-sm outline-inner secondary text-nowrap">
                                            The Arcane Mystery Singles Pack!
                                        </h2>
                                    </div>

                                    <img src="/images/pack.png" alt="Arcane pack" class="w-full h-auto" />

                                    <div class="relative">
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 -mt-12">
                                            <div v-for="   product in products   " :key="product.key"
                                                class="flex items-center justify-between gap-3 border border-arcane-border/60 rounded-lg px-3 py-2.5 bg-arcane-surface/80">
                                                <div>
                                                    <div
                                                        :class="['text-sm font-semibold', 'text-' + product.name.toLowerCase()]">
                                                        {{ product.name }} (x{{ product.packs }})
                                                    </div>
                                                </div>
                                                <div class="text-right text-xs">
                                                    <div class="font-semibold">
                                                        {{ product.pricePerPack }} <span class="text-arcane-muted">/
                                                            pack</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-arcane-muted text-[11px] mt-6">
                                            Packs are built using live market data and a defined rarity structure
                                            (Common, Rare, Super, Legendary, Mythic) so batches stay consistent
                                            and fair.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

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
                            mix of commons through mythics with awesome chase cards.
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