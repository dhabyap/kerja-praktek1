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
        $todayBooking = Booking::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();

        // Total transaksi bulan ini
        // $monthlyTransactionCount = Transaction::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();

        // Uang masuk bulan ini
        $income = Booking::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('harga');

        // Uang keluar bulan ini
        $expense = Transaction::
            whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('harga');

        // Laba Bulan ini
        $laba = $income - $expense;

        return [
            Card::make('Booking Bulan Ini', $todayBooking)
                ->description($now->format('d M Y'))
                ->color('info')
                ->extraAttributes([
                    'class' => 'bg-blue-100 text-blue-800',
                ]),

            Card::make('Uang Masuk Bulan Ini', 'Rp ' . number_format($income, 0, ',', '.'))
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-green-100 text-green-800',
                ]),

            Card::make('Uang Keluar Bulan Ini', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-yellow-100 text-yellow-800',
                ]),

            Card::make('Laba Bulan Ini', 'Rp ' . number_format($laba, 0, ',', '.'))
                ->description($laba >= 0 ? 'Untung' : 'Rugi')
                ->descriptionColor($laba >= 0 ? 'success' : 'danger')

        ];


    }

}
