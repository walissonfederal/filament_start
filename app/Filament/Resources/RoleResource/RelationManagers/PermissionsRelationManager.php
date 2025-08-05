<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use App\Models\Permission;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return "Permissões";
    }

    public function form(Form $form): Form
    {
        $model = $this->getOwnerRecord();
        $groups = Permission::whereNotIn("id", $model->permissions->pluck('id'))
            ->select([
                "id",
                "crud",
                "description"
            ])
            ->get()
            ->groupBy("crud");

        $list = [];
        foreach ($groups as $group => $permissions) {
            foreach ($permissions as $permission) {
                $list["Grupo de Permissões para: (" . $group . ")"][$permission->id] =
                    "--- " . $permission->description;
            }
        }

        $tenant = Filament::getTenant();

        return $form
            ->schema([
                Select::make('permissions')
                    ->label('Permissões')
                    ->options($list)
                    /*->before(function (Set $set, Get $get) use ($tenant) {
                        $array = [];

                        foreach ($get("permissions") as $id) {
                            $array[$id] = [
                                "corporate_id" => $tenant->id
                            ];
                        }

                        $set("permissions", $array);

                    })*/
                    ->searchable()
                    ->multiple()
                    ->columnSpan(4)
                    ->required()
                    ->reactive(),
            ]);
    }

    public function table(Table $table): Table
    {
        $model = $this->getOwnerRecord();

        $tenant = Filament::getTenant();

        return $table
            ->recordTitleAttribute('Permissões')
            ->columns([
                TextColumn::make('description')
                    ->label("Selecionar para Ações"),
            ])
            ->headerActions([
                Action::make('addAllPermissions')
                    ->hidden(fn(): bool => isset($model->system) && $model->system)
                    ->label('Adicionar Tudo')
                    ->mountUsing(function () use ($tenant) {
                        $model = $this->getOwnerRecord();
                        if (isset($model->id)) {
                            $model->permissions()->detach();
                            $allPermissions = Permission::all()->pluck('id')->toArray();
                            $model->permissions()->sync($allPermissions);
                            Notification::make()
                                ->success()
                                ->title('Adicionados com sucesso!')
                                ->seconds(5)
                                ->send();
                        }
                    }),
                Action::make('removeAllPermissions')
                    ->hidden(fn(): bool => isset($model->system) && $model->system)
                    ->color('danger')
                    ->label('Remover Tudo')
                    ->mountUsing(function () use ($tenant) {
                        $model = $this->getOwnerRecord();
                        if (isset($model->id)) {
                            $model->permissions()
                                ->detach();

                            Notification::make()
                                ->success()
                                ->title('Removidos com sucesso!')
                                ->seconds(5)
                                ->send();
                        }
                    }),
                CreateAction::make('permissions')
                    ->hidden(fn(): bool => isset($model->system) && $model->system)
                    ->modelLabel("(Escolher Permissões)")
                    ->modalSubmitActionLabel("Adicionar")
                    ->label('Escolher Permissões')
                    ->action(function (CreateAction $action) {
                        $action->process(static fn(Model $record) => $record->save());
                        $action->success();
                    })
                    ->using(function ($data) use ($tenant) {
                        $model = $this->getOwnerRecord();
                        if (isset($model->id)) {
                            $model->permissions()
                                ->syncWithoutDetaching($data['permissions'] ?? []);
                        }
                    }),
            ])
            ->actions([
                DeleteAction::make()
                    ->hidden(fn(): bool => isset($model->system) && $model->system),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->hidden(fn(): bool => isset($model->system) && $model->system),
                ]),
            ]);
    }
}
