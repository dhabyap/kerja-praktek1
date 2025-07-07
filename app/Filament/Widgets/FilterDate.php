<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;

class FilterDate extends Widget implements HasForms
{
    use InteractsWithForms;
    
    protected static string $view = 'filament.widgets.booking-stats-header';
    
    public ?int $filterMonth = null;
    public ?int $filterYear = null;
    
    protected function getFormSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('filterMonth')
                        ->label('Bulan')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ])
                        ->default(now()->month)
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->filterMonth = $state;
                            $this->dispatchFilterUpdate();
                        }),

                    Select::make('filterYear')
                        ->label('Tahun')
                        ->options(function () {
                            $currentYear = now()->year;
                            $years = [];
                            for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
                                $years[$i] = $i;
                            }
                            return $years;
                        })
                        ->default(now()->year)
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->filterYear = $state;
                            $this->dispatchFilterUpdate();
                        }),
                ])
        ];
    }
    
    protected function dispatchFilterUpdate(): void
    {
        if ($this->filterMonth && $this->filterYear) {
            $this->dispatch('updateBookingStats', [
                'month' => $this->filterMonth,
                'year' => $this->filterYear
            ]);
        }
    }
    
    public function mount(): void
    {
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
    }
    
    public function getSelectedPeriod(): string
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $monthName = $monthNames[$this->filterMonth] ?? 'Bulan';
        return "{$monthName} {$this->filterYear}";
    }
}