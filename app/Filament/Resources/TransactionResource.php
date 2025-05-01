<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use App\Models\Unit;
use Filament\Tables;
use App\Models\Booking;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransactionResource\Pages\EditTransaction;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use Illuminate\Support\Facades\Log;


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
                Select::make('booking_id')->label('Pilih Bookingan(Optional)')
                    ->relationship('booking', 'id')

                    ->options(function () {
                        return Booking::all()->pluck('kode_booking', 'id');
                    })
                    ->searchable(),
                DatePicker::make('tanggal')->required(),
                TextInput::make('keterangan'),
                Select::make('unit_id')->label('Pilih Unit(Optional)')
                    ->options(function () {
                        return Unit::all()->pluck('nama', 'id');
                    })
                    ->searchable(),
                TextInput::make('harga')->numeric()->required(),
                Select::make('type')
                    ->label('Pilih Type')
                    ->options([
                        'masuk' => 'Masuk',
                        'keluar' => 'Keluar',
                    ])
                    ->required(),
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

                TextColumn::make('id')
                    ->label('Unit')
                    ->formatStateUsing(function ($record) {
                        return $record->booking?->unit?->nama ?? $record->unit?->nama ?? '-';
                    }),

                TextColumn::make('created_at')
                    ->label('Appartement')
                    ->formatStateUsing(function ($record) {
                        return $record->booking?->unit?->appartement?->nama ?? $record->unit?->appartement?->nama ?? '-';
                    }),

                // TextColumn::make('booking.unit.appartement.nama')->label('Appartement') ?? TextColumn::make('unit.appartement.nama')->label('Appartement'),

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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
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
