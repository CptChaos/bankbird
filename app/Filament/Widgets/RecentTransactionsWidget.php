<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Laatste transacties';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->orderByDesc('date')
                    ->orderByDesc('id')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Datum')
                    ->date('d-m-Y'),

                TextColumn::make('account.name')
                    ->label('Rekening')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('description')
                    ->label('Omschrijving')
                    ->limit(45),

                ImageColumn::make('merchant.logo_url')
                    ->label('')
                    ->imageHeight(24)
                    ->square()
                    ->extraImgAttributes(['class' => 'object-contain'])
                    ->grow(false),

                TextColumn::make('merchant.name')
                    ->label('Merchant')
                    ->placeholder('—'),

                TextColumn::make('category.name')
                    ->label('Categorie')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('amount')
                    ->label('Bedrag')
                    ->money('EUR')
                    ->color(fn (Transaction $record): string => $record->type === TransactionType::Debit ? 'danger' : 'success')
                    ->alignEnd()
                    ->weight('semibold'),
            ])
            ->paginated(false);
    }
}
