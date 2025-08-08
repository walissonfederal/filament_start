<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\TransactionResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\Transaction;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class SearchFichaRapidaResults extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource  = ClientResource::class;
    protected static string $view      = 'filament.resources.client-resource.pages.search-ficha-rapida-results';
    public Client           $client;
    public string           $activeTab = 'orders';
    public string           $tabName;

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return new HtmlString("Ficha Rápida: {$this->client->name}");
    }

    public function mount(int $client_id): void
    {
        $this->client = Client::findOrFail($client_id);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        if ($this->activeTab === 'orders') {
            $this->tabName = "Pedidos";
            return $table
                ->query(
                    Order::query()->where('client_id', $this->client->id)
                )
                ->columns(OrderResource::fieldsColumns())
                ->filters(
                    OrderResource::fieldsFilter(hidden: ["client_id"]),
                    layout: FiltersLayout::AboveContentCollapsible
                )
                ->filtersFormColumns(3)
                ->filtersTriggerAction(
                    fn(Action $action) => $action
                        ->button()
                        ->label('Filtrar...'),
                )
                ->actions(OrderResource::fieldsActions())
                ->bulkActions(OrderResource::fieldsBulkActions())
                ->paginated([
                    10,
                    25,
                    50
                ]);
        }

        if ($this->activeTab === 'transactions') {
            $this->tabName = "Faturas";
            return $table
                ->query(
                    Transaction::query()->whereHas("order", function ($query) {
                        $query->where('client_id', $this->client->id);
                    })
                )
                ->columns(TransactionResource::fieldsColumns())
                ->filters(
                    TransactionResource::fieldsFilter(hidden: []),
                    layout: FiltersLayout::AboveContentCollapsible
                )
                ->filtersFormColumns(3)
                ->filtersTriggerAction(
                    fn(Action $action) => $action
                        ->button()
                        ->label('Filtrar...'),
                )
                ->actions(TransactionResource::fieldsActions())
                ->bulkActions(TransactionResource::fieldsBulkActions())
                ->paginated([
                    10,
                    25,
                    50
                ]);
        }

        // Caso nenhuma tab conhecida
        // return $table->query(Order::query()->whereRaw('0=1'));

        return $table;
    }
}
