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

    public ?int $filterMonth = null;
    public ?int $filterYear = null;

    public function mount(): void
    {
        $this->filterMonth = request()->query('filterMonth') ?? now()->month;
        $this->filterYear = request()->query('filterYear') ?? now()->year;

        static::$heading = 'Grafik Transaksi Apartemen ' . ($this->appartement ? $this->appartement->nama . ' ' : '')
            . Carbon::create($this->filterYear, $this->filterMonth)->translatedFormat('F Y');
    }

    public function getHeading(): ?string
    {
        return static::$heading;
    }

    protected function getData(): array
    {
        if (!$this->appartement) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $startOfMonth = Carbon::create($this->filterYear, $this->filterMonth, 1)->startOfMonth();
        $endOfMonth = Carbon::create($this->filterYear, $this->filterMonth, 1)->endOfMonth();

        $labels = [];
        $dataMasuk = [];
        $dataKeluar = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $labels[] = $date->format('d M');

            $cash = Booking::whereDate('tanggal', $date)
                ->whereHas('unit', fn($q) => $q->where('appartement_id', $this->appartement->id))
                ->sum('harga_cash');

            $tf = Booking::whereDate('tanggal', $date)
                ->whereHas('unit', fn($q) => $q->where('appartement_id', $this->appartement->id))
                ->sum('harga_transfer');

            $keluar = Transaction::whereDate('tanggal', $date)
                ->whereHas('user', fn($q) => $q->where('appartement_id', $this->appartement->id))
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
