<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Payment;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DailyPaymentReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.daily-payment-report';

    protected ?string $subheading = 'Daily payment transactions.';

    public function table(Table $table): Table
    {
        return $table
            ->query(Payment::query())
            ->columns([
                TextColumn::make('member.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('cashier.name')
                    ->label('Handled By')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('payment_date')
                    ->form([
                        DatePicker::make('date')->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['date']) {
                            return null;
                        }

                        return 'Date: ' . Carbon::parse($data['date'])->toFormattedDateString();
                    })
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make(),
            ]);
    }
}
