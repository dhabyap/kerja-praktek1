<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item, $index) {
            return [
                'No' => $index + 1,
                'Kode Invoice' => $item->kode_invoice,
                'Tanggal' => $item->tanggal,
                'Unit' => $item->unit?->nama ?? '-',
                'Appartement' => $item->unit?->appartement?->nama ?? '-',
                'Nama Admin' => $item->user?->name ?? '-',
                'Harga' => $item->harga,
                'Tipe Pembayaran' => $item->tipe_pembayaran,
                'Keterangan' => $item->keterangan,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Invoice',
            'Tanggal',
            'Unit',
            'Appartement',
            'Nama Admin',
            'Harga',
            'Tipe Pembayaran',
            'Keterangan',
        ];
    }
}