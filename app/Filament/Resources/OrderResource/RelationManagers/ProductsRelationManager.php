<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
                        Forms\Components\Select::make('recordId')
                            ->label('Produto')
                            ->options(\App\Models\Product::pluck('name', 'id'))
                            ->searchable()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $product = Product::find($state);
                                $set("price", $product->price_main ?? null);
                            })
                            ->live()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantidade')
                            ->numeric()
                            ->default(1)
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
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading("Modificar Produto Adicionado")
                    ->label("modificar")
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantidade')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('price')
                            ->label('Preço')
                            ->required()
                            ->numeric(),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observações'),
                    ]),

                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
