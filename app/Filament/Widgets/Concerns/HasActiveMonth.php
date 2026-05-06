<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Transaction;
use Illuminate\Support\Carbon;

trait HasActiveMonth
{
    /**
     * Return the most relevant month: current month if it has transactions,
     * otherwise the last month with any data.
     */
    protected function activeMonth(): Carbon
    {
        $now = Carbon::now()->startOfMonth();

        $hasCurrent = Transaction::whereBetween('date', [
            $now->copy()->startOfMonth(),
            $now->copy()->endOfMonth(),
        ])->exists();

        if ($hasCurrent) {
            return $now;
        }

        $latest = Transaction::max('date');

        return $latest ? Carbon::parse($latest)->startOfMonth() : $now;
    }
}
