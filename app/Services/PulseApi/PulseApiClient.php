<?php

namespace App\Services\PulseApi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PulseApiClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
    ) {}

    protected function request(): PendingRequest
    {
        // throw: false — retry() otherwise throws on ANY non-2xx after exhausting
        // attempts, which breaks the deliberate status()/throw() handling below
        // (404 on getCard, 403 on batchGetCards).
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 300, throw: false)
            ->withHeaders(['x-api-key' => $this->apiKey]);
    }

    /**
     * Full-text card search with optional structured filters (set_id, rarity, sort_by, ...).
     * PulseAPI requires at least one search parameter.
     *
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function search(string $q = '', array $filters = [], int $page = 1, int $limit = 20): array
    {
        return $this->request()
            ->get('/api/v1/cards/search', array_filter([
                'q'     => $q ?: null,
                'page'  => $page,
                'limit' => $limit,
                ...$filters,
            ]))
            ->throw()
            ->json();
    }

    /**
     * Fetch a single card by its canonical product_id, e.g. "card:sv3pt5|199/165|null|null|null|null".
     *
     * @return array<string, mixed>|null
     */
    public function getCard(string $productId): ?array
    {
        $response = $this->request()->get('/api/v1/cards/' . rawurlencode($productId));

        if ($response->status() === 404) {
            return null;
        }

        return $response->throw()->json('data');
    }

    /**
     * Look up up to 50 cards per request (paid tier). Chunks automatically for larger arrays.
     * Falls back to individual getCard() calls per chunk on a 403 — keeps intake/refresh
     * working on a free/starter-tier key, just slower, and speeds up automatically the
     * moment the key is upgraded.
     *
     * @param  array<int, string>  $productIds
     * @return array<string, array<string, mixed>|null> keyed by product_id
     */
    public function batchGetCards(array $productIds): array
    {
        $results = [];

        foreach (array_chunk(array_values(array_unique($productIds)), 50) as $chunk) {
            if (empty($chunk)) continue;

            try {
                $data = $this->request()
                    ->post('/api/v1/cards/batch', ['product_ids' => $chunk])
                    ->throw()
                    ->json('data');

                $results = [...$results, ...$data];
            } catch (\Illuminate\Http\Client\RequestException $e) {
                if ($e->response->status() !== 403) {
                    throw $e;
                }

                foreach ($chunk as $productId) {
                    $results[$productId] = $this->getCard($productId);
                }
            }
        }

        // Plug any remaining gaps (lookup index lag, etc.) with a structured search
        // on the same set + number, rather than leaving the row unresolved.
        foreach ($results as $productId => $card) {
            if ($card === null) {
                $results[$productId] = $this->resolveViaSearch($productId);
            }
        }

        return $results;
    }

    /**
     * Best-effort recovery for a card that came back null from batch/get — re-derive it
     * via a structured search on the same set + number rather than giving up entirely.
     *
     * @return array<string, mixed>|null
     */
    protected function resolveViaSearch(string $productId): ?array
    {
        if (! str_starts_with($productId, 'card:')) {
            return null;
        }

        [$setId, $number] = explode('|', substr($productId, 5)) + [null, null];

        if (! $setId || ! $number) {
            return null;
        }

        $candidates = collect($this->search('', ['set_id' => $setId, 'card_number' => $number], limit: 10)['data'] ?? []);

        return $candidates->firstWhere('product_id', $productId) ?? $candidates->first();
    }
}
