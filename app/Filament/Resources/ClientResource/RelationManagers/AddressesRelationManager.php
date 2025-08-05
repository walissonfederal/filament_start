<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\ClientAddress;
use App\Services\BuscarViaCepService;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Component;

class AddressesRelationManager extends RelationManager
{
    protected static string  $relationship = 'addresses';
    protected static ?string $label        = 'Endereço';
    protected static ?string $pluralLabel  = 'Endereços';
    protected static ?string $title        = "Endereços";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('id'),
                TextInput::make('zipcode')
                    ->label("CEP")
                    ->suffixAction(
                        Action::make('viaCep')
                            ->label("Buscar CEP")
                            ->icon('heroicon-m-map-pin')
                            //->requiresConfirmation()
                            ->action(function (Set $set, $state, Get $get, Component $livewire) {
                                $data = BuscarViaCepService::getData((string)$state);

                                if (isset($data["cep"])) {
                                    $set('street', $data["logradouro"]);
                                    $set('complement', $data["complemento"]);
                                    $set('district', $data["bairro"]);
                                    $set('city', $data["localidade"]);
                                    $set('state', $data["uf"]);
                                }
                            })
                    )
                    ->hint("Busca de CEP")
                    ->afterStateUpdated(function (Set $set, Get $get, Component $livewire) {
                        $data = BuscarViaCepService::getData((string)$get("zipcode"));

                        if (isset($data["cep"])) {
                            $set('street', $data["logradouro"]);
                            $set('complement', $data["complemento"]);
                            $set('district', $data["bairro"]);
                            $set('city', $data["localidade"]);
                            $set('state', $data["uf"]);
                        }
                    })
                    ->mask(function (Get $get) {
                        return "99999-999";
                    })
                    ->debounce(1000)
                    ->required(),

                TextInput::make('street')
                    ->label('Logradouro')
                    ->required()
                    ->maxLength(255),

                TextInput::make('number')
                    ->label('Número')
                    ->maxLength(20),

                TextInput::make('complement')
                    ->label('Complemento')
                    ->maxLength(255),

                TextInput::make('district')
                    ->label('Bairro')
                    ->maxLength(255),

                TextInput::make('city')
                    ->label('Cidade')
                    ->required()
                    ->maxLength(255),

                TextInput::make('state')
                    ->label('UF')
                    ->required()
                    ->maxLength(2),

                Select::make('main')
                    ->label('Principal?')
                    ->options([
                        1 => "Sim",
                        0 => "Não",
                    ])
                    ->placeholder("É endereço Principal")
                    ->beforeStateDehydrated(function (Set $set, Get $get, $state) {
                        if ($state == 1) {
                            $clientId = static::getOwnerRecord()->id;
                            ClientAddress::where('client_id', $clientId)
                                ->update(['main' => 0]);
                        }
                    })
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street')
            ->columns([
                Tables\Columns\TextColumn::make('zipcode')->label('CEP'),
                Tables\Columns\TextColumn::make('street')->label('Logradouro'),
                Tables\Columns\TextColumn::make('number')->label('Número'),
                Tables\Columns\TextColumn::make('district')->label('Bairro'),
                Tables\Columns\TextColumn::make('city')->label('Cidade'),
                Tables\Columns\TextColumn::make('state')->label('UF'),
                Tables\Columns\TextColumn::make('main')
                    ->label('Principal?')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'secondary')
                    ->formatStateUsing(fn($state) => $state ? 'Sim' : 'Não'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
