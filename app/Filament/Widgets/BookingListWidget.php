<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Transaction;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers;
use Illuminate\Database\Eloquent\Builder;

class BookingListWidget extends BaseWidget
{
    public ?int $unitId = null;
    public ?int $filterMonth = null;
    public ?int $filterYear = null;
    protected int|string|array $columnSpan = 'full';
    protected array $queryString = [
        'filterMonth' => ['except' => null],
        'filterYear' => ['except' => null],
    ];

    public function mount()
    {
        $this->filterMonth = $this->filterMonth ?? request()->query('filterMonth', now()->month);
        $this->filterYear = $this->filterYear ?? request()->query('filterYear', now()->year);
    }

    protected function getTableQuery(): Builder
    {
        $query = Booking::query()
            ->where('unit_id', $this->unitId)
            ->whereMonth('tanggal', $this->filterMonth)
            ->whereYear('tanggal', $this->filterYear);

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nama')->searchable()->sortable(),
            TextColumn::make('tanggal')->date()->sortable(),
            TextColumn::make('keterangan')->label('Ketengaran')->sortable(),
            TextColumn::make('user.name')->label('Nama Admin')->searchable()->sortable(),
            TextColumn::make('unit.nama')->label('Unit')->sortable(),
            TextColumn::make('harga_cash')
                ->money('IDR')
                ->sortable()
                ->summarize([
                    Summarizers\Sum::make()
                        ->money('IDR')
                        ->label('Total Cash')
                ]),
            TextColumn::make('harga_transfer')
                ->money('IDR')
                ->sortable()
                ->summarize([
                    Summarizers\Sum::make()
                        ->money('IDR')
                        ->label('Total Transfer')
                ]),
            TextColumn::make('unit.appartement.nama')->label('Nama Appartement')->sortable(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make('labelBulan')
                ->label('Data Bulan: ' . \Carbon\Carbon::create()->month($this->filterMonth)->translatedFormat('F') . ' ' . $this->filterYear)
                ->disabled()
                ->color('gray'),

            Action::make('downloadExcel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn() => route('booking.export', [
                    'unit_id' => $this->unitId,
                    'filterMonth' => $this->filterMonth,
                    'filterYear' => $this->filterYear,
                ]))
                ->openUrlInNewTab(),
        ];
    }



}
