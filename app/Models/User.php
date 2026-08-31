<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ArcaneResetPasswordNotification;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'google_id',
    'shipping_name', 'shipping_address_line_1', 'shipping_address_line_2',
    'shipping_city', 'shipping_postcode', 'shipping_country',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = ['role'];

    public function getRoleAttribute(): ?string
    {
        return $this->roles->first()?->name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }

    // existing traits, fillables, etc.
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * Convenience accessor if you stick to 1 store per seller.
     */
    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    public function ripWallet(): HasOne
    {
        return $this->hasOne(RipWallet::class);
    }

    /**
     * Where a "kept" Digital Rip gets posted — required before checkout
     * (see RipCheckoutService::createOrderWithRips()), same minimum-fields
     * convention as RipWallet::hasBankDetails().
     */
    public function hasShippingAddress(): bool
    {
        return filled($this->shipping_name)
            && filled($this->shipping_address_line_1)
            && filled($this->shipping_city)
            && filled($this->shipping_postcode);
    }

    /**
     * Multi-line formatted shipping address for display (e.g. the "view
     * address" popup on RipsToPostResource) — null rather than a
     * partially-blank address if the customer hasn't set one.
     *
     * @return string[]|null
     */
    public function shippingAddressLines(): ?array
    {
        if (! $this->hasShippingAddress()) {
            return null;
        }

        return array_values(array_filter([
            $this->shipping_name,
            $this->shipping_address_line_1,
            $this->shipping_address_line_2,
            $this->shipping_city,
            $this->shipping_postcode,
            $this->shipping_country,
        ]));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ArcaneResetPasswordNotification($token));
    }
}
