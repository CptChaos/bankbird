<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasActiveMonth;
use App\Models\Transaction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopMerchantsThisMonth extends BaseWidget
{
    use HasActiveMonth;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): string
    {
        return 'Top merchants — ' . $this->activeMonth()->locale('nl')->translatedFormat('F Y');
    }

    public function table(Table $table): Table
    {
        $month = $this->activeMonth();
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        return $table
            ->query(
                Transaction::query()
                    ->selectRaw('MIN(id) as id, merchant_id, SUM(amount) as total_amount, COUNT(*) as tx_count')
                    ->where('type', 'debit')
                    ->whereBetween('date', [$start, $end])
                    ->whereNotNull('merchant_id')
                    ->whereHas('category', fn ($q) => $q->where('name', '!=', 'Sparen'))
                    ->groupBy('merchant_id')
                    ->orderByDesc('total_amount')
                    ->limit(10)
            )
            ->columns([
                ImageColumn::make('merchant.logo_url')
                    ->label('')
                    ->imageHeight(24)
                    ->square()
                    ->extraImgAttributes(['class' => 'object-contain'])
                    ->grow(false),

                TextColumn::make('merchant.name')
                    ->label('Merchant')
                    ->weight('semibold'),

                TextColumn::make('merchant.category.name')
                    ->label('Categorie')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('tx_count')
                    ->label('#')
                    ->alignEnd(),

                TextColumn::make('total_amount')
                    ->label('Totaal')
                    ->money('EUR')
                    ->color('danger')
                    ->alignEnd()
                    ->weight('semibold'),
            ])
            ->paginated(false);
    }
}
