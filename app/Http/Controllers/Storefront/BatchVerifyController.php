<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Store;
use App\Services\Verification\BatchVerifier;
use Inertia\Inertia;

class BatchVerifyController extends Controller
{
    public function show(Store $store, Batch $batch, BatchVerifier $verifier)
    {
        if ($batch->store_id !== $store->id) {
            abort(404);
        }

        return Inertia::render('Storefront/BatchVerify', [
            'store' => [
                'slug' => $store->slug,
                'name' => $store->name,
            ],
            'batch' => [
                'id' => $batch->id,
                'reference' => $batch->reference,
            ],
            'verification' => [
                'id' => $batch->verification_hash,
                'seed' => $batch->isVerificationRevealed() ? $batch->verification_seed : null,
            ],
            'result' => $verifier->verify($batch),
        ]);
    }
}
