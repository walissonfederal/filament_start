<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                        ->action(function () {
                            Notification::make()
                                        ->success()
                                        ->title('Usuário removido desta corporação!')
                                        ->seconds(5)
                                        ->send();

                            redirect("/admin/users");
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
