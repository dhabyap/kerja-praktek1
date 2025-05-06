<!DOCTYPE html>
<html>

<head>
    <title>Invoice {{ $booking->kode_invoice }}</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Invoice Booking</h2>
    <p><strong>Kode Invoice:</strong> {{ $booking->kode_invoice }}</p>
    <p><strong>Nama:</strong> {{ $booking->nama }}</p>
    <p><strong>Tanggal:</strong> {{ $booking->tanggal }}</p>
    <p><strong>Unit:</strong> {{ $booking->unit->nama ?? '-' }}</p>
    <p><strong>User:</strong> {{ $booking->user->name ?? '-' }}</p>

    <h3>Transaksi:</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->transactions as $i => $transaksi)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $transaksi->tanggal }}</td>
                <td>{{ $transaksi->keterangan }}</td>
                <td>Rp {{ number_format($transaksi->harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total:</strong> Rp {{ number_format($booking->transactions->sum('harga'), 0, ',', '.') }}</p>
</body>

</html>