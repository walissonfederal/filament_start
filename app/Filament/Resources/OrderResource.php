<?php

namespace App\Filament\Resources;

use App\Enums\CurrentMonthReferenceEnum;
use App\Enums\StatusOrderEnum;
use App\Enums\TypeOrderEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\ServicesRelationManager;
use App\Models\Client;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->schema(self::fieldsForm());
    }

    public static function fieldsForm(): array
    {
        return [
            Forms\Components\Grid::make()->schema([

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

            ])->columns(3),

            Forms\Components\Grid::make()->schema([

                Select::make('reference')
                    ->live()
                    ->label("Mês de Referência")
                    ->default(now()->format("m/Y"))
                    ->options(
                        collect(CurrentMonthReferenceEnum::cases())
                            ->mapWithKeys(fn(CurrentMonthReferenceEnum $cm) => [
                                $cm->numberYear() => $cm->labelYear()
                            ])->toArray()
                    )
                    ->required(),

                TextInput::make('total_price')
                    ->numeric()
                    ->default(0.00)
                    ->label('Total')
                    ->required()

            ])->columns(3),
        ];
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
                    ->searchable(),

                TextColumn::make('client.document')
                    ->label('Documento do Cliente')
                    ->description('Clique no documento para copiar', position: 'below')
                    ->copyable()
                    ->copyMessage('Documento copiado!')
                    ->copyMessageDuration(1500)
                    ->searchable(),

                TextColumn::make('reference')
                    ->label('Referência')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => "R$ " . number_format($state, 2, '.', ','))
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Situação')
                    ->formatStateUsing(fn($state) => StatusOrderEnum::labelEnum($state))
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
            ->filters([

                SelectFilter::make('client_id')
                    ->label('Nome do Cliente')
                    ->searchable()
                    ->optionsLimit(5)
                    ->getSearchResultsUsing(function (string $search) {
                        return Client::query()
                            ->where(function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('document', 'like', "%{$search}%");
                            })
                            ->limit(5)
                            ->pluck('name', 'id');
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        return Client::find($value)?->name;
                    }),

                SelectFilter::make('reference')
                    ->label("Mês de Referência")
                    ->options(
                        collect(CurrentMonthReferenceEnum::cases())
                            ->mapWithKeys(fn(CurrentMonthReferenceEnum $cm) => [
                                $cm->numberYear() => $cm->labelYear()
                            ])->toArray()
                    )
                    ->searchable()
                    ->preload(),

                Filter::make('Data Criação')
                    ->columnSpan(2)
                    ->form([
                        DatePicker::make('created_at_start')
                            ->label('Data Criação (Inicial)'),
                        DatePicker::make('created_at_end')
                            ->label('Data Criação (Final)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_at_start'],
                                function (Builder $query, $date): Builder {
                                    return $query->whereDate('created_at', '>=', $date);
                                }
                            )
                            ->when(
                                $data['created_at_end'],
                                function (Builder $query, $date): Builder {
                                    return $query->whereDate('created_at', '<=', $date);
                                }
                            );
                    })->columns(2),

            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filtrar...'),
            )
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
