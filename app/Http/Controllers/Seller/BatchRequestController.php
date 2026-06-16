<?php

namespace App\Http\Controllers\Seller;

use App\Enums\BatchType;
use App\Enums\Game;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Services\Batches\BatchDesign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Filament\Notifications\Notification;
use App\Models\User;

class BatchRequestController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        if (! $user->hasRole('seller')) abort(403);

        $stores = $user->stores()->get(['id', 'name']);

        // Product info for the form
        $products = [];
        foreach (Game::cases() as $game) {
            foreach (BatchType::cases() as $type) {
                $products[] = [
                    'game'         => $game->value,
                    'game_label'   => $game->label(),
                    'type'         => $type->value,
                    'type_label'   => $type->label(),
                    'packs'        => BatchDesign::packCount($game, $type),
                    'price_pounds' => BatchDesign::targetSalePrice($game, $type) / 100,
                ];
            }
        }

        return Inertia::render('Seller/BatchRequest', [
            'stores'   => $stores,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user->hasRole('seller')) abort(403);

        $data = $request->validate([
            'store_id' => ['required', 'integer'],
            'game'     => ['required', 'string'],
            'type'     => ['required', 'string'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        // Verify seller owns this store
        if (! $user->stores()->where('id', $data['store_id'])->exists()) {
            abort(403);
        }

        $game = Game::from($data['game']);
        $type = BatchType::from($data['type']);

        $batch = Batch::create([
            'reference'                => Batch::nextReference(),
            'store_id'                 => $data['store_id'],
            'game'                     => $game->value,
            'type'                     => $type->value,
            'status'                   => 'draft',
            'pack_count'               => BatchDesign::packCount($game, $type),
            'sale_price_pence'         => BatchDesign::targetSalePrice($game, $type),
            'total_cost_pence'         => 0,
            'total_market_value_pence' => 0,
            'margin_pence'             => 0,
            'margin_scheme_vat_pence'  => 0,
            'admin_notes'              => $data['notes']
                ? "Requested by seller {$user->email}: {$data['notes']}"
                : "Requested by seller {$user->email}",
        ]);

        Notification::make()
            ->title('New batch request')
            ->body("{$user->email} requested {$type->label()} for store ID {$data['store_id']}")
            ->success()
            ->sendToDatabase(User::role('admin')->get());

        return redirect()->route('seller.dashboard')
            ->with('success', "Batch {$batch->reference} requested. We'll review and generate it shortly.");
    }
}
