<?php

namespace App\Filament\Resources\PtScheduleResource\Pages;

use App\Filament\Resources\PtScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPtSchedules extends ListRecords
{
    protected static string $resource = PtScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
