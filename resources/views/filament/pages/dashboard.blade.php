<x-filament::page>
    @foreach (App\Filament\Pages\Dashboard::getWidgets() as $widget)
    @livewire($widget)
    @endforeach
</x-filament::page>