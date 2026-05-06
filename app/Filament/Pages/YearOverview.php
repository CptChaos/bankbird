<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class YearOverview extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.year-overview';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Rapporten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Jaaroverzicht';
    }

    public function getTitle(): string
    {
        return 'Jaaroverzicht';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['year' => (string) now()->year]);
    }

    public function form(Schema $schema): Schema
    {
        $years = [];
        for ($y = now()->year; $y >= now()->year - 5; $y--) {
            $years[(string) $y] = (string) $y;
        }

        return $schema
            ->components([
                Select::make('year')
                    ->label('Jaar')
                    ->options($years)
                    ->default((string) now()->year)
                    ->live(),
            ])
            ->statePath('data');
    }

    public function getYearData(): array
    {
        $year  = (int) ($this->data['year'] ?? now()->year);
        $rows  = [];
        $totalIncome   = 0;
        $totalExpenses = 0;

        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();

            $income   = (float) Transaction::where('type', 'credit')->whereBetween('date', [$start, $end])->sum('amount');
            $expenses = (float) Transaction::where('type', 'debit')->whereBetween('date', [$start, $end])->sum('amount');
            $net      = $income - $expenses;

            $totalIncome   += $income;
            $totalExpenses += $expenses;

            $rows[] = [
                'month'    => $start->locale('nl')->translatedFormat('F'),
                'monthKey' => $start->format('Y-m'),
                'income'   => $income,
                'expenses' => $expenses,
                'net'      => $net,
            ];
        }

        return [
            'rows'          => $rows,
            'totalIncome'   => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'totalNet'      => $totalIncome - $totalExpenses,
            'year'          => $year,
        ];
    }
}
