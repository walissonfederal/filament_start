<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\TypeTransactionStockEnum;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductsRelationManager extends RelationManager
{
    protected static string  $relationship = 'products';
    protected static ?string $title        = "Produtos";
    protected static ?string $label        = "Produto";
    protected static ?string $pluralLabel  = "Produtos";
    protected static ?string $icon         = "heroicon-o-shopping-bag";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([

                Tables\Columns\TextColumn::make('name')->label('Produto'),


                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Quantidade')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.price')
                    ->label('Preço no Pedido')
                    ->formatStateUsing(fn($state) => is_null($state) ?
                        '-' :
                        "R$ " . number_format($state, 2, ',', '.')
                    ),

                Tables\Columns\TextColumn::make('pivot.observations')
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
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->modalSubmitActionLabel("Adicionar")
                    ->modalHeading("Adicionar Produto")
                    ->label("Escolher produto")
                    ->form([
                        Forms\Components\Hidden::make("max_quantity")
                            ->disabled(),

                        Forms\Components\Select::make('recordId')
                            ->label('Produto')
                            ->options(function () {
                                return Product::whereHas('stock', function ($q) {
                                    $q->where("type_transaction", TypeTransactionStockEnum::ENTRADA)
                                        ->where('quantity', '>', 0);
                                })->get()
                                    ->mapWithKeys(function ($product) {

                                        $qtyEntry = $product->stock()
                                            ->where("type_transaction", TypeTransactionStockEnum::ENTRADA)
                                            ->sum("quantity") ?? 0;

                                        $qtyExit = $product->stock()
                                            ->where("type_transaction", TypeTransactionStockEnum::SAIDA)
                                            ->sum("quantity") ?? 0;

                                        $qty = $qtyEntry - $qtyExit;

                                        return [
                                            $product->id => "{$product->name} — {$qty} em estoque"
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $product = Product::withSum('stock', 'quantity')
                                    ->whereHas('stock', function ($q) {
                                        $q->where("type_transaction", TypeTransactionStockEnum::ENTRADA)
                                            ->where('quantity', '>', 0);
                                    })
                                    ->find($state);

                                $qtyEntry = $product->stock()
                                    ->where("type_transaction", TypeTransactionStockEnum::ENTRADA)
                                    ->sum("quantity") ?? 0;

                                $qtyExit = $product->stock()
                                    ->where("type_transaction", TypeTransactionStockEnum::SAIDA)
                                    ->sum("quantity") ?? 0;

                                $qty = $qtyEntry - $qtyExit;

                                $set('price', $product->price_main ?? null);
                                $set('max_quantity', $qty);
                                $set('quantity', 0);
                                $set('stock_display', $qty);
                            })
                            ->live()
                            ->hint("Produtos sem estoque não aparecem!")
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantidade')
                            ->numeric()
                            ->default(1)
                            ->reactive()
                            ->live()
                            ->hint("Quantidade é liberada mediante Estoque do Produto")
                            ->rules(function (Forms\Get $get) {
                                return [
                                    function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $max = $get('max_quantity') ?? 1;

                                        if ($max > 0 && $value < 1) {
                                            $fail("O valor de quantidade precisa ser maior que 0!");
                                        }

                                        if ($value > $max) {
                                            $fail("A quantidade solicitada ($value) excede o estoque disponível ($max).");
                                        }
                                    }
                                ];
                            })
                            ->required(),

                        Forms\Components\TextInput::make('price')
                            ->label('Preço')
                            ->reactive()
                            ->default(function (Forms\Get $get) {
                                return Product::find($get("recordId"))->price_main ?? null;
                            })
                            ->numeric()
                            ->required(),

                        Forms\Components\Textarea::make('observations')
                            ->label('Observações'),
                    ])
                    ->after(function (array $data = []) {
                        $order = $this->getOwnerRecord();

                        Stock::create([
                            "product_id"       => $data["recordId"],
                            "quantity"         => $data["quantity"],
                            'entry_date'       => now(),
                            'price'            => $data["price"],
                            'type_transaction' => TypeTransactionStockEnum::SAIDA,
                            "observations"     => $data["observations"],
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading("Modificar Produto Adicionado")
                    ->label("modificar")
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantidade')
                            ->disabled()
                            ->required()
                            ->dehydrated()
                            ->numeric(),
                        Forms\Components\TextInput::make('price')
                            ->label('Preço')
                            ->disabled()
                            ->required()
                            ->dehydrated()
                            ->numeric(),
                        Forms\Components\Textarea::make('observations')
                            ->disabled()
                            ->required()
                            ->dehydrated()
                            ->label('Observações'),
                    ]),

                Tables\Actions\DetachAction::make()
                    ->after(function (Model $record) {
                        $order = $this->getOwnerRecord();

                        Stock::create([
                            "product_id"       => $record->product_id,
                            "quantity"         => $record->quantity,
                            'entry_date'       => now(),
                            'price'            => $record->price,
                            'type_transaction' => TypeTransactionStockEnum::ENTRADA,
                            "observations"     => $record->observations,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
