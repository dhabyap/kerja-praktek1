<?php
namespace App\Filament\Pages;

use App\Filament\Widgets\BookingChart;
use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\TransactionChart;
use Filament\Pages\Page;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Booking;

class Dashboard extends Page
{
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-home';


    public function mount()
    {
        // Bisa tambah data tambahan jika diperlukan
    }

    public static function getWidgets(): array
    {
        return [
            DashboardOverview::class,
            TransactionChart::class,
            BookingChart::class,
        ];
    }

}
