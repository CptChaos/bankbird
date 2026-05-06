<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

trait RestrictsInDemoMode
{
    public static function canCreate(): bool
    {
        return ! \App\Support\Demo::active();
    }

    public static function canEdit(Model $record): bool
    {
        return ! \App\Support\Demo::active();
    }

    public static function canDelete(Model $record): bool
    {
        return ! \App\Support\Demo::active();
    }

    public static function canDeleteAny(): bool
    {
        return ! \App\Support\Demo::active();
    }
}
