<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                    ->relationship('client', 'name')
                    ->searchable()
                    ->createOptionForm(ClientResource::fieldsForm())
                    ->editOptionForm(ClientResource::fieldsForm())
                    ->required(),

                Forms\Components\Select::make('status')
                    ->required(),

                Forms\Components\Select::make('type')
                    ->required(),

                TextInput::make('reference')
                    ->label('Referência')
                    ->required()
                    ->mask('99/9999')
                    ->placeholder('MM/AAAA')
                    ->rule(function (string $attribute, $value, Closure $fail) {
                        // Valida formato
                        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $value)) {
                            $fail('O formato deve ser MM/AAAA.');
                            return;
                        }

                        // Extrai mês e ano
                        [
                            $mes,
                            $ano
                        ] = explode('/', $value);

                        $anoAtual = (int)date('Y');

                        // Valida ano
                        if ((int)$ano < $anoAtual) {
                            $fail("O ano deve ser igual ou maior que {$anoAtual}.");
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label("Nome do Cliente")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
