<?php

namespace App\Filament\Resources\TransactionRelationManagerResource\Pages;

use App\Filament\Resources\TransactionRelationManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactionRelationManagers extends ListRecords
{
    protected static string $resource = TransactionRelationManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

}
