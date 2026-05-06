<?php

namespace App\Models;

use App\Services\MerchantPatternService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merchant extends Model
{
    protected $fillable = [
        'name', 'normalized_name', 'category_id', 'logo_url', 'match_patterns',
    ];

    protected function casts(): array
    {
        return [
            'match_patterns' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (Merchant $merchant) {
            if ($merchant->wasChanged('category_id')) {
                $merchant->transactions()->update([
                    'category_id' => $merchant->category_id,
                ]);
            }

            if ($merchant->wasChanged('match_patterns')) {
                app(MerchantPatternService::class)->syncMerchant($merchant);
            }
        });

        static::created(function (Merchant $merchant) {
            if (! empty($merchant->match_patterns)) {
                app(MerchantPatternService::class)->syncMerchant($merchant);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
