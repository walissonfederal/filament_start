<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model           = Product::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Negócios';
    protected static ?string $label           = 'produto';
    protected static ?string $pluralLabel     = 'produtos';
    protected static ?string $slug            = 'products';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::fieldsForm());
    }

    public static function fieldsForm(?Product $product = null): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label("Nome")
                ->default($product->name ?? null)
                ->required()
                ->maxLength(191),

            Forms\Components\TextInput::make('code')
                ->label("Código de Identificação")
                ->default($product->code ?? null)
                ->required()
                ->maxLength(191),

            Forms\Components\TextInput::make('price_main')
                ->label('Preço Principal')
                ->default($product->price_main ?? null)
                ->numeric()
                ->required(),

            FileUpload::make('picture')
                ->label("Imagem Principal")
                ->default($product->picture ?? null)
                ->directory('products/pictures')
                ->columnSpanFull()
                ->image()
                ->imageEditor()
                ->imageEditorAspectRatios(
                    [
                        '1:1',
                    ]
                )
                ->openable()
                ->previewable(true)
                ->label('Foto do perfil'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('picture')
                    ->label('IMG')
                    ->disk('public')
                    ->height(50)
                    ->width(50)
                /*->rounded()*/,

                Tables\Columns\TextColumn::make('name')
                    ->label("Nome")
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label("Código de Identificação")
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price_main')
                    ->label('Preço Principal')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => is_null($state) ?
                        '-' :
                        "R$ " . number_format($state, 2, ',', '.')
                    ),

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
                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
