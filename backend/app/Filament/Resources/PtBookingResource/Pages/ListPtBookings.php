<?php

namespace App\Filament\Resources\PtBookingResource\Pages;

use App\Filament\Resources\PtBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPtBookings extends ListRecords
{
    protected static string $resource = PtBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
