<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategorieOddResource\Pages;
use App\Filament\Resources\CategorieOddResource\RelationManagers;
use App\Models\CategorieOdd;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategorieOddResource extends Resource
{
    protected static ?string $model = CategorieOdd::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Cibles des Objectifs';

    protected static ?string $navigationGroup = 'Objectifs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('category_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('intitule')
                    ->required(),
                Forms\Components\TextInput::make('name_en')
                    ->required(),
                Forms\Components\Select::make('id_odd')
                    ->relationship('odd', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('intitule')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_odd')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListCategorieOdds::route('/'),
            'create' => Pages\CreateCategorieOdd::route('/create'),
            'view' => Pages\ViewCategorieOdd::route('/{record}'),
            'edit' => Pages\EditCategorieOdd::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->role == 1;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
