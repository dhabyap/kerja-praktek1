<?php

namespace App\Filament\Resources;

use App\Models\Booking;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Unit;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->can('admin-local')) {
            return $query->whereHas('unit', function ($q) {
                $q->where('appartement_id', auth()->user()->appartement_id);
            });
        }

        return $query;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal')->required(),
                TextInput::make('keterangan'),
                Select::make('booking_id')->label('Pilih Bookingan')
                    ->relationship('booking', 'id')

                    ->options(function () {
                        return Booking::all()->pluck('kode_booking', 'id');
                    })
                    ->searchable(),
                TextInput::make('harga')->numeric()->required(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::getEloquentQuery()
                    ->with(['unit.appartement', 'booking', 'user'])
            )
            ->columns([
                TextColumn::make('kode_invoice')->searchable(),
                TextColumn::make('tanggal')->date(),
                TextColumn::make('booking.kode_booking')->label('Kode Booking'),
                TextColumn::make('booking.unit.nama')->label('Unit'),
                TextColumn::make('booking.unit.appartement.nama')->label('Appartement'),
                TextColumn::make('user.name'),
                TextColumn::make('harga')->money('IDR'),
                TextColumn::make('keterangan'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('admin-global') || auth()->user()->can('super-admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('super-admin');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('super-admin');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('super-admin');
    }


}
