<?php

namespace App\Filament\Resources\BookingResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\BookingResource;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kode_booking'] = 'BOOK-' . strtoupper(Str::random(6));
        $data['user_id'] = Filament::auth()->user()->id;

        return $data;
    }

}
