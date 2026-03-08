<?php

namespace App\Filament\Resources\PersonalTrainerResource\Pages;

use App\Filament\Resources\PersonalTrainerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonalTrainers extends ListRecords
{
    protected static string $resource = PersonalTrainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
