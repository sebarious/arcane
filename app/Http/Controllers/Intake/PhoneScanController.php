<?php

namespace App\Http\Controllers\Intake;

use App\Filament\Resources\CardInventories\Pages\RapidIntake;
use App\Http\Controllers\Controller;
use App\Services\Intake\CardRowResolver;
use App\Services\Intake\ScanSession;
use App\Services\Vision\RotatingFrameScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Unauthenticated, token-scoped endpoints for the "scan with phone" feature —
 * see ScanSession for how the token is minted/expired and RapidIntake's
 * pollPhoneScans() for how the desktop merges what's pushed here.
 */
class PhoneScanController extends Controller
{
    public function show(string $token, ScanSession $sessions)
    {
        if (! $sessions->exists($token)) {
            return view('intake.phone-scan-expired');
        }

        return view('intake.phone-scan', ['token' => $token]);
    }

    public function frame(Request $request, string $token, ScanSession $sessions, CardRowResolver $resolver, RotatingFrameScanner $scanner)
    {
        if (! $sessions->exists($token)) {
            return response()->json(['status' => 'expired'], 410);
        }

        $validated = $request->validate([
            'image' => ['required', 'string'],
        ]);

        $imageDataUrl = $validated['image'];
        if (str_contains($imageDataUrl, ',')) {
            $imageDataUrl = substr($imageDataUrl, strpos($imageDataUrl, ',') + 1);
        }

        $bytes = base64_decode($imageDataUrl, true);
        if ($bytes === false || $bytes === '') {
            return response()->json(['status' => 'error', 'message' => 'Could not read that frame.']);
        }

        // Once we know which rotation this phone/browser needs, remember it (per
        // session, this controller is stateless between requests) so subsequent
        // frames try it first instead of re-sweeping every rotation.
        $rotationKey = "rapid_intake_scan_session_rotation:{$token}";

        try {
            $scan = $scanner->scan($bytes, Cache::get($rotationKey));
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $number = $scan['number'];
        if (! $number) {
            return response()->json(['status' => 'no_number']);
        }

        Cache::put($rotationKey, $scan['rotation'], now()->addMinutes(20));

        // Same card almost certainly still held up to the camera — don't spam-add it.
        $dedupeKey = "rapid_intake_scan_session_last:{$token}";
        $last = Cache::get($dedupeKey);
        if ($last && $last['number'] === $number && $last['at'] > (time() - 8)) {
            return response()->json(['status' => 'duplicate', 'number' => $number]);
        }
        Cache::put($dedupeKey, ['number' => $number, 'at' => time()], now()->addMinutes(20));

        if ($sessions->count($token) >= RapidIntake::MAX_ITEMS) {
            return response()->json(['status' => 'limit_reached', 'number' => $number]);
        }

        $buyPercentage = $sessions->buyPercentage($token) ?? 0.0;

        // A scan that finds nothing at all doesn't get pushed into the session —
        // see RapidIntake::scanFrame() for the full reasoning (sort into piles as
        // you go, without an unresolved row left behind to clean up later).
        $rows = [0 => $resolver->emptyRow()];
        $rows[0]['search_number'] = $number;

        $outcome = $resolver->applySearchResolution($rows, 0, $buyPercentage, $scan['setCode']);

        $cardName = null;
        if ($outcome === 'resolved') {
            $resolved = CardRowResolver::decodeResolved($rows[0]['resolved'] ?? null);
            $cardName = $resolved['card_name'] ?? null;
        }

        if ($outcome !== 'not_found') {
            $sessions->push($token, $rows[0]);
        }

        return response()->json([
            'status'    => $outcome,
            'number'    => $number,
            'card_name' => $cardName,
        ]);
    }
}
