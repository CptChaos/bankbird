<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Transaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CategoryDrilldown extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.pages.category-drilldown';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-funnel';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Rapporten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Categorie Detail';
    }

    public function getTitle(): string
    {
        return 'Categorie Detail';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'category_id' => null,
            'merchant_id' => null,
            'from'        => now()->startOfMonth()->toDateString(),
            'until'       => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Categorie')
                    ->options(Category::whereNull('parent_id')->orderBy('name')->pluck('name', 'id'))
                    ->nullable()
                    ->live()
                    ->placeholder('Alle categorieën'),

                Select::make('merchant_id')
                    ->label('Merchant')
                    ->options(Merchant::orderBy('name')->pluck('name', 'id'))
                    ->nullable()
                    ->searchable()
                    ->live()
                    ->placeholder('Alle merchants'),

                DatePicker::make('from')
                    ->label('Van')
                    ->displayFormat('d-m-Y')
                    ->default(now()->startOfMonth())
                    ->live(),

                DatePicker::make('until')
                    ->label('Tot en met')
                    ->displayFormat('d-m-Y')
                    ->default(now())
                    ->live(),
            ])
            ->columns(4)
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $from       = $this->data['from'] ?? now()->startOfMonth()->toDateString();
                $until      = $this->data['until'] ?? now()->toDateString();
                $categoryId = $this->data['category_id'] ?? null;
                $merchantId = $this->data['merchant_id'] ?? null;

                $query = Transaction::query()
                    ->where('type', 'debit')
                    ->whereBetween('date', [$from, $until]);

                if ($categoryId) {
                    $childIds = Category::where('parent_id', $categoryId)->pluck('id');
                    $query->where(function ($q) use ($categoryId, $childIds) {
                        $q->where('category_id', $categoryId)
                          ->orWhereIn('category_id', $childIds);
                    });
                }

                if ($merchantId) {
                    $query->where('merchant_id', $merchantId);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Omschrijving')
                    ->limit(60)
                    ->searchable(),

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
                    ->badge(),

                TextColumn::make('amount')
                    ->label('Bedrag')
                    ->money('EUR')
                    ->color('danger')
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc');
    }

    public function getSummary(): array
    {
        $from       = $this->data['from'] ?? now()->startOfMonth()->toDateString();
        $until      = $this->data['until'] ?? now()->toDateString();
        $categoryId = $this->data['category_id'] ?? null;
        $merchantId = $this->data['merchant_id'] ?? null;

        $query = Transaction::where('type', 'debit')->whereBetween('date', [$from, $until]);

        if ($categoryId) {
            $childIds = Category::where('parent_id', $categoryId)->pluck('id');
            $query->where(function ($q) use ($categoryId, $childIds) {
                $q->where('category_id', $categoryId)->orWhereIn('category_id', $childIds);
            });
        }

        if ($merchantId) {
            $query->where('merchant_id', $merchantId);
        }

        $total = $query->sum('amount');
        $count = $query->count();
        $avg   = $count > 0 ? $total / $count : 0;

        return compact('total', 'count', 'avg');
    }
}
