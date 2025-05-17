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

        $masuk = Booking::query();
        $keluar = Transaction::query();

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $masuk->whereHas('unit', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });

            $keluar->whereHas('user', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });
        }

        $tf = $masuk->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('harga_transfer');
        $cash = $masuk->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('harga_cash');
        $income = $tf + $cash;
        $expense = $keluar->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('harga');
        $cash_pengeluaran = $keluar->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->where('tipe_pembayaran', 'cash')->sum('harga');

        $todayBooking = $masuk->count();
        $laba = $income - $expense;

        return [
            Card::make('Booking Bulan Ini', $todayBooking)
                ->description($now->format('M Y'))
                ->color('info'),

            Card::make('Uang Masuk Bulan Ini', 'Rp ' . number_format($income, 0, ',', '.'))
                ->color('success'),

            Card::make('Uang Keluar Bulan Ini', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->color('warning'),

            Card::make('Laba Bulan Ini', 'Rp ' . number_format($laba, 0, ',', '.'))
                ->description($laba >= 0 ? 'Untung' : 'Rugi')
                ->descriptionColor($laba >= 0 ? 'success' : 'danger'),

            Card::make('Cash Bulan Ini', 'Rp ' . number_format($cash - $cash_pengeluaran, 0, ',', '.'))
                ->color('Primary'),

        ];
    }

}
