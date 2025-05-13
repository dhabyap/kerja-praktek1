<?php
namespace App\Filament\Widgets;

use App\Models\Appartement;
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

        // Dapatkan daftar appartement
        $appartements = Appartement::query();

        // Filter jika user adalah admin lokal
        if ($user->can('admin-local') && !$user->can('admin-global')) {
            $appartements->where('id', $user->appartement_id);
        }

        $appartements = $appartements->get();

        // Generate labels tanggal
        $labels = [];
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $labels[] = $date->format('d M');
        }

        // Data aggregate untuk semua apartemen
        $dataMasuk = array_fill(0, count($labels), 0);
        $dataKeluar = array_fill(0, count($labels), 0);

        foreach ($appartements as $appartement) {
            foreach ($labels as $index => $label) {
                $date = $startOfMonth->copy()->addDays($index);

                $masukQuery = Booking::whereDate('tanggal', $date)
                    ->whereHas('unit', function ($q) use ($appartement) {
                        $q->where('appartement_id', $appartement->id);
                    });

                $keluarQuery = Transaction::whereDate('tanggal', $date)
                    ->whereHas('unit', function ($q) use ($appartement) {
                        $q->where('appartement_id', $appartement->id);
                    });

                $dataMasuk[$index] += $masukQuery->sum('harga_cash') + $masukQuery->sum('harga_transfer');
                $dataKeluar[$index] += $keluarQuery->sum('harga');
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Uang Masuk',
                    'data' => $dataMasuk,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                ],
                [
                    'label' => 'Total Uang Keluar',
                    'data' => $dataKeluar,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
                ],
            ]
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}