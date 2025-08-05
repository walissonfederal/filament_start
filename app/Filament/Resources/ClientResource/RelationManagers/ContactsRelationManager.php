<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return "Contatos";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('prefix_international')
                         ->default("+55")
                         ->label("DDI")
                         ->maxLength(5),
                TextInput::make('prefix')
                         ->label('DDD')
                         ->required()
                         ->maxLength(5),
                TextInput::make('number')
                         ->label("Número")
                         ->required()
                         ->maxLength(50),
                TextInput::make('name')
                         ->label("Deixar recado para")
                         ->maxLength(100),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Número')
            ->modelLabel("Contato")
            ->pluralModelLabel("Contatos")
            ->columns([
                TextColumn::make('prefix_international'),
                TextColumn::make('prefix'),
                TextColumn::make('number'),
                TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
