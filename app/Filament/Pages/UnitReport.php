<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UnitResource;
use App\Models\Appartement;
use App\Models\Unit;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;

class UnitReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static string $view = 'filament.pages.unit-report';
    protected static ?string $title = 'Laporan Unit';

    protected static ?string $navigationLabel = 'Laporan Unit';
    protected static ?string $pluralModelLabel = 'Laporan Unit';
    protected static ?string $navigationGroup = 'Laporan';

    public int $filterMonth;
    public int $filterYear;
    public ?int $filterAppartement = null;

    public function mount()
    {
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)->schema([
                Select::make('filterMonth')
                    ->label('Bulan')
                    ->options(
                        collect(range(1, 12))->mapWithKeys(
                            fn($m) => [$m => Carbon::create()->month($m)->locale('id')->translatedFormat('F')]
                        )
                    ),
                Select::make('filterYear')
                    ->label('Tahun')
                    ->options(array_combine(range(2023, now()->year), range(2023, now()->year))),
                Select::make('filterAppartement')
                    ->label('Appartement')
                    ->options(Appartement::all()->pluck('nama', 'id'))
                    ->searchable()
                    ->placeholder('Semua Appartement'),
            ]),
        ];
    }

    public function filterData()
    {
        $this->resetTable();
    }

    protected function getTableQuery()
    {
        $startDate = Carbon::create($this->filterYear, $this->filterMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = Unit::query()
            ->with([
                'bookings' => fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]),
                'transactions' => fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]),
                'appartement',
            ])
            ->select('units.*')
            ->selectRaw('
                (
                    SELECT COALESCE(SUM(harga_cash), 0)
                    FROM bookings
                    WHERE bookings.unit_id = units.id
                    AND bookings.tanggal BETWEEN ? AND ?
                ) as pendapatan_cash,
                (
                    SELECT COALESCE(SUM(harga_transfer), 0)
                    FROM bookings
                    WHERE bookings.unit_id = units.id
                    AND bookings.tanggal BETWEEN ? AND ?
                ) as pendapatan_transfer,
                (
                    SELECT COALESCE(SUM(harga_cash + harga_transfer), 0)
                    FROM bookings
                    WHERE bookings.unit_id = units.id
                    AND bookings.tanggal BETWEEN ? AND ?
                ) as total_pendapatan,
                (
                    SELECT COALESCE(SUM(harga), 0)
                    FROM transactions
                    WHERE transactions.unit_id = units.id
                    AND transactions.tanggal BETWEEN ? AND ?
                ) as pengeluaran,
                GREATEST(
                    (
                        SELECT COALESCE(SUM(harga_cash + harga_transfer), 0)
                        FROM bookings
                        WHERE bookings.unit_id = units.id
                        AND bookings.tanggal BETWEEN ? AND ?
                    ) -
                    (
                        SELECT COALESCE(SUM(harga), 0)
                        FROM transactions
                        WHERE transactions.unit_id = units.id
                        AND transactions.tanggal BETWEEN ? AND ?
                    ),
                0) as keuntungan,
                GREATEST(
                    (
                        SELECT COALESCE(SUM(harga), 0)
                        FROM transactions
                        WHERE transactions.unit_id = units.id
                        AND transactions.tanggal BETWEEN ? AND ?
                    ) -
                    (
                        SELECT COALESCE(SUM(harga_cash + harga_transfer), 0)
                        FROM bookings
                        WHERE bookings.unit_id = units.id
                        AND bookings.tanggal BETWEEN ? AND ?
                    ),
                0) as kerugian,
                    (
                    SELECT COUNT(*)
                    FROM bookings
                    WHERE bookings.unit_id = units.id
                    AND bookings.tanggal BETWEEN ? AND ?
                ) as total_booking
            ', [
                $startDate,
                $endDate,  // pendapatan_cash
                $startDate,
                $endDate,  // pendapatan_transfer
                $startDate,
                $endDate,  // total_pendapatan
                $startDate,
                $endDate,  // pengeluaran
                $startDate,
                $endDate,  // keuntungan: bookings
                $startDate,
                $endDate,  // keuntungan: transactions
                $startDate,
                $endDate,  // kerugian: transactions
                $startDate,
                $endDate,  // kerugian: bookings
                $startDate,
                $endDate,  // kerugian: bookings
            ]);

        if ($this->filterAppartement) {
            $query->where('appartement_id', $this->filterAppartement);
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nama')
                ->label('Nama Unit')
                ->sortable()
                ->url(function ($record) {
                    return UnitResource::getUrl('view', [
                        'record' => $record,
                        'filterMonth' => $this->filterMonth,
                        'filterYear' => $this->filterYear,
                    ]);
                })
                ->openUrlInNewTab(false)
                ->color('primary'),

            TextColumn::make('appartement.nama')
                ->label('Nama Appartement')
                ->sortable(),

            TextColumn::make('total_booking')
                ->label('Jumlah Booking')
                ->sortable()
                ->getStateUsing(fn($record) => $record->bookings->count()),

            TextColumn::make('pendapatan_cash')
                ->label('Pendapatan Cash')
                ->money('IDR', true)
                ->sortable()
                ->getStateUsing(fn($record) => $record->bookings->sum('harga_cash')),

            TextColumn::make('pendapatan_transfer')
                ->label('Pendapatan Transfer')
                ->money('IDR', true)
                ->sortable()
                ->getStateUsing(fn($record) => $record->bookings->sum('harga_transfer')),

            TextColumn::make('total_pendapatan')
                ->label('Total Pendapatan')
                ->money('IDR', true)
                ->sortable()
                ->getStateUsing(function ($record) {
                    return $record->bookings->sum('harga_cash') + $record->bookings->sum('harga_transfer');
                }),

            TextColumn::make('pengeluaran')
                ->label('Pengeluaran')
                ->money('IDR', true)
                ->sortable()
                ->getStateUsing(fn($record) => $record->transactions->sum('harga')),

            TextColumn::make('keuntungan')
                ->label('Keuntungan')
                ->money('IDR', true)
                ->sortable()
                ->getStateUsing(function ($record) {
                    $total = $record->bookings->sum('harga_cash') + $record->bookings->sum('harga_transfer');
                    return max(0, $total - $record->transactions->sum('harga'));
                }),

            TextColumn::make('kerugian')
                ->label('Kerugian')
                ->money('IDR', true)
                ->sortable()
                ->getStateUsing(function ($record) {
                    $total = $record->bookings->sum('harga_cash') + $record->bookings->sum('harga_transfer');
                    return max(0, $record->transactions->sum('harga') - $total);
                }),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('super-admin') || auth()->user()->can('admin-global');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('super-admin') || auth()->user()->can('admin-global');
    }
}
