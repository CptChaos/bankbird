<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasActiveMonth;
use App\Models\Account;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountStatsOverview extends BaseWidget
{
    use HasActiveMonth;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $month     = $this->activeMonth();
        $start     = $month->copy()->startOfMonth();
        $end       = $month->copy()->endOfMonth();
        $prevStart = $month->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd   = $month->copy()->subMonthNoOverflow()->endOfMonth();

        $monthLabel = $month->locale('nl')->translatedFormat('F Y');

        $totalBalance = (float) Account::where('is_active', true)->sum('balance');

        $monthExpenses = (float) Transaction::where('type', 'debit')
            ->whereBetween('date', [$start, $end])
            ->whereDoesntHave('category', fn ($q) => $q->where('name', 'Sparen'))
            ->sum('amount');

        $monthIncome = (float) Transaction::where('type', 'credit')
            ->whereBetween('date', [$start, $end])
            ->whereDoesntHave('category', fn ($q) => $q->where('name', 'Sparen'))
            ->sum('amount');

        $prevExpenses = (float) Transaction::where('type', 'debit')
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->whereDoesntHave('category', fn ($q) => $q->where('name', 'Sparen'))
            ->sum('amount');

        $prevIncome = (float) Transaction::where('type', 'credit')
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->whereDoesntHave('category', fn ($q) => $q->where('name', 'Sparen'))
            ->sum('amount');

        $monthNet = $monthIncome - $monthExpenses;
        $prevNet  = $prevIncome - $prevExpenses;

        return [
            Stat::make('Vermogen', '€ ' . $this->fmt($totalBalance))
                ->description('Alle actieve rekeningen')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($totalBalance >= 0 ? 'success' : 'danger')
                ->extraAttributes(['data-counter' => 'true', 'data-counter-value' => $totalBalance]),

            Stat::make("Uitgaven {$monthLabel}", '€ ' . $this->fmt($monthExpenses))
                ->description($this->delta($monthExpenses, $prevExpenses, 'expenses'))
                ->descriptionIcon($monthExpenses > $prevExpenses ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('danger')
                ->extraAttributes(['data-counter' => 'true', 'data-counter-value' => $monthExpenses]),

            Stat::make("Inkomsten {$monthLabel}", '€ ' . $this->fmt($monthIncome))
                ->description($this->delta($monthIncome, $prevIncome, 'income'))
                ->descriptionIcon($monthIncome >= $prevIncome ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->extraAttributes(['data-counter' => 'true', 'data-counter-value' => $monthIncome]),

            Stat::make('Netto saldo', '€ ' . $this->fmt($monthNet))
                ->description($this->delta($monthNet, $prevNet, 'net'))
                ->descriptionIcon($monthNet >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($monthNet >= 0 ? 'success' : 'danger')
                ->extraAttributes(['data-counter' => 'true', 'data-counter-value' => $monthNet]),
        ];
    }

    private function fmt(float $v): string
    {
        return number_format($v, 2, ',', '.');
    }

    private function delta(float $now, float $prev, string $context): string
    {
        if ($prev == 0.0) {
            return 'Geen vergelijking';
        }

        $diff = $now - $prev;
        $pct  = round(($diff / abs($prev)) * 100, 1);
        $sign = $diff >= 0 ? '+' : '';

        return "{$sign}{$pct}% vs vorige maand";
    }
}
