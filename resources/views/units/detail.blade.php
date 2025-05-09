@extends('layouts.app')

@section('content')
<div class="container py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Unit: {{ $unit->nama }}</h1>
        <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h2 class="text-xl font-semibold mb-4">Informasi Unit</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Nama Unit:</span> {{ $unit->nama }}</p>
                    <p><span class="font-medium">Apartemen:</span> {{ $unit->appartement->nama }}</p>
                    <p><span class="font-medium">Tipe:</span> {{ $unit->type ?? '-' }}</p>
                    <p><span class="font-medium">Status:</span> {{ $unit->status ?? '-' }}</p>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4">Statistik Keuangan</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Total Booking:</span> {{ $unit->bookings->count() }}</p>
                    <p><span class="font-medium">Pendapatan Cash:</span> Rp {{
                        number_format($unit->bookings->sum('harga_cash'), 0, ',', '.') }}</p>
                    <p><span class="font-medium">Pendapatan Transfer:</span> Rp {{
                        number_format($unit->bookings->sum('harga_transfer'), 0, ',', '.') }}</p>
                    <p><span class="font-medium">Total Pendapatan:</span> Rp {{
                        number_format($unit->bookings->sum('harga_cash') + $unit->bookings->sum('harga_transfer'), 0,
                        ',', '.') }}</p>
                    <p><span class="font-medium">Pengeluaran:</span> Rp {{
                        number_format($unit->transactions->sum('harga'), 0, ',', '.') }}</p>
                    <p><span class="font-medium">Keuntungan:</span> Rp {{ number_format(max(0,
                        ($unit->bookings->sum('harga_cash') + $unit->bookings->sum('harga_transfer')) -
                        $unit->transactions->sum('harga')), 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Daftar Booking</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border">Tanggal</th>
                        <th class="py-2 px-4 border">Nama Penyewa</th>
                        <th class="py-2 px-4 border">Cash</th>
                        <th class="py-2 px-4 border">Transfer</th>
                        <th class="py-2 px-4 border">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unit->bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border">
                            {{ \Carbon\Carbon::parse($booking->tanggal)->format('d M Y') }}
                        </td>
                        <td class="py-2 px-4 border">{{ $booking->nama_penyewa }}</td>
                        <td class="py-2 px-4 border">Rp {{ number_format($booking->harga_cash, 0, ',', '.') }}</td>
                        <td class="py-2 px-4 border">Rp {{ number_format($booking->harga_transfer, 0, ',', '.') }}</td>
                        <td class="py-2 px-4 border">{{ $booking->status }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 px-4 border text-center">Tidak ada data booking</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Daftar Pengeluaran</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 border">Tanggal</th>
                        <th class="py-2 px-4 border">Jenis</th>
                        <th class="py-2 px-4 border">Keterangan</th>
                        <th class="py-2 px-4 border">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unit->transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border">{{ \Carbon\Carbon::parse($transaction->tanggal)->format('d M Y') }}
                        </td>
                        <td class="py-2 px-4 border">{{ $transaction->type }}</td>
                        <td class="py-2 px-4 border">{{ $transaction->keterangan }}</td>
                        <td class="py-2 px-4 border">Rp {{ number_format($transaction->harga, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 px-4 border text-center">Tidak ada data transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection