<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ImportStatus: string implements HasColor, HasLabel
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Failed     = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending    => 'In wachtrij',
            self::Processing => 'Bezig',
            self::Completed  => 'Voltooid',
            self::Failed     => 'Mislukt',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending    => 'gray',
            self::Processing => 'warning',
            self::Completed  => 'success',
            self::Failed     => 'danger',
        };
    }
}
