<?php

namespace App\Filament\Pages;

use App\Filament\Pages\CategoryMerchants;
use App\Models\Transaction;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

class MonthlyReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.monthly-report';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Rapporten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Maandrapport';
    }

    public function getTitle(): string
    {
        return 'Maandrapport';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public ?array $data = [];

    #[Url]
    public string $month = '';

    public function mount(): void
    {
        $this->form->fill([
            'month' => $this->month ?: now()->format('Y-m'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $months = [];
        for ($i = 0; $i < 24; $i++) {
            $date = now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->locale('nl')->translatedFormat('F Y');
        }

        return $schema
            ->components([
                Select::make('month')
                    ->label('Maand')
                    ->options($months)
                    ->default(now()->format('Y-m'))
                    ->live(),
            ])
            ->statePath('data');
    }

    public function getReportData(): array
    {
        $monthKey = $this->data['month'] ?? now()->format('Y-m');
        $start    = Carbon::parse($monthKey . '-01')->startOfMonth();
        $end      = $start->copy()->endOfMonth();

        $totalIncome   = Transaction::where('type', 'credit')->whereBetween('date', [$start, $end])->sum('amount');
        $totalExpenses = Transaction::where('type', 'debit')->whereBetween('date', [$start, $end])->sum('amount');
        $net           = $totalIncome - $totalExpenses;

        $categoryBreakdown = Transaction::where('type', 'debit')
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('category_id')
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($group) use ($totalExpenses) {
                $amount = $group->sum('amount');

                return [
                    'id'         => $group->first()->category->id,
                    'name'       => $group->first()->category->name,
                    'color'      => $group->first()->category->color,
                    'amount'     => $amount,
                    'percentage' => $totalExpenses > 0 ? round(($amount / $totalExpenses) * 100, 1) : 0,
                    'count'      => $group->count(),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        return compact('totalIncome', 'totalExpenses', 'net', 'categoryBreakdown', 'start', 'end');
    }

    public function goToCategory(int $id): void
    {
        $month = $this->data['month'] ?? now()->format('Y-m');
        $url   = CategoryMerchants::getUrl() . '?' . http_build_query(['categoryId' => $id, 'month' => $month]);

        $this->redirect($url, navigate: true);
    }
}
