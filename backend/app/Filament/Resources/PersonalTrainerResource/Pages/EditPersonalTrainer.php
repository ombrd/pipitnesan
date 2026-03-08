<?php

namespace App\Filament\Resources\PersonalTrainerResource\Pages;

use App\Filament\Resources\PersonalTrainerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPersonalTrainer extends EditRecord
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
                ->url(fn () => $this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            Actions\DeleteAction::make(),
        ];
    }
}
