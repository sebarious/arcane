<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id',
        'affiliate_code',
        'credit_balance_pence',
        'bank_account_name',
        'bank_sort_code',
        'bank_account_number',
        'status',
    ];

    protected $casts = [
        'credit_balance_pence' => 'integer',
    ];

    /**
     * A short, memorable word + 3 digits (e.g. "ZEST482") — plain 3-4 letter
     * English words rather than random letters, so a generated code is
     * actually easy to read back over the phone. Deliberately independent
     * of Store::generateAffiliateCode()'s name-derived scheme — an affiliate
     * signs up with no name-worthy seed to draw from yet at this point, and
     * the whole point here is letting them regenerate until they like one
     * (see SignupController::suggestCode()), which a name-derived code
     * can't meaningfully offer.
     */
    protected const CODE_WORDS = [
        'ZEST', 'GLOW', 'PEAK', 'WOLF', 'RUBY', 'JADE', 'SAGE', 'FOX', 'IVY', 'OAK',
        'BOLT', 'SPARK', 'FLARE', 'GOLD', 'IRON', 'JET', 'NOVA', 'ONYX', 'PEARL', 'REEF',
        'SNOW', 'STAR', 'SUN', 'TIDE', 'WAVE', 'WIND', 'ZEN', 'ACE', 'ARC', 'BLAZE',
        'CEDAR', 'CLIFF', 'CORAL', 'CREST', 'DAWN', 'DUSK', 'ECHO', 'EMBER', 'FERN', 'FLAME',
        'FLASH', 'FLINT', 'FOG', 'FROST', 'GALE', 'GEM', 'GRIT', 'HAWK', 'HAZE', 'HOLLY',
        'HOP', 'INK', 'ISLE', 'KELP', 'KIWI', 'LARK', 'LEAF', 'LIME', 'LOTUS', 'LUNA',
        'LYNX', 'MAPLE', 'MESA', 'MICA', 'MIST', 'MOON', 'MOSS', 'MYTH', 'NOOK', 'OASIS',
        'OPAL', 'ORCA', 'OTTER', 'OWL', 'PALM', 'PINE', 'PLUM', 'POND', 'PUMA', 'QUARTZ',
        'QUILL', 'RAIN', 'RAVEN', 'REED', 'RIFT', 'RIVER', 'ROSE', 'RUST', 'SABLE', 'SAND',
        'SEAL', 'SHELL', 'SIGH', 'SILK', 'SKY', 'SLATE', 'SNAP', 'SNOWY', 'SPICE', 'SPRIG',
        'SPRUCE', 'STAG', 'STONE', 'STORM', 'SWAN', 'TEAL', 'THORN', 'TIGER', 'TOPAZ', 'TWIG',
        'VALE', 'VINE', 'VIOLET', 'WISP', 'WREN', 'YARD', 'ZEBRA', 'ZINC', 'AMBER', 'ASH',
        'ATLAS', 'AURA', 'BAY', 'BIRCH', 'BLOOM', 'BREEZE', 'BRICK', 'BROOK', 'CANYON', 'CHALK',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(AffiliateCreditTransaction::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    public function hasBankDetails(): bool
    {
        return filled($this->bank_account_name) && filled($this->bank_sort_code) && filled($this->bank_account_number);
    }

    protected static function booted(): void
    {
        static::creating(function (Affiliate $affiliate) {
            if (blank($affiliate->affiliate_code)) {
                $affiliate->affiliate_code = static::generateAffiliateCode();
            }
        });
    }

    /**
     * One random word + 3 digits, checked against every code already in use
     * (retried until it lands on a free one — the word list times 900 possible
     * suffixes per word is comfortably large enough that this never loops more
     * than a handful of times in practice).
     */
    public static function generateAffiliateCode(): string
    {
        do {
            $word = self::CODE_WORDS[array_rand(self::CODE_WORDS)];
            $code = $word.random_int(100, 999);
        } while (static::where('affiliate_code', $code)->exists());

        return $code;
    }
}
