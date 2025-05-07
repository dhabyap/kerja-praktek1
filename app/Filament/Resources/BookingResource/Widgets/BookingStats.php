<?php

namespace App\Filament\Resources\BookingResource\Widgets;

use Filament\Forms\Components\DatePicker;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;
use App\Models\Booking;

class BookingStats extends StatsOverviewWidget
{
    public ?string $tanggal = null;

    protected function getCards(): array
    {
        $tanggal = $this->tanggal ?? now()->format('Y-m-d');
        $date = Carbon::parse($tanggal);
        $user = auth()->user();

        $query = Booking::whereDate('tanggal', $date);

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $query->whereHas('unit', fn($q) => $q->where('appartement_id', $user->appartement_id));
        }

        $total = $query->count();
        $cash = (clone $query)->sum('harga_cash');
        $transfer = (clone $query)->sum('harga_transfer');
        $totalMasuk = $cash + $transfer;

        return [
            Card::make('Total Booking Hari Ini', $total)
                ->description($date->format('d M Y'))
                ->color('info'),
            Card::make('Total Pemasukan', 'Rp ' . number_format($totalMasuk, 0, ',', '.'))->color('success'),
            Card::make('Total Cash', 'Rp ' . number_format($cash, 0, ',', '.'))->color('primary'),
            Card::make('Total Transfer', 'Rp ' . number_format($transfer, 0, ',', '.'))->color('danger'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('tanggal')
                ->label('Pilih Tanggal')
                ->default(now())
                ->reactive()
                ->afterStateUpdated(fn() => $this->dispatch('refresh')),
        ];
    }

    protected function hasForm(): bool
    {
        return true;
    }
}
