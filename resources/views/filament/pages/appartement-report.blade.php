<x-filament-panels::page>
    <div class="space-y-4">
        <h1 class="text-xl font-bold">Laporan Keuntungan & Kerugian per Appartement</h1>

        <form method="GET" action="">
            <div class="flex items-end gap-4">
                <div class="w-48">
                    <label for="filterMonth"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200">Bulan</label>
                    <select id="filterMonth" name="filterMonth"
                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" {{ request('filterMonth', now()->month) == $month ? 'selected' : ''
                            }}>
                            {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-48">
                    <label for="filterYear"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tahun</label>
                    <select id="filterYear" name="filterYear"
                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        @foreach(range(2023, now()->year + 1) as $year)
                        <option value="{{ $year }}" {{ request('filterYear', now()->year) == $year ? 'selected' : '' }}>
                            {{ $year }}
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