<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

class CategoryMerchants extends Page
{
    protected string $view = 'filament.pages.category-merchants';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    #[Url]
    public int $categoryId = 0;

    #[Url]
    public string $month = '';

    public function mount(): void
    {
        if (! $this->month) {
            $this->month = now()->format('Y-m');
        }
    }

    public function getTitle(): string
    {
        $category   = Category::find($this->categoryId);
        $monthLabel = Carbon::parse($this->month . '-01')->locale('nl')->translatedFormat('F Y');

        return ($category?->name ?? 'Categorie') . ' — ' . $monthLabel;
    }

    public function getPageData(): array
    {
        $start    = Carbon::parse($this->month . '-01')->startOfMonth();
        $end      = $start->copy()->endOfMonth();
        $category = Category::find($this->categoryId);

        $transactions = Transaction::where('type', 'debit')
            ->whereBetween('date', [$start, $end])
            ->where('category_id', $this->categoryId)
            ->with('merchant')
            ->orderBy('date')
            ->get();

        $merchants = $transactions
            ->groupBy(fn ($tx) => $tx->merchant_id ?? 'overige')
            ->map(function ($group, $key) {
                $amount = $group->sum('amount');

                return [
                    'name'         => $key === 'overige' ? 'Overige' : $group->first()->merchant->name,
                    'logo_url'     => $key === 'overige' ? null : $group->first()->merchant->logo_url,
                    'amount'       => (float) $amount,
                    'count'        => $group->count(),
                    'transactions' => $group
                        ->sortBy('date')
                        ->map(fn ($tx) => [
                            'date'   => $tx->date,
                            'amount' => (float) $tx->amount,
                        ])
                        ->values()
                        ->toArray(),
                ];
            })
            ->sortByDesc('amount')
            ->values()
            ->toArray();

        return compact('category', 'merchants', 'start');
    }

    public function getBackUrl(): string
    {
        return MonthlyReport::getUrl();
    }
}
