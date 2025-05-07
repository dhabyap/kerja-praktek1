<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Widgets\BookingStats;
use App\Models\User;
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
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\Filter;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaksi';
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
                TextInput::make('harga_transfer')->numeric(),
                Select::make('waktu')
                    ->label('Pilih Waktu')
                    ->options([
                        'siang' => 'Siang',
                        'malam' => 'Malam',
                    ])
                    ->required(),
                TextInput::make('harga_cash')->numeric(),

                Select::make('keterangan')
                    ->label('keterangan')
                    ->options([
                        'halfday' => 'Halfday',
                        'fullday' => 'Fullday',
                        'transit' => 'Transit',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('tanggal')->date()->sortable(),
                TextColumn::make('keterangan')->label('Ketengaran')->sortable(),
                TextColumn::make('user.name')->label('Nama Admin')->searchable()->sortable(),
                TextColumn::make('unit.nama')->label('Unit')->sortable(),
                TextColumn::make('harga_cash')->money('IDR')->sortable(),
                TextColumn::make('harga_transfer')->money('IDR')->sortable(),
                TextColumn::make('unit.appartement.nama')->label('Nama Appartement')->sortable(),
            ])
            ->filters([
                Filter::make('nama')
                    ->form([
                        TextInput::make('nama')->label('Nama'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['nama'], fn($q, $nama) => $q->where('nama', 'like', "%{$nama}%"));
                    }),

                Filter::make('tanggal_range')
                    ->form([
                        DatePicker::make('tanggal_from')->label('Dari Tanggal'),
                        DatePicker::make('tanggal_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['tanggal_from'], fn($q, $from) => $q->whereDate('tanggal', '>=', $from))
                            ->when($data['tanggal_until'], fn($q, $until) => $q->whereDate('tanggal', '<=', $until));
                    }),

                // Filter Unit
                Filter::make('unit_id')
                    ->form([
                        Select::make('unit_id')
                            ->label('Unit')
                            ->options(Unit::pluck('nama', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['unit_id'], fn($q, $unitId) => $q->where('unit_id', $unitId));
                    }),

                // Filter User
                Filter::make('user_id')
                    ->form([
                        Select::make('user_id')
                            ->label('User')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['user_id'], fn($q, $userId) => $q->where('user_id', $userId));
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadExcel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn() => route('booking.export', request()->all()))
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\Action::make('downloadInvoice')
                //     ->label('Download Invoice')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->url(fn($record) => route('booking.download.invoice', ['booking' => $record->id]))
                //     ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            // TransactionRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            BookingStats::class,
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

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('super-admin');
    }

}
