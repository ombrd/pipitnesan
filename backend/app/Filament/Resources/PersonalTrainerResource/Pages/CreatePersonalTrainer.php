<?php

namespace App\Filament\Resources\PersonalTrainerResource\Pages;

use App\Filament\Resources\PersonalTrainerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonalTrainer extends CreateRecord
{
    protected static string $resource = PersonalTrainerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Kembali (Back)')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
