<?php

namespace App\Services\Intake;

use App\Enums\Game;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Short-lived, cache-backed pairing session that lets a phone act as a remote
 * scanner for Rapid Intake: the desktop creates a session and polls it, the
 * phone (an unauthenticated, token-scoped page — see PhoneScanController)
 * pushes resolved rows into it. The random token is the only credential;
 * TTL keeps a leaked/bookmarked link from staying useful for long.
 */
class ScanSession
{
    protected const TTL_MINUTES = 20;

    public function create(float $buyPercentage, Game $game): string
    {
        $token = Str::random(40);

        Cache::put($this->key($token), [
            'entries' => [],
            'next_id' => 1,
            'buy_percentage' => $buyPercentage,
            'game' => $game->value,
        ], now()->addMinutes(self::TTL_MINUTES));

        return $token;
    }

    public function exists(string $token): bool
    {
        return Cache::has($this->key($token));
    }

    public function forget(string $token): void
    {
        Cache::forget($this->key($token));
    }

    public function buyPercentage(string $token): ?float
    {
        return Cache::get($this->key($token))['buy_percentage'] ?? null;
    }

    public function game(string $token): ?Game
    {
        return Game::tryFrom(Cache::get($this->key($token))['game'] ?? '');
    }

    public function count(string $token): int
    {
        return count(Cache::get($this->key($token))['entries'] ?? []);
    }

    /**
     * @return int|null the new entry's id, or null if the session has expired
     */
    public function push(string $token, array $row): ?int
    {
        $key = $this->key($token);
        $state = Cache::get($key);

        if (! $state) {
            return null;
        }

        $id = $state['next_id'];
        $state['entries'][$id] = $row;
        $state['next_id'] = $id + 1;

        Cache::put($key, $state, now()->addMinutes(self::TTL_MINUTES));

        return $id;
    }

    /**
     * @return array<int, array> entries with id > $afterId, keyed by id
     */
    public function since(string $token, int $afterId): array
    {
        $entries = Cache::get($this->key($token))['entries'] ?? [];

        return collect($entries)
            ->filter(fn ($row, $id) => $id > $afterId)
            ->all();
    }

    protected function key(string $token): string
    {
        return "rapid_intake_scan_session:{$token}";
    }
}
