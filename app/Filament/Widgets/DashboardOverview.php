<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\Charts\Chart;
use Filament\Forms\Components\Charts\ChartSeries;

class DashboardOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Booking hari ini
        $todayBooking = Booking::whereDate('tanggal', $now->toDateString())->count();

        // Total transaksi bulan ini
        $monthlyTransactionCount = Transaction::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();

        // Uang masuk bulan ini
        $income = Transaction::where('type', 'masuk')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('harga');

        // Uang keluar bulan ini
        $expense = Transaction::where('type', 'keluar')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('harga');

        return [
            Card::make('Booking Hari Ini', $todayBooking)
                ->description($now->format('d M Y'))
                ->color('info'),

            Card::make('Transaksi Bulan Ini', $monthlyTransactionCount)
                ->color('success'),

            Card::make('Uang Masuk Bulan Ini', 'Rp ' . number_format($income, 0, ',', '.'))
                ->color('primary'),

            Card::make('Uang Keluar Bulan Ini', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->color('danger'),
        ];
    }

}
