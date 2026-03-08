<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PtBookingResource\Pages;
use App\Filament\Resources\PtBookingResource\RelationManagers;
use App\Models\PtBooking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PtBookingResource extends Resource
{
    protected static ?string $model = PtBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Transactions';

    protected static ?string $modelLabel = 'Personal Training Booking';
    protected static ?string $navigationLabel = 'Personal Training Bookings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('member_id')
                    ->relationship('member', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('pt_schedule_id')
                    ->relationship('schedule', 'date')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'booked' => 'Booked',
                        'cancelled' => 'Cancelled',
                        'done' => 'Done',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schedule.date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPtBookings::route('/'),
            'create' => Pages\CreatePtBooking::route('/create'),
            'edit' => Pages\EditPtBooking::route('/{record}/edit'),
        ];
    }
}
