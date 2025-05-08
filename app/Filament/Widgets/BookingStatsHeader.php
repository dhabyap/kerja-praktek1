<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DatePicker;

class BookingStatsHeader extends Widget implements HasForms
{
    use InteractsWithForms;
    
    protected static string $view = 'filament.widgets.booking-stats-header';
    
    public ?string $filterDate = null;
    
    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('filterDate')
                ->label('Filter Tanggal')
                ->default(now())
                ->displayFormat('d M Y')
                ->closeOnDateSelection()
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    $this->filterDate = $state;
                    $this->dispatch('updateBookingStats', date: $state);
                }),
        ];
    }
    
    public function mount(): void
    {
        $this->filterDate = now()->toDateString();
    }
}