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

        $result = [];
        $labels = [];

        // Generate labels tanggal
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $labels[] = $date->format('d M');
        }

        foreach ($appartements as $appartement) {
            $dataMasuk = [];
            $dataKeluar = [];

            for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
                // Query uang masuk (booking)
                $masukQuery = Booking::whereDate('tanggal', $date)
                    ->whereHas('unit', function ($q) use ($appartement) {
                        $q->where('appartement_id', $appartement->id);
                    });

                // Query uang keluar (transaction)
                $keluarQuery = Transaction::whereDate('tanggal', $date)
                    ->whereHas('unit', function ($q) use ($appartement) {
                        $q->where('appartement_id', $appartement->id);
                    });

                $dataMasuk[] = $masukQuery->sum('harga_cash') + $masukQuery->sum('harga_transfer');
                $dataKeluar[] = $keluarQuery->sum('harga');
            }

            $result[] = [
                'appartement' => $appartement->nama,
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Uang Masuk',
                        'data' => $dataMasuk,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    ],
                    [
                        'label' => 'Uang Keluar',
                        'data' => $dataKeluar,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
                    ],
                ]
            ];
        }

        return $result;
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
