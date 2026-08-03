<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'affiliate_code',
        'credit_balance_pence',
        'name',
        'description',
        'platforms',
        'social_links',
        'location',
        'contact_email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'postcode',
        'country',
        'vat_number',
        'public_page_enabled',
        'status',
        'logo'
    ];

    protected $casts = [
        'public_page_enabled' => 'boolean',
        'platforms'           => 'array',
        'social_links'        => 'array',
    ];

    public function user()      { return $this->belongsTo(User::class); }

    public function batches()   { return $this->hasMany(Batch::class); }

    public function invoices()  { return $this->hasMany(Invoice::class); }

    public function creditTransactions() { return $this->hasMany(StoreCreditTransaction::class); }

    public function getLogoAttribute(): ?string
    {
        return $this->attributes['logo'] ? route('image.show', ['path' => $this->attributes['logo']]) : null;
    }

    public function getRouteKeyName(): string { return 'slug'; }

    protected static function booted(): void
    {
        static::creating(function (Store $store) {
            if (blank($store->affiliate_code)) {
                $store->affiliate_code = static::generateAffiliateCode($store->name ?: $store->slug ?: 'STORE');
            }
        });
    }

    /**
     * A short, shareable code customers quote on /sell to get the affiliate bonus
     * (see config('selling.affiliate_bonus_percentage')) — derived from the store's
     * name so it's recognisable, with a random suffix to keep it unique.
     */
    public static function generateAffiliateCode(string $seed): string
    {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $seed));
        $base = substr($base, 0, 8) ?: 'STORE';

        do {
            $code = $base.random_int(100, 999);
        } while (static::where('affiliate_code', $code)->exists());

        return $code;
    }
}
