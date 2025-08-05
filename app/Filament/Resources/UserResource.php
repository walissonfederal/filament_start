<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Role;
use App\Models\User;
use App\Services\Permissions\CanTrait;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    use CanTrait;

    protected static ?string $model           = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $label           = 'usuário';
    protected static ?string $pluralLabel     = 'usuários';
    protected static ?string $slug            = 'users';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    TextInput::make('name')
                        ->disabled(fn(?Model $record = null): bool => isset($record->system) && $record->system)
                        ->label('Nome')
                        ->required(),
                    TextInput::make('email')
                        ->disabled(fn(?Model $record = null): bool => $record->system ?? false)
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->required(),
                    TextInput::make('password')
                        ->disabled(fn(?Model $record = null): bool => isset($record->system) && $record->system)
                        ->password()
                        ->label("Senha")
                        ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                        ->dehydrated(fn(?string $state): bool => filled($state))
                        ->required(fn(string $operation): bool => $operation === 'create'),
                    Select::make('roles')
                        ->disabled(fn(?Model $record = null): bool => $record->system ?? false)
                        ->relationship('roles', 'name')
                        ->options(
                            function () {
                                return Role::pluck('name', 'id');
                            }
                        )
                        ->multiple()
                        ->preload()
                        ->label("Funções")
                        ->required(),

                    FileUpload::make('avatar')
                        ->disabled(fn(?Model $record = null): bool => isset($record->system) && $record->system)
                        ->directory('avatares')
                        ->columnSpanFull()
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios(
                            [
                                '1:1',
                            ]
                        )
                        ->openable()
                        ->previewable(true)
                        ->label('Foto do perfil'),
                ]
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(
                [
                    ImageColumn::make('avatar')
                        ->label('Avatar')
                        ->getStateUsing(fn ($record) => $record->getFilamentAvatarUrl())
                        ->height(50)
                        ->width(50)
                        ->rounded(),
                    TextColumn::make('roles.name')
                        ->label('Funções')
                        ->searchable()
                        ->listWithLineBreaks(),
                    TextColumn::make('name')
                        ->searchable(),
                    TextColumn::make('email')
                        ->searchable()
                        ->copyable()
                        ->copyMessage('Email copiado!')
                        ->copyMessageDuration(1500),
                    TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]
            )
            ->filters(
                [
                    //
                ]
            )
            ->actions(
                [
                    EditAction::make()
                        ->hidden(fn(?Model $record = null): bool => isset($record->system) && $record->system),
                    DeleteAction::make()
                        ->hidden(fn(?Model $record = null): bool => isset($record->system) && $record->system)
                        ->action(
                            function (DeleteAction $action) {
                                //
                            }
                        )
                        ->before(
                            function (?Model $record = null) {
                                $tenant = Filament::getTenant();
                                if (isset($record->id)) {
                                    $record->corporates()->detach([$tenant->id]);
                                }
                            }
                        ),
                    RestoreAction::make(),
                ]
            )
            ->bulkActions(
                [
                    BulkActionGroup::make(
                        [
                            RestoreBulkAction::make(),
                        ]
                    ),
                ]
            )
            ->defaultSort('name')
            ->defaultPaginationPageOption(50);
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
            'index'  => Pages\ListUser::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
            'view'   => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return
            parent::getEloquentQuery()
                ->withoutGlobalScopes(
                    [
                        SoftDeletingScope::class,
                    ]
                );
    }
}
