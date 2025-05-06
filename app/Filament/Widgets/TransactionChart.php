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
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $labels = [];
        $dataMasuk = [];
        $dataKeluar = [];

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $labels[] = $date->format('d M');

            $masuk = Booking::whereDate('tanggal', $date)
                ->sum('harga');

            $keluar = Transaction::whereDate('tanggal', $date)
                ->where('type', 'keluar')
                ->sum('harga');

            $dataMasuk[] = $masuk;
            $dataKeluar[] = $keluar;
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
