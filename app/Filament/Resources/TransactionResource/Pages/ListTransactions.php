<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Widgets\FinancialTotalsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()/*
                                ->after(function () {
                                    return redirect("/admin/transactions");
                                })*/,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialTotalsWidget::class,
        ];
    }

    /*protected function getFooterWidgets(): array
    {
        return [
            FinancialTotalsWidget::class,
        ];
    }*/
}
