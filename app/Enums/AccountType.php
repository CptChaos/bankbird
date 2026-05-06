<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccountType: string implements HasColor, HasLabel
{
    case Betaal = 'betaal';
    case Spaar  = 'spaar';
    case Credit = 'credit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Betaal => 'Betaalrekening',
            self::Spaar  => 'Spaarrekening',
            self::Credit => 'Creditcard',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Betaal => 'success',
            self::Spaar  => 'info',
            self::Credit => 'warning',
        };
    }
}
