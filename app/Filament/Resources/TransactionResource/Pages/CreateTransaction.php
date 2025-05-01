<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['kode_invoice'] = '';
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        $tanggal = now()->format('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $kodeInvoice = "INV-{$tanggal}-{$random}.{$record->id}";

            $record->update([
            'kode_invoice' => $kodeInvoice,
        ]);
    }
}