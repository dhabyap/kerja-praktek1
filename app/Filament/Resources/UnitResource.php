<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Unit;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appartement;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Column;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UnitResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UnitResource\Pages\EditUnit;
use App\Filament\Resources\UnitResource\Pages\ListUnits;
use App\Filament\Resources\UnitResource\Pages\CreateUnit;
use App\Filament\Resources\UnitResource\RelationManagers;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Unit';
    protected static ?string $pluralModelLabel = 'Units';
    protected static ?string $navigationGroup = 'Master Data';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->can('admin-local') || auth()->user()->can('admin-global')) {
            return $query->where('appartement_id', auth()->user()->appartement_id);
        }

        return $query;
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        $user = Filament::auth()->user();

        return $form->schema([
            Select::make('appartement_id')
                ->label('Apartment')
                ->options(function () use ($user) {
                    if ($user->level_id === 1) {
                        return Appartement::all()->pluck('nama', 'id');
                    } else {
                        return Appartement::where('id', $user->appartement_id)->pluck('nama', 'id');
                    }
                })
                ->required(),
            TextInput::make('nama')->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->state(
                        static function (Column $column, $record, $rowLoop) {
                            return $rowLoop->iteration;
                        }
                    ),
                TextColumn::make('nama')->searchable(),
                TextColumn::make('appartement.nama')
                    ->label('Apartment')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('id');
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'view' => Pages\ViewUnit::route('/{record}'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }

}
