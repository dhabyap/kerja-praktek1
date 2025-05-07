<?php
namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class TransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Transaksi Bulanan';

    protected function getData(): array
    {
        $user = auth()->user();

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $labels = [];
        $dataMasuk = [];
        $dataKeluar = [];

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $labels[] = $date->format('d M');

            // Mulai dengan query builder
            $masukQuery = Booking::whereDate('tanggal', $date);
            $keluarQuery = Transaction::whereDate('tanggal', $date);

            // Filter jika admin lokal/global
            if ($user->can('admin-local') || $user->can('admin-global')) {
                $masukQuery->whereHas('unit', function ($q) use ($user) {
                    $q->where('appartement_id', $user->appartement_id);
                });

                $keluarQuery->whereHas('unit', function ($q) use ($user) {
                    $q->where('appartement_id', $user->appartement_id);
                });
            }

            // Eksekusi query
            $dataMasuk[] = $masukQuery->sum('harga_cash') + $masukQuery->sum('harga_transfer');
            $dataKeluar[] = $keluarQuery->sum('harga');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Uang Masuk',
                    'data' => $dataMasuk,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)', // biru
                ],
                [
                    'label' => 'Uang Keluar',
                    'data' => $dataKeluar,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.6)', // merah
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
