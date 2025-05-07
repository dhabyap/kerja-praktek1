<?php
namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Booking::all(); // or apply any specific filter/logic here
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
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

    /**
     * @param mixed $booking
     * @return array
     */
    public function map($booking): array
    {
        return [
            $booking->nama,
            $booking->tanggal,
            $booking->keterangan,
            $booking->user->name,
            $booking->unit->nama,
            $booking->harga_cash,
            $booking->harga_transfer,
            $booking->unit->appartement->nama,
        ];
    }
}
