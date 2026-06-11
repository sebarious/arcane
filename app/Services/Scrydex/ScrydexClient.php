<?php

namespace App\Services\Scrydex;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ScrydexClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
        protected string $teamId,
    ) {}

    protected function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500)
            ->withHeaders([
                'X-Api-Key' => $this->apiKey,
                'X-Team-ID' => $this->teamId,
            ]);
    }

    /**
     * Search English Pokémon cards.
     * q is Scrydex's Lucene-style syntax, e.g. 'name:"Charizard ex" expansion.id:sv3pt5'
     *
     * @return array{data: array<int, array<string, mixed>>, total?: int, page?: int}
     */
    public function searchCards(
        string $q = '',
        int $page = 1,
        int $pageSize = 50,
        string $language = 'en',
    ): array {
        return $this->request()
            ->get("/pokemon/v1/{$language}/cards", array_filter([
                'q'         => $q ?: null,
                'page'      => $page,
                'page_size' => $pageSize,
                'include'   => 'prices',     // bundle pricing in the card payload
            ]))
            ->throw()
            ->json();
    }

    /** @return array<string, mixed> */
    public function getCard(string $id, string $language = 'en'): array
    {
        return $this->request()
            ->get("/pokemon/v1/{$language}/cards/{$id}", ['include' => 'prices'])
            ->throw()
            ->json('data');
    }

    /** @return array<int, array<string, mixed>> */
    public function listExpansions(string $language = 'en'): array
    {
        return $this->request()
            ->get("/pokemon/v1/{$language}/expansions", ['page_size' => 250])
            ->throw()
            ->json('data');
    }

    /** @return array<int, array<string, mixed>> */
    public function getCardsInExpansion(string $expansionId, string $language = 'en'): array
    {
        $all = [];
        $page = 1;

        do {
            $response = $this->searchCards(
                q: "expansion.id:{$expansionId}",
                page: $page,
                pageSize: 250,
                language: $language,
            );
            $all = [...$all, ...($response['data'] ?? [])];
            $total = $response['total'] ?? count($all);
            $page++;
        } while (count($all) < $total && ! empty($response['data']));

        return $all;
    }

    /**
     * Sold listings for a card. Useful for our market-value lookup —
     * filter by source=ebay and a recent days window.
     *
     * @return array{data: array<int, array<string, mixed>>, total?: int}
     */
    public function getCardListings(
        string $cardId,
        ?string $source = null,
        ?int $days = 90,
        int $pageSize = 50,
        int $page = 1,
    ): array {
        return $this->request()
            ->get("/pokemon/v1/cards/{$cardId}/listings", array_filter([
                'source'    => $source,
                'days'      => $days,
                'page'      => $page,
                'page_size' => $pageSize,
            ]))
            ->throw()
            ->json();
    }
}
