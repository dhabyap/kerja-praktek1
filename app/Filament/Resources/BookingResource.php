<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Unit;
use Filament\Tables;
use App\Models\Booking;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appartement;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BookingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Filament\Resources\TransactionRelationManagerResource\RelationManagers\TransactionRelationManager;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->can('admin-local') || auth()->user()->can('admin-global')) {
            return $query->whereHas('unit', function ($q) {
                $q->where('appartement_id', auth()->user()->appartement_id);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $user = Filament::auth()->user();

        return $form
            ->schema([
                TextInput::make('nama')->required(),
                DatePicker::make('tanggal')->required(),
                // TextInput::make('keterangan'),
                Select::make('unit_id')->label('Pilih Unit')
                    ->relationship('unit', 'nama')->options(function () use ($user) {
                        if ($user->level_id === 1) {
                            return Unit::all()->pluck('nama', 'id');
                        } else {
                            return Unit::where('appartement_id', $user->appartement_id)->pluck('nama', 'id');
                        }
                    })
                    ->required(),
                TextInput::make('harga')->numeric()->required(),
                Select::make('waktu')
                    ->label('Pilih Waktu')
                    ->options([
                        'siang' => 'Siang',
                        'malam' => 'Malam',
                    ])
                    ->required(),
                Select::make('keterangan')
                    ->label('keterangan')
                    ->options([
                        'halfday' => 'Halfday',
                        'fullday' => 'Fullday',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('kode_booking'),
                TextColumn::make('nama'),
                TextColumn::make('tanggal')->date(),
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('unit.nama')->label('Unit'),
                TextColumn::make('harga')->money('IDR'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadInvoice')
                    ->label('Download Invoice')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => route('booking.download.invoice', ['booking' => $record->id]))
                    ->openUrlInNewTab(),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // TransactionRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
    protected function mutatingFormDataBeforeCreate(array $data): array
    {
        $tanggal = now()->format('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $data['kode_invoice'] = "INV-{$tanggal}-{$random}";
        $data['user_id'] = Filament::auth()->user()->id;

        return $data;
    }
}
