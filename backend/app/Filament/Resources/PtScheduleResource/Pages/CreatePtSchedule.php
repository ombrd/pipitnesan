<?php

namespace App\Filament\Resources\PtScheduleResource\Pages;

use App\Filament\Resources\PtScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePtSchedule extends CreateRecord
{
    protected static string $resource = PtScheduleResource::class;

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
