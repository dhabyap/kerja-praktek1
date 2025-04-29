<?php

namespace App\Filament\Resources\TransactionRelationManagerResource\Pages;

use App\Filament\Resources\TransactionRelationManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransactionRelationManager extends EditRecord
{
    protected static string $resource = TransactionRelationManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

}
