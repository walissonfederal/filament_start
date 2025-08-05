<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                                ->action(function () {
                                    Notification::make()
                                                ->success()
                                                ->title('Função removida desta corporação!')
                                                ->seconds(5)
                                                ->send();

                                    redirect("/admin/roles");
                                })
                                ->before(function (Model $record) {
                                    $tenant = Filament::getTenant();
                                    if (isset($record->id)) {
                                        $record->corporates()->detach([$tenant->id]);
                                    }
                                }),
        ];
    }
}
