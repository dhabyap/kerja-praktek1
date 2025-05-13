<x-filament::page>
    
    @livewire(\App\Filament\Widgets\DashboardOverview::class)
    @livewire(\App\Filament\Widgets\BookingChart::class)
    @foreach ($appartements as $appartement)
        @livewire(\App\Filament\Widgets\AppartementChart::class, ['appartement' => $appartement])
    @endforeach
</x-filament::page>
