<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancelar')
                ->label('Voltar')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl())
                ->outlined(),
            Actions\DeleteAction::make(),
        ];
    }

    /*protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }*/
}
