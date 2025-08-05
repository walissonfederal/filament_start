<?php

namespace App\Filament\Resources;

use App\Enums\StatusOrderEnum;
use App\Enums\TypeOrderEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\ServicesRelationManager;
use App\Models\Order;
use App\Rules\ReferenceRule;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model           = Order::class;
    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Negócios';
    protected static ?string $label           = 'pedido';
    protected static ?string $pluralLabel     = 'pedidos';
    protected static ?string $slug            = 'orders';
    protected static ?int    $navigationSort  = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label("Cliente")
                    ->relationship('client', 'name')
                    ->searchable()
                    ->createOptionForm(ClientResource::fieldsForm())
                    ->editOptionForm(ClientResource::fieldsForm())
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Situação')
                    ->default(StatusOrderEnum::CRIADA)
                    ->required()
                    ->options(function () {
                        return collect(StatusOrderEnum::cases())
                            ->mapWithKeys(fn($case) => [$case->value => $case->name])
                            ->toArray();
                    })
                    ->required(),

                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->default(TypeOrderEnum::MENSAL)
                    ->required()
                    ->options(function () {
                        return collect(TypeOrderEnum::cases())
                            ->mapWithKeys(fn($case) => [$case->value => $case->name])
                            ->toArray();
                    })
                    ->required(),

                TextInput::make('reference')
                    ->label('Referência')
                    ->default(now()->format('m/Y'))
                    ->required()
                    ->mask('99/9999')
                    ->placeholder('MM/AAAA')
                    ->rules([new ReferenceRule(),]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label('Nome do Cliente')
                    ->description('Clique no nome para copiar', position: 'below')
                    ->copyable()
                    ->copyMessage('Nome copiado!')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.document')
                    ->label('Documento do Cliente')
                    ->description('Clique no documento para copiar', position: 'below')
                    ->copyable()
                    ->copyMessage('Documento copiado!')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referência')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Situação')
                    ->formatStateUsing(fn($state, $record) => StatusOrderEnum::labelEnum($state))
                    ->color(fn($state, $record) => StatusOrderEnum::colorEnum($state))
                    ->sortable(),

                BadgeColumn::make('type')
                    ->formatStateUsing(fn($state, $record) => TypeOrderEnum::labelEnum($state))
                    ->color(fn($state, $record) => TypeOrderEnum::colorEnum($state))
                    ->label('Tipo')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([ /* ... */])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
            ServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
