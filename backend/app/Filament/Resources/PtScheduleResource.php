<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PtScheduleResource\Pages;
use App\Filament\Resources\PtScheduleResource\RelationManagers;
use App\Models\PtSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Class PtScheduleResource
 * 
 * Mengatur slot jadwal dari Personal Trainer (tanggal, jam mulai, jam selesai, dan kuota peserta).
 *
 * @package App\Filament\Resources
 */
class PtScheduleResource extends Resource
{
    protected static ?string $model = PtSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Transactions';

    protected static ?string $modelLabel = 'Personal Training Schedule';
    protected static ?string $navigationLabel = 'Personal Training Schedules';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('personal_trainer_id')
                    ->relationship('trainer', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('time_start')
                    ->required(),
                Forms\Components\TextInput::make('time_end')
                    ->required(),
                Forms\Components\TextInput::make('quota')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trainer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_start'),
                Tables\Columns\TextColumn::make('time_end'),
                Tables\Columns\TextColumn::make('quota')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListPtSchedules::route('/'),
            'create' => Pages\CreatePtSchedule::route('/create'),
            'edit' => Pages\EditPtSchedule::route('/{record}/edit'),
        ];
    }
}
