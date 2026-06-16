<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Services\Pricing\PriceProvider;
use Illuminate\Console\Command;

class TestRefreshCardPrice extends Command
{
    protected $signature = 'arcane:test-refresh-price
                            {card : Card ID, tcgdex ID, or part of the name}';

    protected $description = 'Test Tcgdex-based refreshPrice() for a single card';

    public function handle(PriceProvider $provider): int
    {
        $identifier = $this->argument('card');

        $card = $this->findCard($identifier);

        if (! $card) {
            $this->error("Card '{$identifier}' not found.");
            return self::FAILURE;
        }

        $this->info("Testing price refresh for card #{$card->id}: {$card->name} ({$card->set_name} · {$card->card_number})");

        $latestBefore = $card->priceSnapshots()
            ->where('source', 'tcgdex')
            ->latest('fetched_at')
            ->first();

        if ($latestBefore) {
            $this->line(sprintf(
                "Before: median £%.2f (low £%.2f, high £%.2f) sample=%d",
                $latestBefore->median_pence / 100,
                $latestBefore->low_pence / 100,
                $latestBefore->high_pence / 100,
                $latestBefore->sample_size,
            ));
        } else {
            $this->line('Before: no tcgdex snapshot.');
        }

        $provider->refreshPrice($card, 'NM');

        $latestAfter = $card->priceSnapshots()
            ->where('source', 'tcgdex')
            ->latest('fetched_at')
            ->first();

        if (! $latestAfter || ($latestBefore && $latestAfter->id === $latestBefore->id)) {
            $this->warn('No new price snapshot, or same as before.');
        } else {
            $this->info('After:');
            $this->line(sprintf(
                "median £%.2f (low £%.2f, high £%.2f) sample=%d",
                $latestAfter->median_pence / 100,
                $latestAfter->low_pence / 100,
                $latestAfter->high_pence / 100,
                $latestAfter->sample_size,
            ));
        }

        $card->refresh();
        $this->line('');
        $this->info('Card & inventory state:');
        $this->line(sprintf(
            "Card.market_price_pence: £%.2f",
            ($card->market_price_pence ?? 0) / 100
        ));

        $samples = $card->inventory()
            ->where('status', 'in_stock')
            ->limit(3)
            ->get(['id', 'market_value_pence', 'cost_pence']);

        foreach ($samples as $inv) {
            $this->line(sprintf(
                "Inv #%d: market £%.2f, cost £%.2f",
                $inv->id,
                ($inv->market_value_pence ?? 0) / 100,
                $inv->cost_pence / 100,
            ));
        }

        return self::SUCCESS;
    }

    protected function findCard(string $identifier): ?Card
    {
        if (ctype_digit($identifier)) {
            return Card::find((int) $identifier);
        }

        // try tcgdex_id in external_ids
        $card = Card::where('external_ids->tcgdex_id', $identifier)->first();
        if ($card) return $card;

        // fallback: partial name
        return Card::where('name', 'ilike', '%' . $identifier . '%')->first();
    }
}
