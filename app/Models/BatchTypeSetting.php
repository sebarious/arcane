<?php

namespace App\Models;

use App\Enums\BatchType;
use Illuminate\Database\Eloquent\Model;

class BatchTypeSetting extends Model
{
    protected $fillable = ['type', 'enabled'];

    protected function casts(): array
    {
        return [
            'type' => BatchType::class,
            'enabled' => 'boolean',
        ];
    }

    public static function isEnabled(BatchType $type): bool
    {
        return (bool) static::query()
            ->where('type', $type->value)
            ->value('enabled');
    }

    /** @return array<string, bool> BatchType value => enabled, one entry per tier. */
    public static function enabledMap(): array
    {
        return static::query()->pluck('enabled', 'type')
            ->mapWithKeys(fn ($enabled, $type) => [
                ($type instanceof BatchType ? $type->value : $type) => (bool) $enabled,
            ])
            ->all();
    }
}
