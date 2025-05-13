<?php
namespace App\Filament\Pages;

use App\Filament\Widgets\AppartementChart;
use App\Filament\Widgets\BookingChart;
use App\Filament\Widgets\DashboardOverview;
use App\Models\Appartement;
use Filament\Pages\Page;
use Livewire\Livewire;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';


    public static function getWidgets(): array
    {
        $user = auth()->user();

        $appartements = Appartement::query()
            ->when(
                $user->can('admin-local') && !$user->can('admin-global'),
                fn($q) => $q->where('id', $user->appartement_id)
            )
            ->get();

        $charts = $appartements->map(function ($appartement) {
            return Livewire::mount(AppartementChart::class, [
                'appartement' => $appartement,
            ])->getId();

        })->toArray();

        return array_merge(
            [
                DashboardOverview::class,
                BookingChart::class,
            ],
            $charts
        );
    }

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'appartements' => Appartement::query()
                ->when(
                    $user->can('admin-local') && !$user->can('admin-global'),
                    fn($q) => $q->where('id', $user->appartement_id)
                )
                ->get()
        ];
    }


    public function getWidgetData(): array
    {
        $user = auth()->user();

        return [
            'appartements' => Appartement::query()
                ->when(
                    $user->can('admin-local') && !$user->can('admin-global'),
                    fn($q) => $q->where('id', $user->appartement_id)
                )
                ->get()
        ];
    }


}