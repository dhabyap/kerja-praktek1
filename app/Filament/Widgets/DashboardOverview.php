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
        $user = auth()->user();

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $masukBase = Booking::query();
        $keluarBase = Transaction::query();

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $masukBase->whereHas('unit', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });

            $keluarBase->whereHas('user', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });
        }

        // Clone dan beri kondisi masing-masing
        $masukQuery = (clone $masukBase)->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
        $keluarQuery = (clone $keluarBase)->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);

        $tf = (clone $masukQuery)->sum('harga_transfer');
        $cash = (clone $masukQuery)->sum('harga_cash');
        $income = $tf + $cash;

        $expense = (clone $keluarQuery)->sum('harga');
        $cash_pengeluaran = (clone $keluarQuery)->where('tipe_pembayaran', 'cash')->sum('harga');
        $tf_pengeluaran = (clone $keluarQuery)->where('tipe_pembayaran', 'transfer')->sum('harga');

        $todayBooking = (clone $masukQuery)->count();
        $laba = $income - $expense;

        return [
            Card::make('Total Booking Bulan Ini', $todayBooking)
                ->description($now->format('M Y'))
                ->color('info'),

            Card::make('Total Uang Masuk Bulan Ini', 'Rp ' . number_format($income, 0, ',', '.'))
                ->color('success'),

            Card::make('Total Uang Keluar Bulan Ini', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->color('warning'),

            Card::make('Total Laba Bulan Ini', 'Rp ' . number_format($laba, 0, ',', '.'))
                ->description($laba >= 0 ? 'Untung' : 'Rugi')
                ->descriptionColor($laba >= 0 ? 'success' : 'danger'),

            Card::make('Total Uang Cash Bulan Ini', 'Rp ' . number_format($cash - $cash_pengeluaran, 0, ',', '.'))
                ->color('primary'),

            Card::make('Total Uang Transfer Bulan Ini', 'Rp ' . number_format($tf - $tf_pengeluaran, 0, ',', '.'))
                ->color('primary'),
        ];
    }

}
