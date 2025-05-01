<?php
namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class BookingChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Booking Bulanan';

    protected function getData(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $labels = [];
        $data = [];

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $labels[] = $date->format('d M');
            $data[] = Booking::whereDate('tanggal', $date)->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Booking',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
