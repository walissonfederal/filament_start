<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers\PermissionsRelationManager;
use App\Models\Role;
use App\Services\Permissions\CanTrait;
use App\Services\Permissions\SyncPermissionsService;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoleResource extends Resource
{
    use CanTrait;

    protected static ?string $model           = Role::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $slug            = 'roles';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $label           = 'função';
    protected static ?string $pluralLabel     = 'funções';

    public static function canEdit(Model $record): bool
    {
        SyncPermissionsService::handle();
        return parent::canEdit($record);
    }

    public static function form(Form $form): Form
    {
        $tenant = Filament::getTenant();

        return $form
            ->schema([
                TextInput::make('name')
                    ->disabled(fn(?Model $record = null): bool => isset($record->system) && $record->system)
                    ->autofocus()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->afterStateUpdated(function ($state, $set, $livewire) use ($tenant) {
                        $isRecord = $livewire->record->id ?? null;
                        $role     = Role::where("name", $state)->first();

                        if (!isset($isRecord) && isset($role->id)) {
                            $role->corporates()->syncWithoutDetaching([$tenant->id]);
                            Notification::make()
                                ->success()
                                ->title('Legal')
                                ->body("Função agora faz parte desta corporação!")
                                ->persistent()
                                ->seconds(5)
                                ->send();

                            redirect("/admin/roles");

                            return;
                        }

                        $nameRole = null;

                        if (strpos($state, "Administrator") !== false) {
                            $nameRole = "Administrator";
                        }

                        if (strpos($state, "User") !== false) {
                            $nameRole = "User";
                        }

                        if ($nameRole) {
                            Notification::make()
                                ->danger()
                                ->title('Não Autorizado!')
                                ->body("O grupo {$nameRole} não pode ser modificado!")
                                ->persistent()
                                ->seconds(5)
                                ->send();

                            $set('name', $nameRole);
                        }
                    }),
                TextInput::make('position')
                    ->disabled(fn(?Model $record = null): bool => isset($record->system) && $record->system)
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label("Nome")
                    ->searchable(),
                TextColumn::make('position')
                    ->label("Ordem")
                    ->numeric()
                    ->sortable(),
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
                EditAction::make()
                    ->hidden(fn(?Model $record = null): bool => isset($record->system) && $record->system),
                DeleteAction::make()
                    ->hidden(fn(?Model $record = null): bool => isset($record->system) && $record->system)
                    ->label("Lixeira")
                    ->action(function (DeleteAction $action) {
                        //
                    })
                    ->before(function (?Model $record = null) {
                        $tenant = Filament::getTenant();
                        if (isset($record->id)) {
                            $record->corporates()->detach([$tenant->id]);
                        }
                    }),
            ])
            ->defaultSort("id")
            ->bulkActions([
                //
            ])
            ->defaultPaginationPageOption(50);
    }

    public static function getRelations(): array
    {
        return [
            PermissionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRole::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
            'view'   => Pages\ViewRole::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
