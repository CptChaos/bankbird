<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'account_id', 'import_id', 'date', 'description', 'raw_description', 'amount',
        'type', 'category_id', 'merchant_id', 'counterpart_iban',
        'imported_at', 'import_hash',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $query) {
            if (auth()->check()) {
                $query->where('transactions.user_id', auth()->id());
            }
        });

        static::creating(function (Transaction $transaction) {
            if (! $transaction->user_id && auth()->check()) {
                $transaction->user_id = auth()->id();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date'             => 'date',
            'amount'           => 'decimal:2',
            'type'             => TransactionType::class,
            'imported_at'      => 'datetime',
            'counterpart_iban' => 'encrypted',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
