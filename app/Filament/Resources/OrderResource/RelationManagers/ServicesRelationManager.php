<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServicesRelationManager extends RelationManager
{
    protected static string  $relationship = 'services';
    protected static ?string $title        = "Serviços";
    protected static ?string $label        = "Serviço";
    protected static ?string $pluralLabel  = "Serviços";
    protected static ?string $icon         = "heroicon-o-wrench";

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
                Tables\Columns\TextColumn::make('name')->label('Serviço'),
                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Quantidade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.price')
                    ->label('Preço')
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
                    ->modalHeading("Adicionar Serviço")
                    ->label("Escolher serviço")
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('Serviço')
                            ->options(\App\Models\Service::pluck('name', 'id'))
                            ->searchable()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $service = Service::find($state);
                                $set("price", $service->price_main ?? null);
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
                                return Service::find($get("recordId"))->price_main ?? null;
                            })
                            ->numeric()
                            ->required(),

                        Forms\Components\Textarea::make('observations')
                            ->label('Observações'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading("Modificar Serviço Adicionado")
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
