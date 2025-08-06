<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderProduct;
use App\Models\OrderService;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ServicesRelationManager extends RelationManager
{
    protected static string  $relationship = 'services';
    protected static ?string $title        = "Serviços";
    protected static ?string $label        = "Serviço";
    protected static ?string $pluralLabel  = "Serviços";
    protected static ?string $icon         = "heroicon-o-wrench";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantidade')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->label('Preço')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('observations')
                    ->label('Observações')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Serviço')
                    ->description('Clique para copiar', position: 'below')
                    ->formatStateUsing(fn($state) => $state ?? '-')
                    ->limit(15)
                    ->tooltip(fn($state) => is_string($state) && mb_strlen($state) > 15 ? $state : null)
                    ->copyable()
                    ->copyMessage('Nome do serviço copiado!')
                    ->copyMessageDuration(1500)
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Quantidade'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço Unitário')
                    ->formatStateUsing(fn($state) => is_null($state) ?
                        '-' :
                        "R$ " . number_format($state, 2, ',', '.')
                    ),

                Tables\Columns\TextColumn::make('pivot.price')
                    ->label('Total Produto')
                    ->formatStateUsing(function (Model $record, $state) {
                        $total = $record->price * $record->quantity;
                        return is_null($total) ?
                            '-' :
                            "R$ " . number_format($total, 2, ',', '.');
                    }),

                Tables\Columns\TextColumn::make('pivot.observations')
                    ->label('Observações')
                    ->description('Clique na observação para copiar', position: 'below')
                    ->formatStateUsing(fn($state) => $state ?? '-')
                    ->limit(30)
                    ->tooltip(fn($state) => is_string($state) && mb_strlen($state) > 30 ? $state : null)
                    ->copyable()
                    ->copyMessage('Observação copiada!')
                    ->copyMessageDuration(1500),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->modalSubmitActionLabel("Adicionar")
                    ->modalHeading("Adicionar Serviço")
                    ->label("Escolher serviço")
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('Serviço')
                            ->options(\App\Models\Service::pluck('name', 'id'))
                            ->searchable()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $service = Service::find($state);
                                $set("price", $service->price_main ?? null);
                            })
                            ->live()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantidade')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\TextInput::make('price')
                            ->label('Preço')
                            ->reactive()
                            ->default(function (Forms\Get $get) {
                                return Service::find($get("recordId"))->price_main ?? null;
                            })
                            ->numeric()
                            ->required(),

                        Forms\Components\Textarea::make('observations')
                            ->label('Observações'),
                    ])->after(function (array $data = []) {
                        $order    = $this->getOwnerRecord();
                        $newPrice = round($order->total_price + ($data["price"] * $data["quantity"]), 2);
                        $order->update(["total_price" => $newPrice]);
                        $this->dispatch('orderTotalUpdated', data: [
                            'client_id'   => $order->client_id,
                            'reference'   => $order->reference,
                            'type'        => $order->type,
                            'status'      => $order->status,
                            'total_price' => $newPrice,
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\DetachAction::make()
                    ->action(function (Model $record, array $data = []) {

                        $ordersServices = OrderService::where("order_id", $record->order_id)
                            ->where("service_id", $record->pivot->service_id)
                            ->get();

                        $totalPrice = 0;
                        foreach ($ordersServices as $orderService) {
                            $totalPrice += $orderService->price * $orderService->quantity;
                            $orderService->delete();
                        }

                        $order    = $this->getOwnerRecord();
                        $newPrice = round($order->total_price - $totalPrice, 2);
                        $order->update(["total_price" => $newPrice]);
                        $this->dispatch('orderTotalUpdated', data: [
                            'client_id'   => $order->client_id,
                            'reference'   => $order->reference,
                            'type'        => $order->type,
                            'status'      => $order->status,
                            'total_price' => $newPrice,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
