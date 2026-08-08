<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton settings row (id 1, seeded by its migration) for the seller API's
 * global rate limit — editable from Filament (see App\Filament\Pages\ApiSettings)
 * rather than a config file, so it can be tuned without a deploy. The daily
 * request quota lives per-store instead (Store::daily_request_limit) since
 * that one's meant to vary seller to seller.
 */
class ApiSetting extends Model
{
    protected $fillable = ['rate_limit_per_minute'];

    protected $casts = [
        'rate_limit_per_minute' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->findOrFail(1);
    }
}
