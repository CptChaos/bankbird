<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasColor, HasLabel
{
    case Debit  = 'debit';
    case Credit = 'credit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Debit  => 'Af',
            self::Credit => 'Bij',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Debit  => 'danger',
            self::Credit => 'success',
        };
    }
}
