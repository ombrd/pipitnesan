<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'System Settings';
    protected static ?string $title = 'System Settings';

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'auto_checkout_time' => Setting::getValue('auto_checkout_time', '23:00'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('auto_checkout_time')
                    ->label('Auto Checkout Time (HH:MM)')
                    ->placeholder('e.g., 23:00')
                    ->required()
                    ->regex('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/')
                    ->helperText('The time at which members who have checked in but not checked out will be automatically checked out (24-hour format).'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('saveSettings'),
        ];
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();

        Setting::setValue('auto_checkout_time', $data['auto_checkout_time']);

        Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }
}
