<?php

namespace App\Http\Controllers;

use App\Services\Batches\TestBatchService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TestBatchController extends Controller
{
    public function __invoke(TestBatchService $service)
    {
        $batch = $service->current();

        // First-ever view (row doesn't exist yet, or exists but a prior
        // refresh attempt failed before ever writing a snapshot) — generate
        // synchronously rather than leaving the page blank until the next
        // weekly cron tick.
        if (! $batch || $batch->demo_snapshot === null) {
            try {
                $batch = $service->refresh();
            } catch (\RuntimeException $e) {
                Log::warning('test-batch first-view generation failed', ['exception' => $e]);

                return Inertia::render('TestBatch', ['available' => false]);
            }
        }

        $snapshot = $batch->demo_snapshot;
        $cardsByBand = collect($snapshot['cards'])->groupBy('band');

        $bands = [];
        foreach (['mythic', 'legendary', 'super', 'rare', 'common'] as $band) {
            $cards = $cardsByBand->get($band, collect());
            $bands[$band] = [
                'count' => $cards->count(),
                'cards' => $cards->map(fn (array $c) => [
                    'name' => $c['name'],
                    'set' => $c['set'],
                    'number' => $c['number'],
                    'image' => $c['image'],
                    'band' => $c['band'],
                    'market_price' => $c['market_value_pence'] ? round($c['market_value_pence'] / 100, 2) : null,
                    'product_badges' => $c['product_badges'],
                ])->values(),
            ];
        }

        return Inertia::render('TestBatch', [
            'available' => true,
            'batch' => [
                'type' => $batch->type?->value,
                'game_label' => $batch->game?->label(),
                'pack_count' => $batch->pack_count,
                'generated_at' => $batch->updated_at?->format('d M Y'),
            ],
            'bands' => $bands,
        ]);
    }
}
