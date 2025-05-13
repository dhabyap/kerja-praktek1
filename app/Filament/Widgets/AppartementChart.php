<?php
namespace App\Filament\Widgets;

use App\Models\Appartement;
use App\Models\Booking;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\BarChartWidget;

class AppartementChart extends BarChartWidget
{
    public ?Appartement $appartement = null;

    public function getHeading(): ?string
    {
        return $this->appartement
            ? 'Transaksi Apartemen ' . $this->appartement->nama
            : 'Grafik Transaksi';
    }


    protected function getData(): array
    {
        if (!$this->appartement)
            return [];

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $labels = [];
        $dataMasuk = [];
        $dataKeluar = [];

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $labels[] = $date->format('d M');

            $cash = Booking::whereDate('tanggal', $date)
                ->whereHas('unit', fn($q) => $q->where('appartement_id', $this->appartement->id))
                ->sum(\DB::raw('harga_cash'));

            $tf = Booking::whereDate('tanggal', $date)
                ->whereHas('unit', fn($q) => $q->where('appartement_id', $this->appartement->id))
                ->sum(\DB::raw('harga_transfer'));

            $keluar = Transaction::whereDate('tanggal', $date)
                ->whereHas('unit', fn($q) => $q->where('appartement_id', $this->appartement->id))
                ->sum('harga');

            $dataMasuk[] = $cash + $tf;
            $dataKeluar[] = $keluar;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Uang Masuk',
                    'data' => $dataMasuk,
                    'backgroundColor' => '#3B82F6',
                ],
                [
                    'label' => 'Uang Keluar',
                    'data' => $dataKeluar,
                    'backgroundColor' => '#EF4444',
                ],
            ],
        ];
    }
}
