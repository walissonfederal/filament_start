<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model           = Service::class;
    protected static ?string $navigationIcon  = 'heroicon-o-wrench';
    protected static ?string $navigationGroup = 'Negócios';
    protected static ?string $label           = 'serviço';
    protected static ?string $pluralLabel     = 'serviços';
    protected static ?string $slug            = 'services';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label("Nome")
                    ->required()
                    ->maxLength(191),

                Forms\Components\TextInput::make('price_main')
                    ->label('Preço Principal')
                    ->numeric()
                    ->required(),

                FileUpload::make('picture')
                    ->label("Imagem Principal")
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
            ]);
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
                /*
                                    ->rounded()*/,

                Tables\Columns\TextColumn::make('name')
                    ->label("Nome")
                    ->searchable(),

                Tables\Columns\TextColumn::make('price_main')
                    ->label('Preço Principal')
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
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
