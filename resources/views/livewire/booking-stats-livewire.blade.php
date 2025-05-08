<div class="w-full max-w-full px-4">
    <div class="mb-6">
        {{ $form }}
    </div>

    <div class="flex flex-wrap gap-4">
        <!-- Total Booking Hari Ini -->
        <div class="flex-1 min-w-[250px] bg-blue-100 p-4 rounded shadow">
            <h3 class="text-blue-700 text-sm">Total Booking Hari Ini</h3>
            <p class="text-2xl font-bold">{{ $total }}</p>
            <p class="text-xs text-blue-500">{{ $date->format('d M Y') }}</p>
        </div>

        <!-- Total Pemasukan -->
        <div class="flex-1 min-w-[250px] bg-green-100 p-4 rounded shadow">
            <h3 class="text-green-700 text-sm">Total Pemasukan</h3>
            <p class="text-2xl font-bold">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
        </div>

        <!-- Total Cash -->
        <div class="flex-1 min-w-[250px] bg-indigo-100 p-4 rounded shadow">
            <h3 class="text-indigo-700 text-sm">Total Cash</h3>
            <p class="text-2xl font-bold">Rp {{ number_format($cash, 0, ',', '.') }}</p>
        </div>

        <!-- Total Transfer -->
        <div class="flex-1 min-w-[250px] bg-red-100 p-4 rounded shadow">
            <h3 class="text-red-700 text-sm">Total Transfer</h3>
            <p class="text-2xl font-bold">Rp {{ number_format($transfer, 0, ',', '.') }}</p>
        </div>
    </div>
</div>