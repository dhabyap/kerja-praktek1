<?php

namespace App\Exports;

use App\Models\Booking;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingsExport implements FromCollection, WithHeadings
{
    protected $bookings;

    public function __construct($bookings)
    {
        $this->bookings = $bookings;
    }

    public function collection()
    {
        return $this->bookings->map(function ($booking, $index) {
            return [
                $index + 1,
                $booking->nama,
                (new Carbon($booking->tanggal))->format('Y-m-d'),
                $booking->keterangan,
                $booking->user->name,
                $booking->unit->nama,
                $booking->harga_cash,
                $booking->harga_transfer,
                $booking->unit->appartement->nama,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Tanggal',
            'Keterangan',
            'Nama Admin',
            'Unit',
            'Harga Cash',
            'Harga Transfer',
            'Nama Appartement',
        ];
    }
}
