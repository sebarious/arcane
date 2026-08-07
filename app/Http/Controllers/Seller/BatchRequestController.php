<?php

namespace App\Http\Controllers\Seller;

use App\Enums\BatchType;
use App\Enums\Game;
use App\Http\Controllers\Controller;
use App\Mail\BatchRequestSubmittedMail;
use App\Models\Batch;
use App\Models\BatchTypeSetting;
use App\Models\User;
use App\Services\Batches\BatchDesign;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class BatchRequestController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        if (! $user->hasRole('seller')) {
            abort(403);
        }
        if (! config('batches.requests_enabled', true)) {
            abort(403, 'Batch requests are temporarily unavailable.');
        }

        $stores = $user->stores()->get(['id', 'name']);

        // Product info for the form
        $enabledTypes = BatchTypeSetting::enabledMap();

        $products = [];
        foreach (Game::cases() as $game) {
            foreach (BatchType::cases() as $type) {
                $products[] = [
                    'game' => $game->value,
                    'game_label' => $game->label(),
                    'type' => $type->value,
                    'type_label' => $type->label(),
                    'packs' => BatchDesign::packCount($game, $type),
                    'price_pounds' => BatchDesign::targetSalePrice($game, $type) / 100,
                    'enabled' => $enabledTypes[$type->value] ?? true,
                ];
            }
        }

        // Eligible merge targets — this seller's own already-generated batches that
        // haven't themselves been merged away yet, so staff have something to merge
        // the new batch's leftovers into (or vice versa) once it's generated.
        $mergeableBatches = Batch::query()
            ->whereIn('store_id', $stores->pluck('id'))
            ->whereIn('status', ['committed', 'dispatched'])
            ->whereNull('merged_into_batch_id')
            ->orderByDesc('created_at')
            ->get(['id', 'reference', 'store_id', 'type', 'pack_count']);

        return Inertia::render('Seller/BatchRequest', [
            'stores' => $stores,
            'products' => $products,
            'mergeableBatches' => $mergeableBatches,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user->hasRole('seller')) {
            abort(403);
        }
        if (! config('batches.requests_enabled', true)) {
            abort(403, 'Batch requests are temporarily unavailable.');
        }

        $data = $request->validate([
            'store_id' => ['required', 'integer'],
            'game' => ['required', 'string'],
            'type' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'merge_request_batch_id' => ['nullable', 'integer'],
        ]);

        // Verify seller owns this store
        if (! $user->stores()->where('id', $data['store_id'])->exists()) {
            abort(403);
        }

        // If a merge target was picked, make sure it's actually one of this seller's
        // own eligible batches for that same store — never trust the client here.
        $mergeRequestBatchId = null;
        if (! empty($data['merge_request_batch_id'])) {
            $mergeRequestBatchId = Batch::query()
                ->where('id', $data['merge_request_batch_id'])
                ->where('store_id', $data['store_id'])
                ->whereIn('status', ['committed', 'dispatched'])
                ->whereNull('merged_into_batch_id')
                ->value('id');
        }

        $game = Game::from($data['game']);
        $type = BatchType::from($data['type']);

        if (! BatchTypeSetting::isEnabled($type)) {
            abort(403, "{$type->label()} isn't currently available to request.");
        }

        $notes = collect([
            "Requested by seller {$user->email}",
            $data['notes'] ?? null,
            $mergeRequestBatchId
                ? 'Seller asked for batch #'.$mergeRequestBatchId.' to be merged into this one once generated.'
                : null,
        ])->filter()->implode(' — ');

        $batch = Batch::create([
            'reference' => Batch::nextReference(),
            'store_id' => $data['store_id'],
            'game' => $game->value,
            'type' => $type->value,
            'status' => 'draft',
            'pack_count' => BatchDesign::packCount($game, $type),
            'sale_price_pence' => BatchDesign::targetSalePrice($game, $type),
            'total_cost_pence' => 0,
            'total_market_value_pence' => 0,
            'margin_pence' => 0,
            'margin_scheme_vat_pence' => 0,
            'admin_notes' => $notes,
            'merge_request_batch_id' => $mergeRequestBatchId,
        ]);

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            // Email each admin
            Mail::to($admin->email)->send(
                new BatchRequestSubmittedMail($batch, $user)
            );
            // Database notification for Filament / notifications table
            Notification::make()
                ->title('New batch request')
                ->body("{$user->name} requested {$type->label()} for store ID {$data['store_id']}.")
                ->sendToDatabase($admin);
        }

        return redirect()->route('seller.dashboard')
            ->with('success', "Batch {$batch->reference} requested. We'll review and generate it shortly.");
    }
}
