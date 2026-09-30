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

        // A real multipart upload rather than a base64 JSON body: base64
        // inflates a photo by about a third and rides on post_max_size, and
        // when that's exceeded PHP discards the whole body before Laravel
        // sees it — taking the CSRF token with it, so the failure surfaces as
        // a confusing 419/500 rather than "that photo was too big".
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:12288'],
        ]);

        // Last one wins — retaking on the phone before the desktop has polled
        // should replace the earlier attempt, not queue up behind it.
        $sessions->put($token, $store->storeUpload($request->file('image')));

        return response()->json(['status' => 'stored']);
    }
}
