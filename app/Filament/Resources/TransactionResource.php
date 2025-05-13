<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use App\Models\Unit;
use App\Models\User;
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
use Filament\Tables\Filters\Filter;
use Filament\Facades\Filament;
use Filament\Tables\Columns\Summarizers;


class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pengeluaran';
    protected static ?string $pluralModelLabel = 'Pengeluaran';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->can('admin-local') || auth()->user()->can('admin-global')) {
            return $query->whereHas('unit', function ($q) {
                $q->where('appartement_id', auth()->user()->appartement_id);
            });
        }
        $query = $query->selectRaw('*, SUM(CASE WHEN tipe_pembayaran = "cash" THEN harga ELSE 0 END) AS total_cash')
            ->selectRaw('SUM(CASE WHEN tipe_pembayaran = "transfer" THEN harga ELSE 0 END) AS total_transfer')
            ->groupBy('id');

        return $query;
    }

    public static function form(Form $form): Form
    {
        // Cara yang benar untuk mendapatkan user di static method
        $user = Filament::auth()->user();

        return $form
            ->schema([
                DatePicker::make('tanggal')->required(),
                Select::make('type')
                    ->label('Pilih Type')
                    ->options([
                        'token' => 'Token dan Air',
                        'sewa_unit' => 'Sewa Unit',
                        'gaji' => 'Gaji',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required(),
                Select::make('tipe_pembayaran')
                    ->label('Pilih Tipe Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'transfer' => 'Transfer',
                    ])
                    ->required(),

                Select::make('unit_id')
                    ->label('Pilih Unit (Optional)')
                    ->options(function () use ($user) { // Tambahkan use ($user)
                        if (!$user) {
                            return Unit::all()->pluck('nama', 'id');
                        }

                        return $user->level_id === 1
                            ? Unit::all()->pluck('nama', 'id')
                            : Unit::where('appartement_id', $user->appartement_id)->pluck('nama', 'id');
                    })
                    ->searchable(),
                TextInput::make('harga')
                    ->numeric()
                    ->required(),
                TextInput::make('keterangan')->label('Keterangan (Optional)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::getEloquentQuery()
                    ->with(['unit.appartement', 'user'])
            )
            ->columns([
                TextColumn::make('kode_invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('id')
                    ->label('Unit')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->booking?->unit?->nama ?? $record->unit?->nama ?? '-';
                    }),

                TextColumn::make('created_at')
                    ->label('Appartement')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->booking?->unit?->appartement?->nama ?? $record->unit?->appartement?->nama ?? '-';
                    }),

                TextColumn::make('user.name')
                    ->label('Nama Admin')
                    ->searchable()
                    ->sortable(),

                // Kolom untuk harga cash
                TextColumn::make('total_cash')
                    ->label('Total Cash')
                    ->sortable()
                    ->money('IDR')
                    ->summarize([
                        Summarizers\Sum::make()
                            ->label('Total Cash')
                            ->money('IDR'),
                    ]),

                // Display the total for 'transfer' payments
                TextColumn::make('total_transfer')
                    ->label('Total Transfer')
                    ->sortable()
                    ->money('IDR')
                    ->summarize([
                        Summarizers\Sum::make()
                            ->label('Total Transfer')
                            ->money('IDR'),
                    ]),

                TextColumn::make('tipe_pembayaran')
                    ->label('Tipe Pembayaran')
                    ->sortable(),

                TextColumn::make('type'),

                TextColumn::make('keterangan'),
            ])

            ->filters([
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
                Filter::make('type')
                    ->form([
                        Select::make('type')
                            ->label('Pilih Tipe')
                            ->options([
                                'token' => 'Token dan Air',
                                'sewa_unit' => 'Sewa Unit',
                                'gaji' => 'Gaji',
                                'lainnya' => 'Lainnya',
                            ])
                            ->searchable()
                            ->preload(),
                    ])->query(function ($query, array $data) {
                        return $query->when($data['type'], fn($q, $type) => $q->where('type', $type));
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadExcel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn() => route('transaksi.export', request()->all()))
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // redirect ke halaman list transaction
        // Atau redirect ke halaman custom:
        // return route('filament.admin.pages.dashboard');
    }


    // public static function canViewAny(): bool
    // {
    //     return auth()->user()->can('admin-global') || auth()->user()->can('super-admin');
    // }

    // public static function canCreate(): bool
    // {
    //     return auth()->user()->can('super-admin') || auth()->user()->can('admin-global');
    // }

    // public static function canEdit(Model $record): bool
    // {
    //     return auth()->user()->can('super-admin');
    // }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('super-admin');
    }


}
