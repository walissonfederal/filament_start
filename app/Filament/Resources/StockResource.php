<?php

namespace App\Filament\Resources;

use App\Enums\TypeTransactionStockEnum;
use App\Filament\Resources\StockResource\Pages;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class StockResource extends Resource
{
    protected static ?string $model           = Stock::class;
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Negócios';
    protected static ?string $label           = 'Estoque';
    protected static ?string $pluralLabel     = 'Estoque';
    protected static ?string $slug            = 'stocks';
    protected static ?int    $navigationSort  = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::fieldsForm());
    }

    public static function fieldsForm(): array
    {
        return [
            Select::make('product_id')
                ->label('Produto')
                ->searchable()
                ->getSearchResultsUsing(function (string $search) {
                    return Product::query()
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn($p) => [$p->id => "{$p->name} | Código: ({$p->code}) | Preço: {$p->price_main}"]);
                })
                ->getOptionLabelUsing(fn($value) => Product::find($value)?->name)
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    $product = Product::find($state);
                    if ($product && isset($product->price_main)) {
                        $set('price', $product->price_main);
                    }
                })
                ->createOptionForm(ProductResource::fieldsForm())
                ->createOptionUsing(function (array $data) {
                    $product = Product::create($data);
                    return $product->id;
                })
                ->helperText('Digite o nome ou o código do produto!'),

            TextInput::make('quantity')
                ->label('Quantidade')
                ->numeric()
                ->minValue(0)
                ->default(1)
                ->required(),

            TextInput::make('price')
                ->label('Preço')
                ->required()
                ->reactive()
                ->numeric()
                ->minValue(0)
                ->helperText('Formato: 1200.50 (Mil e duzentos reais e cinquenta centavos)'),

            Select::make('type_transaction')
                ->label('Tipo de Transação')
                ->options(fn() => collect(TypeTransactionStockEnum::cases())
                    ->mapWithKeys(fn($c) => [$c->value => $c->label() ?? $c->name])
                    ->toArray()
                )
                ->default(1)
                ->required(),

            DateTimePicker::make('entry_date')
                ->label('Data de entrada')
                ->required()
                ->default(now()),

            Textarea::make('observations')
                ->label('Observações')
                ->columnSpanFull()
                ->rows(3)
                ->maxLength(1000)
                ->placeholder('Observações da atualização (opcional)'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->orderBy("updated_at", "desc");
            })
            ->columns([
                TextColumn::make('product.name')
                    ->label('Nome do Produto'),

                TextColumn::make('quantity')
                    ->label('Quantidade'),

                BadgeColumn::make('type_transaction')
                    ->label('Tipo de Transação')
                    ->formatStateUsing(fn($state, $record) => TypeTransactionStockEnum::labelEnum($state))
                    ->color(fn($state, $record) => TypeTransactionStockEnum::colorEnum($state))
                    ->sortable(),

                TextColumn::make('entry_date')
                    ->label('Data de entrada')
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('d/m/Y H:i:s');
                    }),

                Tables\Columns\TextColumn::make('observations')
                    ->label('Observações')
                    ->description('Clique na observação para copiar', position: 'below')
                    ->formatStateUsing(fn($state) => $state ?? '-')
                    ->limit(30)
                    ->tooltip(fn($state) => is_string($state) && mb_strlen($state) > 30 ? $state : null)
                    ->copyable()
                    ->copyMessage('Observação copiada!')
                    ->copyMessageDuration(1500)
                    ->sortable(),
            ])
            ->filters([

                SelectFilter::make('product_id')
                    ->label("Nome do Produto")
                    ->relationship('product', 'name')
                    ->optionsLimit(5)
                    ->searchable(true)
                    ->preload(),

                SelectFilter::make('type_transaction')
                    ->label("Tipo de Transação")
                    ->options(fn() => collect(TypeTransactionStockEnum::cases())
                        ->mapWithKeys(fn($c) => [$c->value => $c->label() ?? $c->name])
                        ->toArray()
                    ),

                SelectFilter::make('observations')
                    ->label("Observações")
                    ->searchable(true)
                    ->preload(5)
                    ->options(
                        Stock::query()
                            ->select('observations')
                            ->distinct()
                            ->whereNotNull('observations')
                            ->orderBy('observations')
                            ->pluck('observations', 'observations')
                            ->toArray()
                    ),

                Filter::make('entry_date')
                    ->columnSpan(2)
                    ->form([
                        DatePicker::make('entry_date_start')
                            ->label('Data de entrada (Inicial)'),
                        DatePicker::make('entry_date_end')
                            ->label('Data de entrada (Final)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['entry_date_start'],
                                function (Builder $query, $date): Builder {
                                    return $query->whereDate('entry_date', '>=', $date);
                                }
                            )
                            ->when(
                                $data['entry_date_end'],
                                function (Builder $query, $date): Builder {
                                    return $query->whereDate('entry_date', '<=', $date);
                                }
                            );
                    })->columns(2),

            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit'   => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
