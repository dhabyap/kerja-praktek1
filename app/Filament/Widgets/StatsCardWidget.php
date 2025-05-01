<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\View\View;
use App\Models\Transaction;
use App\Models\User;

class StatsCard extends Widget
{
    public function render(): View
    {
        return view('filament.widgets.stats-card', [
            'totalTransactions' => Transaction::count(),
            'totalUsers' => User::count(),
        ]);
    }
}
