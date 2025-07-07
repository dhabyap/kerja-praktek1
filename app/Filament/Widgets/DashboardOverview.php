<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class DashboardOverview extends BaseWidget
{
    public ?int $filterMonth = null;
    public ?int $filterYear = null;

    public function mount()
    {
        $this->filterMonth = request()->query('filterMonth') ?? now()->month;
        $this->filterYear = request()->query('filterYear') ?? now()->year;
    }

    protected function getCards(): array
    {
        $user = auth()->user();

        $now = Carbon::now();

        $startOfMonth = Carbon::createFromDate($this->filterYear, $this->filterMonth, 1)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromDate($this->filterYear, $this->filterMonth, 1)->endOfMonth()->toDateString();

        // Fix: Gunakan nilai dari properti class, bukan dari request langsung
        $bulanTahun = Carbon::createFromDate($this->filterYear, $this->filterMonth, 1)->translatedFormat('F Y');

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
            Card::make('Total Booking Bulan ' . $bulanTahun, $todayBooking)
                ->description($now->format('M Y'))
                ->color('info'),

            Card::make('Total Uang Masuk Bulan ' . $bulanTahun, 'Rp ' . number_format($income, 0, ',', '.'))
                ->color('success'),

            Card::make('Total Uang Keluar Bulan ' . $bulanTahun, 'Rp ' . number_format($expense, 0, ',', '.'))
                ->color('warning'),

            Card::make('Total Laba Bulan ' . $bulanTahun, 'Rp ' . number_format($laba, 0, ',', '.'))
                ->description($laba >= 0 ? 'Untung' : 'Rugi')
                ->descriptionColor($laba >= 0 ? 'success' : 'danger'),

            Card::make('Total Uang Cash Bulan ' . $bulanTahun, 'Rp ' . number_format($cash - $cash_pengeluaran, 0, ',', '.'))
                ->description(number_format($cash, 0, ',', '.') . ' - ' . number_format($cash_pengeluaran, 0, ',', '.'))
                ->color('primary'),

            Card::make('Total Uang Transfer Bulan ' . $bulanTahun, 'Rp ' . number_format($tf - $tf_pengeluaran, 0, ',', '.'))
                ->description(number_format($tf, 0, ',', '.') . ' - ' . number_format($tf_pengeluaran, 0, ',', '.'))
                ->color('primary'),
        ];
    }
}