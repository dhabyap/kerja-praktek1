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
        $user = auth()->user();

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $labels = [];
        $data = [];

        // Ambil semua data booking untuk bulan ini terlebih dahulu
        $bookingQuery = Booking::whereBetween('tanggal', [$startOfMonth, $endOfMonth]);

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $bookingQuery->whereHas('unit', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });
        }

        $bookings = $bookingQuery->get()->groupBy(function ($item) {
            return Carbon::parse($item->tanggal)->format('Y-m-d');
        });

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');

            $data[] = isset($bookings[$key]) ? count($bookings[$key]) : 0;
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
