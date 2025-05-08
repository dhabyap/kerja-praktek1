<x-filament-panels::page>
    <div class="space-y-4">
        <h1 class="text-xl font-bold">Laporan Keuntungan & Kerugian per Appartement</h1>

        <form method="GET" action="">
            <div class="flex items-end gap-4">
                <!-- Filter Tanggal Mulai -->
                <div class="w-48">
                    <label for="filterStartDate"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal Mulai</label>
                    <input type="date" id="filterStartDate" name="filterStartDate"
                        value="{{ request('filterStartDate') }}"
                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                </div>

                <!-- Filter Tanggal Akhir -->
                <div class="w-48">
                    <label for="filterEndDate"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal Akhir</label>
                    <input type="date" id="filterEndDate" name="filterEndDate" value="{{ request('filterEndDate') }}"
                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                </div>

                <!-- Filter Appartement -->
                <div class="w-64">
                    <label for="filterAppartement"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200">Appartement</label>
                    <select id="filterAppartement" name="filterAppartement"
                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        <option value="">Semua Appartement</option>
                        @foreach(\App\Models\Appartement::all() as $appartement)
                        <option value="{{ $appartement->id }}" {{ request('filterAppartement')==$appartement->id ?
                            'selected' : '' }}>
                            {{ $appartement->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <x-filament::button type="submit">
                    Terapkan Filter
                </x-filament::button>
            </div>
        </form>

        {{ $this->table }}
    </div>
</x-filament-panels::page>