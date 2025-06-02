@php
use Carbon\Carbon;

$bulanTahun = Carbon::createFromDate(request()->query('filterYear', now()->year), request()->query('filterMonth',
now()->month))->translatedFormat('F Y');
@endphp

<x-filament::widget>
    <x-filament::card>
        <h2 class="text-xl font-bold mb-1 text-center">Statistik Tipe Booking</h2>
        <p class="text-sm text-center text-gray-600 mb-4">Periode: {{ $bulanTahun }}</p>
        <div class="flex flex-wrap gap-3 justify-center m-2">
            {{-- Chart: Tipe Keterangan --}}
            <div class="w-full md:w-1/2 max-w-md border border-gray-300 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4 text-center border-b pb-2">Tipe Keterangan</h3>
                <div class="aspect-w-1 aspect-h-1">
                    <canvas id="keteranganChart"></canvas>
                </div>
            </div>

            {{-- Chart: Tipe Waktu --}}
            <div class="w-full md:w-1/2 max-w-md border border-gray-300 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4 text-center border-b pb-2">Tipe Waktu</h3>
                <div class="aspect-w-1 aspect-h-1">
                    <canvas id="waktuChart"></canvas>
                </div>
            </div>
        </div>
    </x-filament::card>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const keteranganCtx = document.getElementById('keteranganChart');
        new Chart(keteranganCtx, {
            type: 'pie',
            data: {
                labels: @json($keteranganData['labels']),
                datasets: [{
                    data: @json($keteranganData['data']),
                    backgroundColor: ['#fbbf24', '#34d399', '#60a5fa'],
                }]
            }
        });

        const waktuCtx = document.getElementById('waktuChart');
        new Chart(waktuCtx, {
            type: 'pie',
            data: {
                labels: @json($waktuData['labels']),
                datasets: [{
                    data: @json($waktuData['data']),
                    backgroundColor: ['#f5e44c', '#54523e'],
                }]
            }
        });
    </script>
    @endpush
</x-filament::widget>