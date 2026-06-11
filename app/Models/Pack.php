<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $fillable = ['batch_id', 'sequence_no', 'status', 'sold_at'];
    protected $casts = ['sold_at' => 'datetime'];

    public function batch() { return $this->belongsTo(Batch::class); }
    public function card()  { return $this->hasOne(CardInventory::class); }

}
