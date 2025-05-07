<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;



class BookingStats extends StatsOverviewWidget
{
    public ?string $tanggal = null;

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
    }

    protected function getCards(): array
    {
        $user = auth()->user();
        $date = Carbon::parse($this->tanggal);

        $totalTodayQuery = Booking::whereDate('tanggal', $date);
        $masukQuery = Booking::whereDate('tanggal', $date);

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $totalTodayQuery->whereHas('unit', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });

            $masukQuery->whereHas('unit', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });
        }

        $totalToday = $totalTodayQuery->count();
        $tf = $masukQuery->sum('harga_transfer');
        $masuk = $masukQuery->sum('harga_cash');
        $combine = $tf + $masuk;

        return [
            Card::make('Total Booking Hari Ini', $totalToday)
                ->description($date->format('d M Y'))
                ->color('info'),

            Card::make('Total Pemasukan Hari Ini', 'Rp ' . number_format($combine, 0, ',', '.'))
                ->description($date->format('d M Y'))
                ->color('success'),

            Card::make('Total Cash Hari Ini', 'Rp ' . number_format($masuk, 0, ',', '.'))
                ->description($date->format('d M Y'))
                ->color('primary'),

            Card::make('Total Transfer Hari Ini', 'Rp ' . number_format($tf, 0, ',', '.'))
                ->description($date->format('d M Y'))
                ->color('danger'),
        ];
    }
}