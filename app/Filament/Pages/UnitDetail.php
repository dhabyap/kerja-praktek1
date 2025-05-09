<?php

namespace App\Filament\Pages;

use App\Models\Unit;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Panel;
use Illuminate\Support\Carbon;

class UnitDetail extends Page
{
    protected static string $view = 'filament.pages.unit-detail';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public Unit $record;
    public $startDate;
    public $endDate;

    public function mount(Unit $record)
    {
        $this->record = $record;
        $this->startDate = Carbon::now()->startOfMonth();
        $this->endDate = Carbon::now()->endOfMonth();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Components\Section::make('Informasi Unit')
                    ->schema([
                        Components\TextEntry::make('nama'),
                        Components\TextEntry::make('appartement.nama')
                            ->label('Apartemen'),
                        Components\TextEntry::make('type'),
                        Components\TextEntry::make('status'),
                    ])->columns(2),

                Components\Section::make('Statistik Keuangan')
                    ->schema([
                        Components\TextEntry::make('bookings_count')
                            ->label('Total Booking')
                            ->state(fn() => $this->record->bookings()
                                ->whereBetween('tanggal', [$this->startDate, $this->endDate])
                                ->count()),
                        Components\TextEntry::make('total_pendapatan')
                            ->label('Total Pendapatan')
                            ->money('IDR')
                            ->state(fn() => $this->record->bookings()
                                ->whereBetween('tanggal', [$this->startDate, $this->endDate])
                                ->sum('harga_cash') +
                                $this->record->bookings()
                                    ->whereBetween('tanggal', [$this->startDate, $this->endDate])
                                    ->sum('harga_transfer')),
                        Components\TextEntry::make('pengeluaran')
                            ->label('Total Pengeluaran')
                            ->money('IDR')
                            ->state(fn() => $this->record->transactions()
                                ->whereBetween('tanggal', [$this->startDate, $this->endDate])
                                ->sum('harga')),
                    ])->columns(3),
            ]);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->pages([
                \App\Filament\Pages\UnitDetail::class,
            ]);
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         UnitDetailWidgets\DateFilter::class,
    //         UnitDetailWidgets\FinancialSummary::class,
    //     ];
    // }
}