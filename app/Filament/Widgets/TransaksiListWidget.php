<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TransaksiListWidget extends BaseWidget
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

        $query = Transaction::query()
            ->where('unit_id', $this->unitId)
            ->whereMonth('tanggal', $this->filterMonth)
            ->whereYear('tanggal', $this->filterYear);

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
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
            TextColumn::make('harga')
                ->money('IDR')
                ->sortable()
                ->summarize([
                    Summarizers\Sum::make()
                        ->money('IDR')
                        ->label('Total')
                ]),
            TextColumn::make('tipe_pembayaran')
                ->label('Tipe Pembayaran')
                ->sortable(),
            TextColumn::make('type'),
            TextColumn::make('keterangan'),
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
                ->url(fn() => route('transaksi.export', [
                    'unit_id' => $this->unitId,
                    'filterMonth' => $this->filterMonth,
                    'filterYear' => $this->filterYear,
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
