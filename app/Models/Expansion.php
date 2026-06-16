<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expansion extends Model
{
    protected $fillable = [
        'scrydex_id',
        'game',
        'name',
        'series',
        'code',
        'total',
        'printed_total',
        'language',
        'language_code',
        'release_date',
        'is_online_only',
        'logo',
        'symbol',
        'translations'
    ];
}
