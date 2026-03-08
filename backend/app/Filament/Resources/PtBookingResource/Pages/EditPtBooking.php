<?php

namespace App\Filament\Resources\PtBookingResource\Pages;

use App\Filament\Resources\PtBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPtBooking extends EditRecord
{
    protected static string $resource = PtBookingResource::class;

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
