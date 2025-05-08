<x-filament-panels::page>
    <div class="space-y-4">
        <h1 class="text-xl font-bold">Laporan Keuntungan & Kerugian per Unit</h1>

        <form wire:submit.prevent="filterData">
            <div class="flex items-end gap-4">
                <div class="w-48">
                    {{ $this->form->getComponent('filterMonth') }}
                </div>
                <div class="w-48">
                    {{ $this->form->getComponent('filterYear') }}
                </div>
                <div class="w-48">
                    {{ $this->form->getComponent('filterAppartement') }}
                </div>
                <x-filament::button type="submit">
                    Terapkan Filter
                </x-filament::button>
            </div>
        </form>

        {{ $this->table }}
    </div>
</x-filament-panels::page>