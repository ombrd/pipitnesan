<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use App\Models\Payment;
use App\Models\PtBooking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Active Members', Member::where('active_until', '>=', Carbon::today())->count()),
            Stat::make('Payments Today', 'Rp ' . number_format(Payment::whereDate('payment_date', Carbon::today())->sum('amount'), 0, ',', '.')),
            Stat::make('PT Bookings Today', PtBooking::whereHas('schedule', function($q) {
                $q->whereDate('date', Carbon::today());
            })->count()),
        ];
    }
}
