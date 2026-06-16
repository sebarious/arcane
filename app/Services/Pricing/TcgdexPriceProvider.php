<?php

namespace App\Services\Pricing;

use App\Models\Card;
use App\Models\MarketPriceSnapshot;
use Illuminate\Support\Arr;

class TcgdexPriceProvider implements PriceProvider
{
  public function __construct(
    protected TcgdexClient $client,
  ) {}

  public function refreshPrice(Card $card, string $condition = 'NM'): void
  {
    // Only handle Pokémon for now
    if ($card->game?->value !== 'pokemon') {
      return;
    }

    $tcgdexId = $card->external_ids['tcgdex_id'] ?? null;
    if (! $tcgdexId) {
      return;
    }

    $data = $this->client->card($tcgdexId);
    if (! $data) return;

    $pricing = Arr::get($data, 'pricing', []);
    if (! $pricing) return;

    $values = [];

    // Cardmarket (EUR)
    if (isset($pricing['cardmarket']['trendPrice'])) {
      $eur = (float) $pricing['cardmarket']['trendPrice'];
      if ($eur > 0) {
        $values[] = eur_to_gbp($eur);
      }
    }

    // Optionally include TCGplayer (USD)
    if (isset($pricing['tcgplayer']['market'])) {
      $usd = (float) $pricing['tcgplayer']['market'];
      if ($usd > 0) {
        $values[] = usd_to_gbp($usd);
      }
    }

    if (empty($values)) return;

    sort($values);
    $count  = count($values);
    $median = $values[(int) floor($count / 2)];
    $low    = $values[0];
    $high   = $values[$count - 1];

    $snapshot = MarketPriceSnapshot::create([
      'card_id'      => $card->id,
      'condition'    => $condition,
      'source'       => 'tcgdex',
      'currency'     => 'GBP',
      'median_pence' => (int) round($median * 100),
      'low_pence'    => (int) round($low * 100),
      'high_pence'   => (int) round($high * 100),
      'sample_size'  => $count,
      'raw_payload'  => ['pricing' => $pricing],
      'fetched_at'   => now(),
    ]);

    // Update card & in-stock inventory to use this as current market value
    $card->market_price_pence = $snapshot->median_pence;
    $card->save();

    $card->inventory()
      ->where('status', 'in_stock')
      ->update([
        'market_value_pence'       => $snapshot->median_pence,
        'market_value_updated_at'  => now(),
      ]);
  }
}
