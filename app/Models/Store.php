<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Store extends Model
{
    protected $fillable = [
        'user_id', 'slug', 'name', 'contact_email', 'phone',
        'address_line_1', 'address_line_2', 'city', 'postcode', 'country',
        'vat_number', 'public_page_enabled', 'status', 'logo'
    ];

    protected $casts = [
        'public_page_enabled' => 'boolean',
    ];

    public function user()      { return $this->belongsTo(User::class); }

    public function batches()   { return $this->hasMany(Batch::class); }

    public function invoices()  { return $this->hasMany(Invoice::class); }

    public function getLogoAttribute(): ?string
    {
        return $this->attributes['logo'] ? URL::temporarySignedRoute('image.show', now()->addMinutes(5), ['path' => $this->attributes['logo']]) : null;
    }

    public function getRouteKeyName(): string { return 'slug'; }
}
