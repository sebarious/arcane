<?php

namespace App\Services\Intake;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Short-lived pairing session that lets a phone take the photo for a card the
 * admin is editing on a desktop — the same handoff Rapid Intake uses for
 * scanning (see ScanSession), narrowed to carrying a single stored image path
 * back rather than a list of resolved rows.
 *
 * The random token is the only credential, so the TTL is deliberately short:
 * a bookmarked or shoulder-surfed link stops being useful quickly, and the
 * worst it grants is the ability to upload one image.
 */
class CardPhotoSession
{
    protected const TTL_MINUTES = 15;

    public function create(): string
    {
        $token = Str::random(40);

        Cache::put($this->key($token), ['path' => null], now()->addMinutes(self::TTL_MINUTES));

        return $token;
    }

    public function exists(string $token): bool
    {
        return Cache::has($this->key($token));
    }

    /** The stored disk path the phone uploaded, or null if it hasn't yet. */
    public function path(string $token): ?string
    {
        return Cache::get($this->key($token))['path'] ?? null;
    }

    public function put(string $token, string $path): void
    {
        if (! $this->exists($token)) {
            return;
        }

        Cache::put($this->key($token), ['path' => $path], now()->addMinutes(self::TTL_MINUTES));
    }

    public function forget(string $token): void
    {
        Cache::forget($this->key($token));
    }

    protected function key(string $token): string
    {
        return "card_photo_session:{$token}";
    }
}
