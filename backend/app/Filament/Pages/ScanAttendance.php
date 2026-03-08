<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use App\Models\Member;
use App\Models\ActivityLog;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Exception;
use Carbon\Carbon;

class ScanAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationLabel = 'Scan Attendance';
    protected static ?string $title = 'Member Attendance Scanner';

    protected static string $view = 'filament.pages.scan-attendance';

    public ?array $data = [];
    public ?Member $scannedMember = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('barcode')
                    ->label('Scan Member Barcode')
                    ->placeholder('Awaiting scanner input...')
                    ->autofocus()
                    ->required()
            ])
            ->statePath('data');
    }

    public function scanBarcode()
    {
        $barcode = $this->data['barcode'] ?? null;
        
        if (!$barcode) {
            return;
        }

        try {
            // Attempt to parse the JWT
            $token = JWTAuth::setToken($barcode);
            $payload = $token->getPayload();
            
            if ($payload->get('purpose') !== 'attendance') {
                throw new Exception("Invalid QR purpose.");
            }

            $memberNumber = $payload->get('member_number');
            $this->scannedMember = Member::where('member_number', $memberNumber)->first();

        } catch (Exception $e) {
            Notification::make()
                ->title('Invalid or Expired QR Code')
                ->body('The scanned QR code is either not recognized or has expired (1-minute limit).')
                ->danger()
                ->send();
            
            $this->form->fill();
            $this->dispatch('focus-barcode');
            return;
        }

        if (!$this->scannedMember) {
            Notification::make()
                ->title('Member Not Found')
                ->body("No member found matching this QR code.")
                ->danger()
                ->send();
            
            $this->form->fill();
            $this->dispatch('focus-barcode');
            return;
        }

        // Prevent spam scanning (e.g., duplicate scans within 1 minute)
        $recentLog = ActivityLog::where('user_id', $this->scannedMember->id)
            ->where('action', 'gym_attendance')
            ->where('created_at', '>=', Carbon::now()->subMinutes(1))
            ->first();

        if (!$recentLog) {
            ActivityLog::create([
                'user_id' => $this->scannedMember->id,
                'action' => 'gym_attendance',
                'description' => 'Attendance recorded successfully at ' . now()->format('Y-m-d H:i:s')
            ]);

            Notification::make()
                ->title('Attendance Confirmed')
                ->body("Attendance recorded for {$this->scannedMember->name}.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Already Scanned')
                ->body("Attendance for {$this->scannedMember->name} was just recorded.")
                ->warning()
                ->send();
        }

        $this->dispatch('scan-success');
    }

    public function clearScan()
    {
        $this->scannedMember = null;
        $this->form->fill();
        $this->dispatch('focus-barcode');
    }
}
