<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\MarketPriceSnapshot;
use Illuminate\Database\Seeder;

class CardLibrarySeeder extends Seeder
{
    /**
     * Each entry: [name, set, number, printed_rarity, variant, market_value_pence].
     * Spread across our value bands so batch generation has something to chew on.
     *
     *   common:    £1 – £3
     *   rare:      £3 – £10
     *   super:     £10 – £30
     *   legendary: £30 – £100
     *   mythic:    £100+
     */
    protected array $catalogue = [
        // ── Commons (~£1-3) — bulk fodder ──────────────────────────────────
        ['Pikachu',            'sv3pt5', '025/165', 'Common',    'standard', 150],
        ['Bulbasaur',          'sv3pt5', '001/165', 'Common',    'standard', 120],
        ['Charmander',         'sv3pt5', '004/165', 'Common',    'standard', 180],
        ['Squirtle',           'sv3pt5', '007/165', 'Common',    'standard', 130],
        ['Caterpie',           'sv3pt5', '010/165', 'Common',    'standard', 100],
        ['Weedle',             'sv3pt5', '013/165', 'Common',    'standard', 100],
        ['Pidgey',             'sv3pt5', '016/165', 'Common',    'standard', 110],
        ['Rattata',            'sv3pt5', '019/165', 'Common',    'standard', 100],
        ['Spearow',            'sv3pt5', '021/165', 'Common',    'standard', 100],
        ['Ekans',              'sv3pt5', '023/165', 'Common',    'standard', 110],
        ['Sandshrew',          'sv3pt5', '027/165', 'Common',    'standard', 120],
        ['Nidoran♀',           'sv3pt5', '029/165', 'Common',    'standard', 110],
        ['Vulpix',             'sv3pt5', '037/165', 'Common',    'standard', 200],
        ['Jigglypuff',         'sv3pt5', '039/165', 'Common',    'standard', 180],
        ['Zubat',              'sv3pt5', '041/165', 'Common',    'standard', 100],
        ['Oddish',             'sv3pt5', '043/165', 'Common',    'standard', 110],
        ['Paras',              'sv3pt5', '046/165', 'Common',    'standard', 100],
        ['Venonat',            'sv3pt5', '048/165', 'Common',    'standard', 100],
        ['Diglett',            'sv3pt5', '050/165', 'Common',    'standard', 110],
        ['Meowth',             'sv3pt5', '052/165', 'Common',    'standard', 220],
        ['Psyduck',            'sv3pt5', '054/165', 'Common',    'standard', 140],
        ['Growlithe',          'sv3pt5', '058/165', 'Common',    'standard', 190],
        ['Poliwag',            'sv3pt5', '060/165', 'Common',    'standard', 110],
        ['Abra',               'sv3pt5', '063/165', 'Common',    'standard', 120],
        ['Machop',             'sv3pt5', '066/165', 'Common',    'standard', 110],
        ['Bellsprout',         'sv3pt5', '069/165', 'Common',    'standard', 100],
        ['Geodude',            'sv3pt5', '074/165', 'Common',    'standard', 110],
        ['Ponyta',             'sv3pt5', '077/165', 'Common',    'standard', 130],
        ['Slowpoke',           'sv3pt5', '079/165', 'Common',    'standard', 140],
        ['Magnemite',          'sv3pt5', '081/165', 'Common',    'standard', 110],

        // ── Rares (~£3-10) — solid pulls ──────────────────────────────────
        ['Venusaur',           'sv3pt5', '003/165', 'Rare Holo', 'standard',  650],
        ['Blastoise',          'sv3pt5', '009/165', 'Rare Holo', 'standard',  750],
        ['Butterfree',         'sv3pt5', '012/165', 'Rare Holo', 'standard',  450],
        ['Beedrill',           'sv3pt5', '015/165', 'Rare Holo', 'standard',  400],
        ['Pidgeot',            'sv3pt5', '018/165', 'Rare Holo', 'standard',  550],
        ['Raticate',           'sv3pt5', '020/165', 'Rare Holo', 'standard',  350],
        ['Arbok',              'sv3pt5', '024/165', 'Rare Holo', 'standard',  380],
        ['Raichu',             'sv3pt5', '026/165', 'Rare Holo', 'standard',  690],
        ['Ninetales',          'sv3pt5', '038/165', 'Rare Holo', 'standard',  720],
        ['Wigglytuff',         'sv3pt5', '040/165', 'Rare Holo', 'standard',  420],
        ['Golbat',             'sv3pt5', '042/165', 'Rare Holo', 'standard',  330],
        ['Vileplume',          'sv3pt5', '045/165', 'Rare Holo', 'standard',  410],
        ['Parasect',           'sv3pt5', '047/165', 'Rare Holo', 'standard',  320],
        ['Persian',            'sv3pt5', '053/165', 'Rare Holo', 'standard',  390],
        ['Arcanine',           'sv3pt5', '059/165', 'Rare Holo', 'standard',  880],
        ['Alakazam',           'sv3pt5', '065/165', 'Rare Holo', 'standard',  920],
        ['Machamp',            'sv3pt5', '068/165', 'Rare Holo', 'standard',  780],

        // ── Supers (~£10-30) — exciting ────────────────────────────────────
        ['Venusaur ex',        'sv3pt5', '198/165', 'Double Rare', 'standard',  1200],
        ['Blastoise ex',       'sv3pt5', '200/165', 'Double Rare', 'standard',  1400],
        ['Alakazam ex',        'sv3pt5', '201/165', 'Double Rare', 'standard',  1800],
        ['Gengar ex',          'sv3pt5', '202/165', 'Double Rare', 'standard',  2200],
        ['Dragonite ex',       'sv3pt5', '203/165', 'Double Rare', 'standard',  2400],
        ['Mewtwo ex',          'sv3pt5', '052/165', 'Double Rare', 'standard',  2800],
        ['Snorlax ex',         'sv3pt5', '187/165', 'Double Rare', 'standard',  1100],
        ['Zapdos ex',          'sv3pt5', '145/165', 'Double Rare', 'standard',  1900],
        ['Articuno ex',        'sv3pt5', '144/165', 'Double Rare', 'standard',  1700],
        ['Moltres ex',         'sv3pt5', '146/165', 'Double Rare', 'standard',  1600],

        // ── Legendaries (~£30-100) — chase tier ───────────────────────────
        ['Pikachu ex',         'sv3pt5', '247/165', 'Illustration Rare',         'illustration',         4500],
        ['Mew',                'sv3pt5', '193/165', 'Illustration Rare',         'illustration',         5200],
        ['Charizard',          'sv3pt5', '199/165', 'Illustration Rare',         'illustration',         7800],
        ['Squirtle',           'sv3pt5', '170/165', 'Illustration Rare',         'illustration',         3800],
        ['Bulbasaur',          'sv3pt5', '167/165', 'Illustration Rare',         'illustration',         3500],
        ['Charmander',         'sv3pt5', '173/165', 'Illustration Rare',         'illustration',         6200],
        ['Eevee',              'sv3pt5', '188/165', 'Illustration Rare',         'illustration',         4200],
        ['Lapras',             'sv3pt5', '186/165', 'Illustration Rare',         'illustration',         3900],

        // ── Mythics (£100+) — chase / grail ────────────────────────────────
        ['Charizard ex',       'sv3pt5', '215/165', 'Special Illustration Rare', 'special-illustration', 18000],
        ['Mew ex',             'sv3pt5', '232/165', 'Special Illustration Rare', 'special-illustration', 22000],
        ['Pikachu ex',         'sv3pt5', '238/165', 'Special Illustration Rare', 'special-illustration', 14000],
        ['Charizard ex',       'sv3pt5', '223/165', 'Hyper Rare',                'hyper',                26000],
        ['Mew ex',             'sv3pt5', '244/165', 'Gold',                      'gold',                 35000],
    ];

    public function run(): void
    {
        $imagePath = asset('seed-cards/placeholder.svg');

        foreach ($this->catalogue as $i => [$name, $set, $number, $rarity, $variant, $marketPence]) {
            $card = Card::updateOrCreate(
                [
                    'set_code'    => $set,
                    'card_number' => $number,
                    'variant'     => $variant,
                    'language'    => 'en',
                ],
                [
                    'scrydex_id'     => "seed-{$set}-{$number}-{$variant}",
                    'name'           => $name,
                    'set_name'       => 'Scarlet & Violet 151 (Seed)',
                    'language_code'  => 'EN',
                    'printed_rarity' => $rarity,
                    'image_front'    => $imagePath,
                    'external_ids'   => ['seed' => true],
                ],
            );

            // Snapshot a synthetic GBP market price so banding works.
            MarketPriceSnapshot::updateOrCreate(
                [
                    'card_id' => $card->id,
                    'source'  => 'seed',
                ],
                [
                    'condition'    => 'NM',
                    'currency'     => 'GBP',
                    'median_pence' => $marketPence,
                    'low_pence'    => (int) round($marketPence * 0.85),
                    'high_pence'   => (int) round($marketPence * 1.15),
                    'sample_size'  => 25,
                    'raw_payload'  => ['source' => 'seed', 'note' => 'Synthetic dev data'],
                    'fetched_at'   => now(),
                ],
            );
        }

        $this->command->info('Seeded '.count($this->catalogue).' cards with synthetic GBP market prices.');
    }
}
