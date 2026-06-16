<?php

namespace App\Services\Pricing;

use Illuminate\Support\Facades\Http;

class TcgdexClient
{
  protected string $baseUrl = 'https://api.tcgdex.dev/v2';

  /**
   * Fetch a Pokémon card from TCGdex by its TCGdex ID.
   *
   * Example ID: "sv3pt5-215" (set-code + number).
   *
   * @return array<string, mixed>|null
   */
  public function card(string $tcgdexId): ?array
  {
    // For EN Pokémon, it's /en/cards/{id}
    $url = "{$this->baseUrl}/en/cards/{$tcgdexId}";

    $res = Http::timeout(10)->get($url);
    if (! $res->ok()) {
      return null;
    }

    return $res->json();
  }
}
