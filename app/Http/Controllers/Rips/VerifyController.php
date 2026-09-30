<?php

namespace App\Http\Controllers\Rips;

use App\Http\Controllers\Controller;
use App\Models\Rip;
use App\Services\Verification\RipVerifier;
use Inertia\Inertia;

class VerifyController extends Controller
{
    /** GET /rips/{rip}/verify — public, no auth: provably-fair proof anyone can check, same as BatchVerifyController. */
    public function __invoke(Rip $rip, RipVerifier $verifier)
    {
        return Inertia::render('Rips/Verify', [
            'rip' => [
                'id' => $rip->id,
                'pack_name' => $rip->pack_name,
            ],
            'verification' => [
                'hash' => $rip->verification_hash,
                'seed' => $rip->isOpened() ? $rip->verification_seed : null,
            ],
            'result' => $verifier->verify($rip),
        ]);
    }
}
