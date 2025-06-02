<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BookingsDualPieChart;
use Filament\Forms\Components\Grid;
use Filament\Pages\Page;
use App\Filament\Widgets\AppartementChart;
use App\Filament\Widgets\BookingChart;
use App\Filament\Widgets\DashboardOverview;
use Filament\Forms\Components\Select;
use App\Models\Appartement;
use Livewire\Livewire;
use Illuminate\Support\Carbon;

class RekapReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.rekap-report';
    protected static ?string $title = 'Laporan Rekap';
    protected static ?string $navigationLabel = 'Laporan Rekap';
    protected static ?string $pluralModelLabel = 'Laporan Rekap';
    protected static ?string $navigationGroup = 'Laporan';

    public ?Appartement $appartement = null;

    public ?int $filterMonth = null;
    public ?int $filterYear = null;

    protected $listeners = ['updateFilter'];



    public function updateFilter($month, $year)
    {
        $this->filterMonth = $month;
        $this->filterYear = $year;

        $this->loadChartData();
    }

    public function mount()
    {
        $this->filterMonth = request()->query('filterMonth', now()->month);
        $this->filterYear = request()->query('filterYear', now()->year);
    }

    protected function getFormSchema(): array
    {
        $fields = [
            Select::make('filterMonth')
                ->label('Bulan')
                ->options(
                    collect(range(1, 12))->mapWithKeys(
                        fn($m) => [$m => Carbon::create()->month($m)->locale('id')->translatedFormat('F')]
                    )->toArray()
                )
                ->default($this->filterMonth),

            Select::make('filterYear')
                ->label('Tahun')
                ->options(array_combine(range(2023, now()->year), range(2023, now()->year)))
                ->default($this->filterYear),
        ];

        return [
            Grid::make(3)->schema($fields),
        ];
    }

    public static function getWidgets(): array
    {
        $user = auth()->user();

        $appartementsQuery = Appartement::query();

        if ($user->can('admin-local') || $user->can('admin-global')) {
            $appartementsQuery->where('id', $user->appartement_id);
        }

        $appartements = $appartementsQuery->get();

        $charts = $appartements->map(function ($appartement) {
            return Livewire::mount(AppartementChart::class, [
                'appartement' => $appartement,
            ])->getId();
        })->toArray();

        return array_merge(
            [
                BookingsDualPieChart::class,
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
                    $user->can('admin-local') || $user->can('admin-global'),
                    fn($q) => $q->where('id', $user->appartement_id)
                )
                ->get()
        ];
    }

    public function filterData()
    {
        return redirect()->to('/admin/rekap-report?' . http_build_query([
            'filterMonth' => $this->filterMonth,
            'filterYear' => $this->filterYear,
        ]));
    }


    public static function canViewAny(): bool
    {
        return auth()->user()->can('super-admin') || auth()->user()->can('admin-global');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('super-admin') || auth()->user()->can('admin-global');
    }
}
