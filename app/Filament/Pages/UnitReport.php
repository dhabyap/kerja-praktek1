<?php

namespace App\Filament\Pages;

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
            ]);

        if ($this->filterAppartement) {
            $query->where('appartement_id', $this->filterAppartement);
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nama')->label('Nama Unit'),
            TextColumn::make('appartement.nama')->label('Nama Appartement'),

            TextColumn::make('total_booking')->label('Jumlah Booking')
                ->getStateUsing(fn($record) => $record->bookings->count()),

            TextColumn::make('pendapatan_cash')->label('Pendapatan Cash')
                ->money('IDR', true)
                ->getStateUsing(fn($record) => $record->bookings->sum('harga_cash')),

            TextColumn::make('pendapatan_transfer')->label('Pendapatan Transfer')
                ->money('IDR', true)
                ->getStateUsing(fn($record) => $record->bookings->sum('harga_transfer')),

            TextColumn::make('total_pendapatan')->label('Total Pendapatan')
                ->money('IDR', true)
                ->getStateUsing(function ($record) {
                    return $record->bookings->sum('harga_cash') + $record->bookings->sum('harga_transfer');
                }),

            TextColumn::make('pengeluaran')->label('Pengeluaran')
                ->money('IDR', true)
                ->getStateUsing(fn($record) => $record->transactions->sum('harga')),

            TextColumn::make('keuntungan')->label('Keuntungan')
                ->money('IDR', true)
                ->getStateUsing(function ($record) {
                    $total = $record->bookings->sum('harga_cash') + $record->bookings->sum('harga_transfer');
                    return max(0, $total - $record->transactions->sum('harga'));
                }),

            TextColumn::make('kerugian')->label('Kerugian')
                ->money('IDR', true)
                ->getStateUsing(function ($record) {
                    $total = $record->bookings->sum('harga_cash') + $record->bookings->sum('harga_transfer');
                    return max(0, $record->transactions->sum('harga') - $total);
                }),
        ];
    }
}
