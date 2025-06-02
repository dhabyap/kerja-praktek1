<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class BookingChart extends ChartWidget
{
    public ?int $filterMonth = null;
    public ?int $filterYear = null;

    protected static ?string $heading = null;
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function getListeners()
    {
        return [
            'refreshComponent' => '$refresh',
            'refresh-widgets' => '$refresh',
        ];
    }

    public function mount(): void
    {
        $this->filterMonth = request()->query('filterMonth') ?? now()->month;
        $this->filterYear = request()->query('filterYear') ?? now()->year;
    
        static::$heading = 'Grafik Jumlah Booking ' . Carbon::create($this->filterYear, $this->filterMonth)->translatedFormat('F Y') . ' per Unit';
    }

    protected function getData(): array
    {
        $user = auth()->user();

        $startOfMonth = Carbon::createFromDate($this->filterYear, $this->filterMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $bookingQuery = Booking::with('unit')
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);

        // Filter berdasarkan hak akses user
        if ($user->can('admin-local') || $user->can('admin-global')) {
            $bookingQuery->whereHas('unit', function ($q) use ($user) {
                $q->where('appartement_id', $user->appartement_id);
            });
        }

        // Group berdasarkan unit
        $bookings = $bookingQuery->get()
            ->filter(fn($b) => $b->unit)
            ->groupBy('unit_id');

        // Siapkan data untuk chart
        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];

        $predefinedColors = [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)',
            'rgba(199, 199, 199, 0.6)',
        ];

        $predefinedBorders = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(199, 199, 199, 1)',
        ];

        $i = 0;
        foreach ($bookings as $unitId => $group) {
            $unitName = $group->first()->unit->nama ?? 'Unit Tidak Diketahui';
            $labels[] = $unitName;
            $data[] = $group->count();
            $backgroundColors[] = $predefinedColors[$i % count($predefinedColors)];
            $borderColors[] = $predefinedBorders[$i % count($predefinedBorders)];
            $i++;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Booking ' . Carbon::create($this->filterYear, $this->filterMonth)->translatedFormat('F Y'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
