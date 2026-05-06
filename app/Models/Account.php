<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'iban', 'color', 'icon', 'balance', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type'      => AccountType::class,
            'balance'   => 'decimal:2',
            'is_active' => 'boolean',
            'iban'      => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $query) {
            if (auth()->check()) {
                $query->where('accounts.user_id', auth()->id());
            }
        });

        static::creating(function (Account $account) {
            if (! $account->user_id && auth()->check()) {
                $account->user_id = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function imports(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    public static function recalculateBalance(int $accountId): void
    {
        $account = static::withoutGlobalScopes()->findOrFail($accountId);

        $balance = Transaction::withoutGlobalScopes()
            ->where('account_id', $accountId)
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) as net")
            ->value('net') ?? 0;

        $account->update(['balance' => $balance]);
    }
}
