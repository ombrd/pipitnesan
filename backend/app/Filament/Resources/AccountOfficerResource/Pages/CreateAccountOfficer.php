<?php

namespace App\Filament\Resources\AccountOfficerResource\Pages;

use App\Filament\Resources\AccountOfficerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountOfficer extends CreateRecord
{
    protected static string $resource = AccountOfficerResource::class;

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
