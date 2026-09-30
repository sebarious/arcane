<?php

namespace App\Http\Controllers\Intake;

use App\Http\Controllers\Controller;
use App\Services\Intake\CardPhotoSession;
use App\Services\Intake\CardPhotoStore;
use Illuminate\Http\Request;

/**
 * Unauthenticated, token-scoped camera page for photographing a card with a
 * phone while the admin edits it on a desktop — see CardPhotoSession for how
 * the token is minted and expired, and the card-photo-capture form field for
 * how the desktop picks the result back up.
 */
class PhonePhotoController extends Controller
{
    public function show(string $token, CardPhotoSession $sessions)
    {
        if (! $sessions->exists($token)) {
            return view('intake.phone-photo-expired');
        }

        return view('intake.phone-photo', ['token' => $token]);
    }

    public function store(Request $request, string $token, CardPhotoSession $sessions, CardPhotoStore $store)
    {
        if (! $sessions->exists($token)) {
            return response()->json(['status' => 'expired'], 410);
        }

        $validated = $request->validate([
            'image' => ['required', 'string'],
        ]);

        $path = $store->storeDataUrl($validated['image']);

        if ($path === null) {
            return response()->json(['status' => 'error', 'message' => 'That photo could not be read.'], 422);
        }

        // Last one wins — retaking on the phone before the desktop has polled
        // should replace the earlier attempt, not queue up behind it.
        $sessions->put($token, $path);

        return response()->json(['status' => 'stored']);
    }
}
