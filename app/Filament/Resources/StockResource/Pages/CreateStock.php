<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Após salvar, vai para a listagem do estoque
        return StockResource::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Sucesso!')
            ->body('Seu estoque foi atualizado com sucesso!');
    }
}
