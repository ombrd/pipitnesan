<?php

namespace App\Filament\Resources\AccountOfficerResource\Pages;

use App\Filament\Resources\AccountOfficerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountOfficers extends ListRecords
{
    protected static string $resource = AccountOfficerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
