<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Merchant;
use App\Services\MerchantMapperService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Locked;

class ViewCategory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = CategoryResource::class;

    protected string $view = 'filament.resources.category-resource.pages.view-category';

    #[Locked]
    public ?int $recordId = null;

    public function mount(int|string $record): void
    {
        $this->recordId = (int) $record;
    }

    public function getRecord(): Category
    {
        return Category::findOrFail($this->recordId);
    }

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Merchant::query()->where('category_id', $this->recordId))
            ->columns([
                ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->imageHeight(28)
                    ->square()
                    ->extraImgAttributes(['class' => 'object-contain']),

                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('normalized_name')
                    ->label('Genormaliseerd')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('transactions_count')
                    ->label('Transacties')
                    ->counts('transactions')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Merchant toevoegen')
                    ->icon('heroicon-o-building-storefront')
                    ->model(Merchant::class)
                    ->slideOver()
                    ->form([
                        TextInput::make('name')
                            ->label('Naam')
                            ->required()
                            ->maxLength(200),

                        TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->url()
                            ->maxLength(500)
                            ->nullable()
                            ->placeholder('https://logo.clearbit.com/example.com'),
                    ])
                    ->using(function (array $data): Merchant {
                        $data['category_id'] = $this->recordId;
                        $data['normalized_name'] = app(MerchantMapperService::class)->normalize($data['name']);

                        return Merchant::create($data);
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->slideOver()
                    ->form([
                        TextInput::make('name')
                            ->label('Naam')
                            ->required()
                            ->maxLength(200),

                        TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->url()
                            ->maxLength(500)
                            ->nullable()
                            ->placeholder('https://logo.clearbit.com/example.com'),
                    ]),

                DeleteAction::make()->icon('heroicon-o-trash'),
            ])
            ->defaultSort('name');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Categorie bewerken')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->url(EditCategory::getUrl(['record' => $this->recordId])),

            Action::make('back')
                ->label('Terug naar overzicht')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ListCategories::getUrl()),
        ];
    }
}
