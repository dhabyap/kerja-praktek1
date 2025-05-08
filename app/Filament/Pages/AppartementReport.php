<?php

namespace App\Filament\Pages;

use App\Models\Appartement;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AppartementReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.appartement-report';
    protected static ?string $title = 'Laporan Appartement';

    protected static ?string $navigationLabel = 'Laporan Appartement';
    protected static ?string $pluralModelLabel = 'Laporan Appartement';
    protected static ?string $navigationGroup = 'Laporan';

    public ?string $filterStartDate = null;
    public ?string $filterEndDate = null;
    public ?int $filterAppartement = null;

    public function mount()
    {
        $this->filterStartDate = request()->query('filterStartDate', now()->startOfMonth()->format('Y-m-d'));
        $this->filterEndDate = request()->query('filterEndDate', now()->endOfMonth()->format('Y-m-d'));
        $this->filterAppartement = request()->query('filterAppartement');
    }

    protected function getTableQuery(): Builder
    {
        $startDate = $this->filterStartDate ? Carbon::parse($this->filterStartDate)->startOfDay() : now()->startOfMonth();
        $endDate = $this->filterEndDate ? Carbon::parse($this->filterEndDate)->endOfDay() : now()->endOfMonth();

        $query = Appartement::query();

        if ($this->filterAppartement) {
            $query->where('id', $this->filterAppartement);
        }

        return $query->with([
            'units.bookings' => fn($query) => $query->whereBetween('tanggal', [$startDate, $endDate]),
            'units.transactions' => fn($query) => $query->whereBetween('tanggal', [$startDate, $endDate]),
        ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nama')->label('Appartement'),

            TextColumn::make('bookings_count')->label('Jumlah Booking')
                ->getStateUsing(
                    fn($record) => $record->units->flatMap->bookings->count()
                ),

            TextColumn::make('pendapatan_cash')->label('Pendapatan Cash')
                ->money('IDR', true)
                ->getStateUsing(
                    fn($record) => $record->units->flatMap->bookings->sum('harga_cash')
                ),

            TextColumn::make('pendapatan_transfer')->label('Pendapatan Transfer')
                ->money('IDR', true)
                ->getStateUsing(
                    fn($record) => $record->units->flatMap->bookings->sum('harga_transfer')
                ),

            TextColumn::make('total_pendapatan')->label('Total Pendapatan')
                ->money('IDR', true)
                ->getStateUsing(function ($record) {
                    $bookings = $record->units->flatMap->bookings;
                    return $bookings->sum('harga_cash') + $bookings->sum('harga_transfer');
                }),

            TextColumn::make('pengeluaran')->label('Pengeluaran')
                ->money('IDR', true)
                ->getStateUsing(
                    fn($record) => $record->units->flatMap->transactions->sum('harga')
                ),

            TextColumn::make('keuntungan')->label('Keuntungan')
                ->money('IDR', true)
                ->getStateUsing(function ($record) {
                    $bookings = $record->units->flatMap->bookings;
                    $pendapatan = $bookings->sum('harga_cash') + $bookings->sum('harga_transfer');
                    $pengeluaran = $record->units->flatMap->transactions->sum('harga');
                    return max(0, $pendapatan - $pengeluaran);
                }),

            TextColumn::make('kerugian')->label('Kerugian')
                ->money('IDR', true)
                ->getStateUsing(function ($record) {
                    $bookings = $record->units->flatMap->bookings;
                    $pendapatan = $bookings->sum('harga_cash') + $bookings->sum('harga_transfer');
                    $pengeluaran = $record->units->flatMap->transactions->sum('harga');
                    return max(0, $pengeluaran - $pendapatan);
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