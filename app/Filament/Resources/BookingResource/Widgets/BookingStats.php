<?php

namespace App\Filament\Resources\BookingResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Booking;
use Carbon\Carbon;

class BookingStats extends BaseWidget
{
    protected function getCards(): array
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Mulai dengan query builder
        $totalTodayQuery = Booking::whereDate('tanggal', $today);
        $masukQuery = Booking::whereDate('tanggal', $today);

        // Filter jika admin lokal/global
        if ($user->can('admin-local') || $user->can('admin-global')) {
            $totalTodayQuery->whereHas('unit', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });

            $masukQuery->whereHas('unit', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });
        }

        // Eksekusi query
        $totalToday = $totalTodayQuery->count();
        $tf = $masukQuery->sum('harga_transfer');
        $masuk = $masukQuery->sum('harga_cash');

        $combine = $tf + $masuk;

        return [
            Card::make('Total Booking Hari Ini', $totalToday)
                ->description($today->format('d M Y'))
                ->color('info'),

            Card::make('Total Pemasukan Hari Ini', 'Rp ' . number_format($combine, 0, ',', '.'))
                ->description($today->format('d M Y'))
                ->color('success'),

            Card::make('Total Cash Hari Ini', 'Rp ' . number_format($masuk, 0, ',', '.'))
                ->description($today->format('d M Y'))
                ->color('primary'),

            Card::make('Total Transfer Hari Ini', 'Rp ' . number_format($tf, 0, ',', '.'))
                ->description($today->format('d M Y'))
                ->color('danger'),
        ];
    }

}
