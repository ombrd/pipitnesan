<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Member;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;

class ExpiredMemberReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.expired-member-report';

    protected ?string $subheading = 'List of members whose membership has expired or who do not have an active membership.';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Member::query()
                    ->whereDate('active_until', '<', Carbon::today())
                    ->orWhereNull('active_until')
            )
            ->columns([
                TextColumn::make('member_number')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('active_until')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make(),
            ]);
    }
}
