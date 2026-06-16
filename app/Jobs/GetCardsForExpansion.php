<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Scrydex\ScrydexClient;
use App\Services\Scrydex\CardMapper;

class GetCardsForExpansion implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(public string $expansionId, public string $game, public string|null $language = 'en')
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new ScrydexClient(
            baseUrl: config('services.scrydex.url'),
            apiKey: config('services.scrydex.key'),
            teamId: config('services.scrydex.team_id'),
        );

        $cards = $client->getCardsInExpansion($this->expansionId, $this->language, $this->game);

        $ignoredRarities = [
            'pokemon' => ['Uncommon', 'Common', 'Rare']
        ];

        $cards = array_values(array_filter($cards, function ($card) use ($ignoredRarities) {
            $rarity = strtoupper(trim($card['rarity'] ?? ''));

            return !in_array($rarity, array_map('strtoupper', $ignoredRarities[$this->game] ?? []));
        }));

        foreach ($cards as $payload) {
            CardMapper::upsert($payload);
        }
    }
}
