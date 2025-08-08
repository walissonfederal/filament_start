<?php

namespace App\Filament\Resources;

use App\Enums\TypePeopleEnum;
use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\ContactsRelationManager;
use App\Models\Client;
use App\Services\Permissions\CanTrait;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;

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
    protected static ?string  $recordTitleAttribute            = 'name';
    protected static int      $globalSearchResultsLimit        = 20;

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return Pages\SearchFichaRapidaResults::getUrl(['client_id' => $record->id]);
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        $query = [
            "tableFilters" => [
                "client_id" => [
                    "value" => $record->id
                ]
            ]
        ];

        return [
            Action::make('orders')
                ->hiddenLabel()
                ->icon("heroicon-o-truck")
                ->tooltip('Ir para os pedidos desse cliente!')
                ->url(OrderResource::getUrl('index', $query)),
            Action::make('transactions')
                ->hiddenLabel()
                ->icon("heroicon-o-currency-dollar")
                ->tooltip('Ir para faturas desse cliente!')
                ->url(TransactionResource::getUrl('index', $query)),
            Action::make('new_tab')
                ->hiddenLabel()
                ->icon('heroicon-o-plus-circle')
                ->tooltip('Abrir em nova aba')
                ->url(ClientResource::getUrl('edit', ["record" => $record->id]), shouldOpenInNewTab: true),
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Documento'           => $record->document,
            'Situação Cadastral'  => "-",
            'Situação Financeira' => "-",
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'document'
        ];
    }


    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with([
                'contacts',
                'addresses',
                'orders',
            ]);
    }

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
                ->maxLength(100)
                ->dehydrateStateUsing(fn($state) => preg_replace('/\D/', '', $state))
                ->afterStateHydrated(function (TextInput $component, $state, Get $get) {
                    $component->state(function () use ($state, $get) {
                        $clean = preg_replace('/\D/', '', $state);
                        if ($get('people_type') == 2 && strlen($clean) === 14) {
                            return preg_replace("/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/", "$1.$2.$3/$4-$5", $clean);
                        }
                        elseif (strlen($clean) === 11) {
                            return preg_replace("/^(\d{3})(\d{3})(\d{3})(\d{2})$/", "$1.$2.$3-$4", $clean);
                        }
                        return $state;
                    });
                }),
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
                    ->label("Nome")
                    ->sortable()
                    ->searchable(),
                TextColumn::make('document')
                    ->label("CPF/CNPJ")
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label("E-mail")
                    ->sortable()
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
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filtrar...'),
            )
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
            'index'                    => Pages\ListClients::route('/'),
            'create'                   => Pages\CreateClient::route('/create'),
            'edit'                     => Pages\EditClient::route('/{record}/edit'),
            'view'                     => Pages\ViewClient::route('/{record}'),
            'searchFichaRapidaResults' => Pages\SearchFichaRapidaResults::route('/search-ficha-rapida-results/{client_id}'),
        ];
    }
}
