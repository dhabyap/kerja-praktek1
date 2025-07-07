<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;
use App\Models\Booking;

class TransaksiStats extends BaseWidget
{
    public ?int $filterMonth = null;
    public ?int $filterYear = null;

    protected $listeners = ['updateBookingStats' => 'updateStats'];

    public function updateStats($data)
    {
        $this->filterMonth = $data['month'] ?? null;
        $this->filterYear = $data['year'] ?? null;

        // Refresh widget setelah filter berubah
        $this->dispatch('$refresh');
    }

    protected function getCards(): array
    {
        $user = auth()->user();
        $now = Carbon::now();

        if ($user->can('admin-local')) {
            // Jika admin-local, return array kosong (hide widget)
            return [];
        }

        // Gunakan filter dari FilterDate widget atau default ke bulan/tahun saat ini
        $selectedMonth = $this->filterMonth ?? $now->month;
        $selectedYear = $this->filterYear ?? $now->year;

        // Buat tanggal berdasarkan filter bulan dan tahun
        $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endDate = Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth();

        $query = Transaction::whereBetween('tanggal', [$startDate, $endDate]);

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $query->whereHas('user', fn($q) => $q->where('appartement_id', $user->appartement_id));
        }

        $transactions = $query->get();

        $transactionsByType = $transactions->groupBy('type');
        $sumsByType = $transactionsByType->map(function ($transactions) {
            return $transactions->sum('harga');
        });

        $cards = [];
        $totalAll = 0;
        $monthName = $startDate->translatedFormat('F Y');

        foreach ($this->getTypeOptions() as $value => $label) {
            $sum = $sumsByType->get($value, 0);
            $totalAll += $sum;

            $cards[] = Card::make("Total Pengeluaran $label", 'Rp ' . number_format($sum, 0, ',', '.'))
                ->color('danger')
                ->description($monthName)
                ->url(route('filament.admin.resources.transactions.index', [
                    'tableFilters[type][value]' => $value,
                    'tableFilters[tanggal_range][tanggal_from]' => $startDate->toDateString(),
                    'tableFilters[tanggal_range][tanggal_until]' => $endDate->toDateString(),
                ]));
        }

        $cards[] = Card::make("Total Pengeluaran", 'Rp ' . number_format($totalAll, 0, ',', '.'))
            ->description($monthName)
            ->color('danger')
            ->url(route('filament.admin.resources.transactions.index', [
                'tableFilters[tanggal_range][tanggal_from]' => $startDate->toDateString(),
                'tableFilters[tanggal_range][tanggal_until]' => $endDate->toDateString(),
            ]));

        return $cards;
    }

    protected function getTypeOptions(): array
    {
        return [
            'token' => 'Token dan Air',
            'sewa_unit' => 'Sewa Unit',
            'gaji' => 'Gaji',
            'lainnya' => 'Lainnya',
        ];
    }

    public function mount(): void
    {
        // Set default values saat widget dimount
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
    }
}