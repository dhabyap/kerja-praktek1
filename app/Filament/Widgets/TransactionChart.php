<?php
namespace App\Filament\Widgets;

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
        $data = [];

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $labels[] = $date->format('d M');
            $data[] = Transaction::whereDate('tanggal', $date)->sum('harga');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
