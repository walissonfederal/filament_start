<?php

namespace App\Filament\Resources;

use App\Enums\TypePeopleEnum;
use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\ContactsRelationManager;
use App\Models\Client;
use App\Services\Permissions\CanTrait;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClientResource extends Resource
{
    use CanTrait;

    protected static ?string  $model                           = Client::class;
    protected static ?string  $navigationIcon                  = 'heroicon-o-user';
    protected static ?string  $navigationGroup                 = 'Negócios';
    public static null|string $tenantOwnershipRelationshipName = "corporates";
    protected static ?string  $tenantRelationshipName          = "clients";
    protected static ?string  $label                           = "Cliente";
    protected static ?string  $pluralLabel                     = "Clientes";
    protected static ?string  $navigationLabel                 = "Clientes";
    protected static ?string  $slug                            = "clients";

    public static function fieldsForm(): array
    {
        return [
            TextInput::make('name')
                ->autofocus()
                ->label("Nome Completo")
                ->required()
                ->maxLength(100),
            Select::make('people_type')
                ->label("Física/Jurídica")
                ->live()
                ->default(1)
                ->options(
                    collect(TypePeopleEnum::cases())
                        ->mapWithKeys(fn(TypePeopleEnum $type) => [
                            $type->value => $type->label()
                        ])->toArray()
                )
                ->required(),
            TextInput::make('document')
                ->unique(ignoreRecord: true)
                ->label("CPF/CNPJ")
                ->live()
                ->placeholder(function (Get $get) {
                    $people_type = $get("people_type");
                    return $people_type == 2 ? "99.999.999/9999-99" : "999.999.999-99";
                })
                ->mask(function (Get $get) {
                    $people_type = $get("people_type");
                    return $people_type == 2 ? "99.999.999/9999-99" : "999.999.999-99";
                })
                ->maxLength(50)
                ->required()
                ->maxLength(100),
            TextInput::make('email')
                ->label("E-mail")
                ->unique(ignoreRecord: true)
                ->email()
                ->required()
                ->maxLength(255),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::fieldsForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('document')
                    ->label("CPF/CNPJ")
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            ContactsRelationManager::class,
            AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit'   => Pages\EditClient::route('/{record}/edit'),
            'view'   => Pages\ViewClient::route('/{record}'),
        ];
    }
}
