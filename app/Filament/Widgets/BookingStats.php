<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Booking;
use Carbon\Carbon;

class BookingStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    public ?string $filterDate = null;

    protected function getCards(): array
    {
        $user = auth()->user();
        $date = $this->filterDate ? Carbon::parse($this->filterDate) : Carbon::now();

        $totalTodayQuery = Booking::whereDate('tanggal', $date);
        $masukQuery = Booking::whereDate('tanggal', $date);

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $totalTodayQuery->whereHas('user', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });

            $masukQuery->whereHas('user', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });
        }

        $totalToday = $totalTodayQuery->count();
        $tf = $masukQuery->sum('harga_transfer');
        $masuk = $masukQuery->sum('harga_cash');
        $combine = $tf + $masuk;

        return [
            Card::make('Total Booking', $totalToday)
                ->description($date->format('d M Y'))
                ->color('info'),

            Card::make('Total Pemasukan', 'Rp ' . number_format($combine, 0, ',', '.'))
                ->description($date->format('d M Y'))
                ->color('success'),

            Card::make('Total Cash', 'Rp ' . number_format($masuk, 0, ',', '.'))
                ->description($date->format('d M Y'))
                ->color('primary'),

            Card::make('Total Transfer', 'Rp ' . number_format($tf, 0, ',', '.'))
                ->description($date->format('d M Y'))
                ->color('danger'),
        ];
    }

    protected function getListeners(): array
    {
        return [
            'updateBookingStats' => 'updateDate',
        ];
    }

    public function updateDate(string $date): void
    {
        $this->filterDate = $date;
        $this->dispatch('updateStats');
    }
}