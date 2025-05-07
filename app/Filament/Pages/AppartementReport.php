<?php

namespace App\Filament\Pages;

use App\Models\Appartement;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class AppartementReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.appartement-report';
    protected static ?string $title = 'Laporan Appartement';

    public ?int $filterMonth;
    public ?int $filterYear;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->can('admin-local') || auth()->user()->can('admin-global')) {
            return $query->where('appartement_id', auth()->user()->appartement_id);
        }

        return $query;
    }
    public function mount()
    {
        $this->filterMonth = request()->query('filterMonth', now()->month);
        $this->filterYear = request()->query('filterYear', now()->year);
    }


    protected function getFormSchema(): array
    {
        return [
            Select::make('filterMonth')
                ->label('Bulan')
                ->options(array_combine(range(1, 12), array_map(function ($month) {
                    return Carbon::create()->month($month)->translatedFormat('F');
                }, range(1, 12))))
                ->default(now()->month)
                ->reactive(), // Pastikan ini reactive agar formnya bisa diperbarui

            Select::make('filterYear')
                ->label('Tahun')
                ->options(range(2023, now()->year + 1))
                ->default(now()->year)
                ->reactive(), // Pastikan ini reactive agar formnya bisa diperbarui
        ];
    }


    protected function getTableQuery()
    {
        $start = Carbon::create($this->filterYear, $this->filterMonth)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return Appartement::with([
            'units.bookings' => fn($query) => $query->whereBetween('tanggal', [$start, $end]),
            'units.transactions' => fn($query) => $query->whereBetween('tanggal', [$start, $end]),
        ]);
    }

    public function filterData()
    {
        $this->filterMonth = $this->form->getState()['filterMonth'];
        $this->filterYear = $this->form->getState()['filterYear'];

        $this->resetTable();
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
        return auth()->user()->can('super-admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('super-admin');
    }

}
