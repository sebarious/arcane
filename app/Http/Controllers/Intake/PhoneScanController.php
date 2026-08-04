<?php

namespace App\Http\Controllers\Intake;

use App\Filament\Resources\CardInventories\Pages\RapidIntake;
use App\Http\Controllers\Controller;
use App\Services\Intake\CardRowResolver;
use App\Services\Intake\ScanSession;
use App\Services\Vision\CardNumberExtractor;
use App\Services\Vision\GoogleVisionClient;
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

    public function frame(Request $request, string $token, ScanSession $sessions, CardRowResolver $resolver)
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

        try {
            $text = app(GoogleVisionClient::class)->detectText($bytes);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        if (blank($text)) {
            return response()->json(['status' => 'no_text']);
        }

        $number = CardNumberExtractor::extract($text);
        if (! $number) {
            return response()->json(['status' => 'no_number']);
        }

        // Same debounce reasoning as RapidIntake::scanFrame() — this controller is
        // stateless between requests, so the "last scanned" marker lives in cache too.
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

        $rows = [0 => $resolver->emptyRow()];
        $rows[0]['search_number'] = $number;

        $outcome = $resolver->applySearchResolution($rows, 0, $buyPercentage);

        $sessions->push($token, $rows[0]);

        $cardName = null;
        if ($outcome === 'resolved') {
            $resolved = CardRowResolver::decodeResolved($rows[0]['resolved'] ?? null);
            $cardName = $resolved['card_name'] ?? null;
        }

        return response()->json([
            'status'    => $outcome,
            'number'    => $number,
            'card_name' => $cardName,
        ]);
    }
}
