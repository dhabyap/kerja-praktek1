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
        $today = Carbon::today();

        $totalToday = Booking::whereDate('tanggal', $today)->count();

        $masuk = Booking::whereDate('tanggal', $today)
            ->sum('harga');

        return [
            Card::make('Total Booking Hari Ini', $totalToday)
                ->description($today->format('d M Y'))
                ->color('info'),

            Card::make('Total Pemasukan Hari Ini', number_format($masuk, 0, ',', '.'))
                ->description($today->format('d M Y'))
                ->color('success'),
        ];
    }
}
