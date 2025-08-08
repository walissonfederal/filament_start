<?php

namespace App\Filament\Resources;

use App\Enums\CurrentMonthReferenceEnum;
use App\Enums\NecessaryTransactionEnum;
use App\Enums\StatusOrderEnum;
use App\Enums\TypeTransactionEnum;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Client;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Permissions\CanTrait;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Guava\FilamentKnowledgeBase\Contracts\HasKnowledgeBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;

class TransactionResource extends Resource implements HasKnowledgeBase
{
    use CanTrait;

    protected static ?string $model           = Transaction::class;
    protected static ?string $navigationIcon  = 'heroicon-o-currency-dollar';
    protected static ?string $label           = 'Entrada/Saída';
    protected static ?string $pluralLabel     = 'Entrada/Saída';
    protected static ?string $navigationLabel = 'Entrada/Saída';
    protected static ?string $navigationGroup = 'Financeiro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Grid 1 ---
                Grid::make(3)
                    ->schema([
                        Select::make('type')
                            ->live()
                            ->default(1)
                            ->label("Tipo de Transação?")
                            ->options(
                                collect(TypeTransactionEnum::cases())
                                    ->mapWithKeys(fn(TypeTransactionEnum $type) => [
                                        $type->value => $type->label()
                                    ])->toArray()
                            )
                            ->required(),
                        Select::make('order_id')
                            ->label("Pedido Vinculado")
                            ->visible(fn(Get $get) => $get("type") == 1)
                            ->searchable()
                            ->live()
                            ->getSearchResultsUsing(function (string $search) {
                                return Order::query()
                                    ->whereNot("status", StatusOrderEnum::NEGADO)
                                    ->join('clients', 'orders.client_id', '=', 'clients.id')
                                    ->where('clients.name', 'like', "%{$search}%")
                                    ->orWhere('orders.id', 'like', "%{$search}%")
                                    ->selectRaw('orders.id, CONCAT("Pedido #", orders.id, " - ", clients.name) as label')
                                    ->limit(50)
                                    ->pluck('label', 'orders.id');
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $order = Order::with('client')->find($value);
                                return $order ? "Pedido #{$order->id} - {$order->client->name}" : null;
                            })
                            ->hintColor("success")
                            ->hintIcon("heroicon-o-magnifying-glass")
                            ->hint("Pesquise por Cliente ou Pedido")
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $order = Order::find($state);
                                if ($order && isset($order->total_price)) {
                                    $set('value', $order->total_price);
                                    $set('current_month', $order->reference);
                                    $set('name', "Pedido Número: {$order->id}, para cliente: {$order->client->name}");
                                }
                            }),

                        Select::make('necessary')
                            ->live()
                            ->reactive()
                            ->label("Necessidade?")
                            ->visible(fn(Get $get) => $get("type") == 2)
                            ->options(
                                collect(NecessaryTransactionEnum::cases())
                                    ->mapWithKeys(fn(NecessaryTransactionEnum $necessary) => [
                                        $necessary->value => $necessary->label()
                                    ])->toArray()
                            )
                            ->required(fn(Get $get) => $get("type") == 2)
                            ->default(1),

                        Select::make('current_month')
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
                    ]),

                // Grid 2 ---
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Descrição da Transação')
                            ->datalist(
                                \App\Models\Transaction::query()
                                    ->select('name')
                                    ->distinct()
                                    ->limit(10)
                                    ->orderBy('name')
                                    ->pluck('name')
                                    ->filter()
                                    ->values()
                                    ->toArray()
                            )
                            ->required()
                            ->maxLength(191),

                        TextInput::make('value')
                            ->required()
                            ->reactive()
                            ->label('Valor ( Ex: 1000.00 Mil Reais)')
                            ->numeric(),
                    ]),

                // Grid 3 ---
                Grid::make(4)->schema([

                    DatePicker::make('date_initial_monthly')
                        ->label("Data de Início da Recorrência"),
                    DatePicker::make('date_finish_monthly')
                        ->label("Data de Fim da Recorrência"),
                    TextInput::make('due_day')
                        ->string()
                        ->label("Dia do Vencimento")
                        ->default("15")
                        ->length(2)
                        ->placeholder('10'),

                    TextInput::make('payment_day')
                        ->string()
                        ->label("Dia do Pagamento")
                        ->length(2)
                        ->placeholder('10'),
                ]),

                Fieldset::make('Anexo')
                    ->schema([
                        FileUpload::make('recipient')
                            ->label("Anexo do Recibo")
                            ->reactive()
                            ->live()
                            ->columnSpanFull()
                            ->disk('public')
                            ->directory('recibos')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required(fn(Get $get) => $get("type") == 2),
                        PdfViewerField::make('recipient_view')
                            ->visible(fn(Model $record) => isset($record->recipient))
                            ->dehydrated(false)
                            ->reactive()
                            ->live()
                            ->columnSpanFull()
                            ->fileUrl(function (Get $get) {
                                $path = array_values($get("recipient"))[0] ?? null;
                                return Storage::url($path);
                            })
                            ->label('Visualizar Recibo')
                            ->minHeight('40svh')
                    ]),

                // Grid 4 ---
                Grid::make(3)->schema([
                    Toggle::make('monthly')
                        ->label("Recorrente?"),
                    Toggle::make('paid')
                        ->label("Pago?"),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('PDF Viewer')
                    ->description('Visualize o recibo no formato PDF.')
                    ->collapsible()
                    ->schema([
                        PdfViewerEntry::make('file')
                            ->label('Visualizar Recibo')
                            ->minHeight('40svh')
                            ->fileUrl(fn($record) => $record->recipient ? Storage::url($record->recipient) : null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::fieldsColumns())
            ->filters(self::fieldsFilter(), layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filtrar...'),
            )
            ->defaultSort("current_month", "DESC")
            ->actions(self::fieldsActions())
            ->bulkActions(self::fieldsBulkActions());
    }

    public static function fieldsColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label("Descrição")
                ->limit(15)
                ->tooltip(fn($state) => is_string($state) && mb_strlen($state) > 15 ? $state : null)
                ->searchable(),

            TextColumn::make('order_id')
                ->label("Pedido Vinculado")
                ->formatStateUsing(function (string $state = null): string {
                    if ($state) {
                        $order  = Order::find($state) ?? null;
                        $name   = $order->client->name ?? null;
                        $string = "Pedido: {$order->id} | Cliente: {$name}";
                        return $string ?? "-";
                    }

                    return "-";
                })
                ->limit(15)
                ->tooltip(function (string $state = null): string {
                    if ($state) {
                        $order  = Order::find($state) ?? null;
                        $name   = $order->client->name ?? null;
                        $string = "Pedido: {$order->id} | Cliente: {$name}";
                        return $string ?? "-";
                    }

                    return "Nenhum dado a ser mostrado!";
                })
                ->copyable()
                ->copyMessage('Clique para copiar!')
                ->copyMessageDuration(1500),

            TextColumn::make('type')
                ->label("Tipo")
                ->badge()
                ->color(function (string $state): string {
                    $cor = TypeTransactionEnum::from($state)->color();
                    return $cor;
                })
                ->formatStateUsing(function (string $state): string {
                    $preLabel = TypeTransactionEnum::from($state)->preLabel();
                    return $preLabel;
                }),
            TextInputColumn::make('value')
                ->label('Valor')
                ->afterStateUpdated(function (Set $set, string $state) {
                    if ($state) {
                        redirect("/admin/transactions");
                    }
                })
                ->extraAttributes([
                    'style' => 'width: 150px;',
                ]),
            IconColumn::make('monthly')
                ->label("Recorrente?")
                ->boolean(),
            ToggleColumn::make('paid')
                ->label("Pago?"),
            TextColumn::make('due_day')
                ->label("Vencimento")
                ->date()
                ->formatStateUsing(function (string $state): string {
                    if ($state) {
                        $value = now()->format("$state/m/Y");
                    }
                    return $value ?? "";
                }),
            TextColumn::make('created_at')
                ->label("Criado")
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->label("Atualizado")
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function fieldsFilter(array $hidden = []): array
    {
        return [

            SelectFilter::make('client_id')
                ->label('Nome do Cliente')
                ->hidden(in_array("client_id", $hidden))
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
                })
                ->query(function (Builder $query, array $data) {
                    $query->when(
                        $data['value'] ?? null,
                        function (Builder $whenQuery, $client_id): Builder {
                            return $whenQuery->whereHas('order', function ($orderQuery) use ($client_id) {
                                $orderQuery->where('client_id', $client_id);
                            });
                        }
                    );

                    return $query;
                }),

            SelectFilter::make('monthly')
                ->label("Recorrente?")
                ->options([
                    0 => "Não",
                    1 => "Sim",
                ]),
            SelectFilter::make('necessary')
                ->label("Necessário")
                ->options(collect(NecessaryTransactionEnum::cases())
                    ->mapWithKeys(fn(NecessaryTransactionEnum $necessary) => [
                        $necessary->value => $necessary->label()
                    ])->toArray()
                ),
            SelectFilter::make('type')
                ->label("Tipo")
                ->options(collect(TypeTransactionEnum::cases())
                    ->mapWithKeys(fn(TypeTransactionEnum $type) => [
                        $type->value => $type->label()
                    ])->toArray()
                ),
            SelectFilter::make("paid")
                //->default(0)
                ->placeholder("Já foi pago?")
                ->options([
                    1 => "Pago",
                    0 => "Não Pago",
                ])
                ->label("Pago?"),
            SelectFilter::make('name')
                ->label("Descrição")
                ->preload()
                ->searchable()
                ->optionsLimit(5)
                ->options(
                    Transaction::distinct()
                        ->pluck("name", "name")
                        ->toArray()
                ),
            SelectFilter::make('order_id')
                ->label('Ped. Vinculado. (ID Pedido / Nome.Cliente)')
                ->searchable()
                ->getSearchResultsUsing(function (string $search) {
                    return Order::with('client')
                        ->whereHas('client', fn($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhere('id', 'like', "%{$search}%")
                        ->limit(10)
                        ->get()
                        ->mapWithKeys(function ($order) {
                            return [$order->id => "{$order->id} - {$order->client?->name}"];
                        })
                        ->toArray();
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    $order = Order::with('client')->find($value);
                    return $order ? "{$order->id} - {$order->client?->name}" : null;
                }),

        ];
    }

    public static function fieldsActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()
                    ->after(function () {
                        return redirect("/admin/transactions");
                    }),
                DeleteAction::make()
                    ->after(function () {
                        return redirect("/admin/transactions");
                    }),
            ])
        ];
    }

    public static function fieldsBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->after(function () {
                        return redirect("/admin/transactions");
                    }),
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit'   => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getDocumentation(): array|string
    {
        return [
            'transactions.crud',
        ];
    }
}
