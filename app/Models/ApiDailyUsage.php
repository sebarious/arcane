<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApiDailyUsage extends Model
{
    protected $table = 'api_daily_usage';

    protected $fillable = ['store_id', 'date', 'request_count'];

    protected $casts = [
        'date' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Records one request against $store's count for today, unless that would
     * push it past Store::daily_request_limit — in which case nothing is
     * recorded and false is returned. Row-locked inside a transaction so two
     * concurrent requests can't both slip through right at the boundary.
     */
    public static function attemptIncrement(Store $store): bool
    {
        $today = Carbon::today()->toDateString();

        return DB::transaction(function () use ($store, $today) {
            // createOrFirst (not firstOrCreate) so a simultaneous first-request-
            // of-the-day from two workers can't both try to insert the same
            // (store_id, date) row — it catches the unique-constraint race and
            // re-fetches instead of throwing.
            $usage = static::query()->createOrFirst(
                ['store_id' => $store->id, 'date' => $today],
                ['request_count' => 0],
            );

            $usage = static::query()->whereKey($usage->id)->lockForUpdate()->firstOrFail();

            if ($usage->request_count >= $store->daily_request_limit) {
                return false;
            }

            $usage->increment('request_count');

            return true;
        });
    }

    /** Requests recorded so far today for $store — for display, not enforcement. */
    public static function usedToday(Store $store): int
    {
        return (int) static::query()
            ->where('store_id', $store->id)
            ->where('date', Carbon::today()->toDateString())
            ->value('request_count');
    }

    /**
     * @return array<int, array{date: string, count: int}> One entry per day in
     *  the trailing $days window (oldest first), zero-filled for days with no
     *  recorded calls.
     */
    public static function trailing(Store $store, int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1);

        $rows = static::query()
            ->where('store_id', $store->id)
            ->where('date', '>=', $start->toDateString())
            ->get()
            ->keyBy(fn (self $row) => $row->date->toDateString());

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($start, $rows) {
                $date = $start->copy()->addDays($offset);

                return [
                    'date' => $date->toDateString(),
                    'count' => (int) ($rows[$date->toDateString()]->request_count ?? 0),
                ];
            })
            ->all();
    }
}
