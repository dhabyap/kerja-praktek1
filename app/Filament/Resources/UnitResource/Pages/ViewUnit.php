<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use App\Filament\Widgets\BookingListWidget;
use App\Filament\Widgets\TransaksiListWidget;
use App\Filament\Widgets\TransaksiStats;
use Filament\Resources\Pages\ViewRecord;

class ViewUnit extends ViewRecord
{
    protected static string $resource = UnitResource::class;

    protected function getFooterWidgets(): array
    {

        return [
            TransaksiListWidget::make(['unitId' => $this->record->id]),
            BookingListWidget::make(['unitId' => $this->record->id]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
