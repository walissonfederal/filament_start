<?php

namespace App\Filament\Resources;

use App\Enums\TypeTransactionStockEnum;
use App\Filament\Resources\StockResource\Pages;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class StockResource extends Resource
{
    protected static ?string $model           = Stock::class;
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Negócios';
    protected static ?string $label           = 'Estoque';
    protected static ?string $pluralLabel     = 'Estoque';
    protected static ?string $slug            = 'stock';
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

                TextColumn::make('entry_date')
                    ->label('Data de entrada')
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('d/m/Y H:i:s');
                    }),
            ])
            ->filters([
                //
            ])
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
