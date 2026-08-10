<?php

namespace App\Enums;

enum ApiMode: string
{
    case Test = 'test';
    case Live = 'live';

    public function label(): string
    {
        return match ($this) {
            self::Test => 'Test (sandbox data)',
            self::Live => 'Live (production data)',
        };
    }
}
