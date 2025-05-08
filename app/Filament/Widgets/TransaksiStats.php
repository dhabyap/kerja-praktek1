<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;
use App\Models\Booking;


class TransaksiStats extends BaseWidget
{
    public ?string $tanggal = null;

    protected function getCards(): array
    {
        $user = auth()->user();
        $now = Carbon::now();

        // Pastikan format tanggal benar
        $startDate = $this->tanggal
            ? Carbon::parse($this->tanggal)->startOfMonth()
            : $now->copy()->startOfMonth();

        $endDate = $this->tanggal
            ? Carbon::parse($this->tanggal)->endOfMonth()
            : $now->copy()->endOfMonth();

        // Debug: tampilkan range tanggal yang digunakan
        \Log::debug('Filter Date Range:', [
            'start' => $startDate->format('Y-m-d H:i:s'),
            'end' => $endDate->format('Y-m-d H:i:s'),
            'input' => $this->tanggal
        ]);

        $query = Transaction::whereBetween('tanggal', [$startDate, $endDate]);

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $query->whereHas('unit', fn($q) => $q->where('appartement_id', $user->appartement_id));
        }

        // Debug: tampilkan SQL query yang dihasilkan
        \Log::debug('SQL Query:', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);

        $transactions = $query->get();

        // Debug: tampilkan data yang ditemukan
        \Log::debug('Transactions Found:', $transactions->toArray());

        $transactionsByType = $transactions->groupBy('type');
        $sumsByType = $transactionsByType->map(function ($transactions) {
            return $transactions->sum('harga');
        });

        $cards = [];
        $totalAll = 0;

        foreach ($this->getTypeOptions() as $value => $label) {
            $sum = $sumsByType->get($value, 0);
            $totalAll += $sum;

            $cards[] = Card::make("Total Pengeluaran $label", 'Rp ' . number_format($sum, 0, ',', '.'))
                ->color('danger');
        }

        $monthName = $startDate->translatedFormat('F Y');
        $cards[] = Card::make("Total Pengeluaran Bulan Ini", 'Rp ' . number_format($totalAll, 0, ',', '.'))
            ->description($monthName)
            ->color('danger');

        return $cards;
    }

    protected function getTypeOptions(): array
    {
        return [
            'token' => 'Token dan Air',
            'sewa_unit' => 'Sewa Unit',
            'gaji' => 'Gaji',
            'lainnya' => 'Lainnya',
        ];
    }
}