<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkoutPlanResource\Pages;
use App\Models\WorkoutPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkoutPlanResource extends Resource
{
    protected static ?string $model = WorkoutPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Gym Management';
    
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Select::make('goal')
                            ->required()
                            ->options([
                                'weight_loss' => 'Weight Loss',
                                'muscle_gain' => 'Muscle Gain',
                                'endurance' => 'Endurance',
                                'maintenance' => 'Maintenance',
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Target Audience Filtering (Optional)')
                    ->schema([
                        Forms\Components\TextInput::make('min_age')
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('max_age')
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('min_bmi')
                            ->numeric()
                            ->step(0.1)
                            ->label('Min BMI'),
                        Forms\Components\TextInput::make('max_bmi')
                            ->numeric()
                            ->step(0.1)
                            ->label('Max BMI'),
                    ])->columns(4),

                Forms\Components\Section::make('7-Day Schedule')
                    ->schema([
                        Forms\Components\Repeater::make('schedule')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('day')
                                            ->options([1=>'Day 1', 2=>'Day 2', 3=>'Day 3', 4=>'Day 4', 5=>'Day 5', 6=>'Day 6', 7=>'Day 7'])
                                            ->required(),
                                        Forms\Components\TextInput::make('focus')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Toggle::make('is_rest')
                                            ->label('Is Rest Day?')
                                            ->reactive(),
                                    ]),
                                Forms\Components\Repeater::make('exercises')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')->required(),
                                        Forms\Components\TextInput::make('sets')->required(),
                                        Forms\Components\TextInput::make('reps')->required(),
                                        Forms\Components\TextInput::make('rest_time')->required(),
                                    ])
                                    ->columns(4)
                                    ->hidden(fn (Forms\Get $get) => $get('is_rest'))
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                            ])
                            ->defaultItems(7)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 'Day ' . ($state['day'] ?? '?') . ' - ' . ($state['focus'] ?? ''))
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('goal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->searchable(),
                Tables\Columns\TextColumn::make('min_age')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_age')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_bmi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_bmi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => Pages\ListWorkoutPlans::route('/'),
            'create' => Pages\CreateWorkoutPlan::route('/create'),
            'edit' => Pages\EditWorkoutPlan::route('/{record}/edit'),
        ];
    }
}
