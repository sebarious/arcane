<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'user_id', 'slug', 'name', 'contact_email', 'phone',
        'address_line_1', 'address_line_2', 'city', 'postcode', 'country',
        'vat_number', 'public_page_enabled', 'status',
    ];

    protected $casts = [
        'public_page_enabled' => 'boolean',
    ];

    public function user()      { return $this->belongsTo(User::class); }

    public function batches()   { return $this->hasMany(Batch::class); }

    public function invoices()  { return $this->hasMany(Invoice::class); }
    
    public function getRouteKeyName(): string { return 'slug'; }
}
