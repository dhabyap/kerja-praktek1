<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TransactionResource;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutatingFormDataBeforeCreate(array $data): array
    {
        $tanggal = now()->format('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $data['kode_invoice'] = "INV-{$tanggal}-{$random}";
        $data['user_id'] = Filament::auth()->user()->id;

        return $data;
    }
}
