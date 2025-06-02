<x-filament-panels::page>
    <form wire:submit.prevent="filterData">
        <div class="flex items-end gap-4 mb-6">
            <div class="w-48">
                {{ $this->form->getComponent('filterMonth') }}
            </div>
            <div class="w-48">
                {{ $this->form->getComponent('filterYear') }}
            </div>
            <x-filament::button type="submit">
                Terapkan Filter
            </x-filament::button>
        </div>
    </form>

    @livewire(\App\Filament\Widgets\BookingsDualPieChart::class, ['filterMonth' => $filterMonth, 'filterYear' =>
    $filterYear])

    <div class="mt-6">
        @livewire(\App\Filament\Widgets\DashboardOverview::class, ['filterMonth' => $filterMonth, 'filterYear' =>
        $filterYear])
    </div>
    <div class="mt-6">
        @livewire(\App\Filament\Widgets\BookingChart::class, ['filterMonth' => $filterMonth, 'filterYear' =>
        $filterYear])
    </div>
    <div class="mt-2">
        @foreach ($appartements as $appartement)
        <div class="mt-5">
            @livewire(\App\Filament\Widgets\AppartementChart::class, ['appartement' => $appartement, 'filterMonth' =>
            $filterMonth, 'filterYear' => $filterYear])
        </div>
        @endforeach
    </div>
</x-filament-panels::page>