<?php

namespace App\Filament\Resources\WorkoutPlanResource\Pages;

use App\Filament\Resources\WorkoutPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkoutPlan extends EditRecord
{
    protected static string $resource = WorkoutPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
