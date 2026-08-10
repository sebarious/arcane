<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'store_id', 'method', 'path', 'status_code', 'duration_ms', 'ip', 'response_summary', 'created_at',
    ];

    protected $casts = [
        'response_summary' => 'array',
        'created_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
