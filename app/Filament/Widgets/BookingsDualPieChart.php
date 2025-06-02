<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BookingsDualPieChart extends Widget
{

    protected static string $view = 'filament.widgets.booking-dual-pie-chart';

    public function getViewData(): array
    {
        $month = request()->query('filterMonth') ?? now()->month;
        $year = request()->query('filterYear') ?? now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $bookings = DB::table('bookings')
            ->select('keterangan', 'waktu', DB::raw('count(*) as total'))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('keterangan', 'waktu')
            ->get();

        $keteranganGrouped = $bookings->groupBy('keterangan')->map(fn($group) => $group->sum('total'));
        $keteranganData = [
            'labels' => $keteranganGrouped->keys()->values(),
            'data' => $keteranganGrouped->values(),
        ];

        $waktuGrouped = $bookings->groupBy('waktu')->map(fn($group) => $group->sum('total'));
        $waktuData = [
            'labels' => $waktuGrouped->keys()->values(),
            'data' => $waktuGrouped->values(),
        ];

        return compact('keteranganData', 'waktuData');
    }

}

