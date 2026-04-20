<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * Class MemberResource
 * 
 * Mengelola pembuatan, pengeditan, dan list direktori Member (pelanggan gym).
 * Termasuk interaktivitas select berjenjang (seperti memilih Cabang lalu menampilkan Promo dan AO khusus cabang tersebut).
 *
 * @package App\Filament\Resources
 */
class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('member_number')
                    ->default(fn () => 'MBR' . strtoupper(Str::random(11)))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel(),
                Forms\Components\TextInput::make('email')
                    ->email(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Forms\Components\TextInput::make('id_card_number')
                    ->label('ID Card/KTP')
                    ->required(),
                Forms\Components\DatePicker::make('active_until')
                    ->disabled()
                    ->dehydrated(false)
                    ->hidden(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->live()
                    ->preload(),
                Forms\Components\Textarea::make('address')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('birth_place'),
                Forms\Components\DatePicker::make('birth_date'),
                Forms\Components\Select::make('account_officer_code')
                    ->label('Account Officer')
                    ->options(function (Forms\Get $get) {
                        $branchId = $get('branch_id');
                        if (!$branchId) {
                            return [];
                        }
                        return \App\Models\AccountOfficer::where('branch_id', $branchId)
                            ->pluck('name', 'code');
                    })
                    ->disabled(fn (Forms\Get $get): bool => ! filled($get('branch_id')))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('promotion_id')
                    ->label('Promotion Package')
                    ->options(function (Forms\Get $get) {
                        $branchId = $get('branch_id');
                        if (!$branchId) {
                            return [];
                        }
                        return \App\Models\Promotion::where('status', true)
                            ->where('branch_id', $branchId)
                            ->pluck('name', 'id');
                    })
                    ->disabled(fn (Forms\Get $get): bool => ! filled($get('branch_id')))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $promo = \App\Models\Promotion::find($state);
                            if ($promo) {
                                $set('total_payment', $promo->price);
                            }
                        } else {
                            $set('total_payment', null);
                        }
                    }),
                Forms\Components\TextInput::make('total_payment')
                    ->label('Total Payment')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Select::make('personal_trainer_code')
                    ->label('Personal Trainer Code')
                    ->options(function (Forms\Get $get) {
                        $branchId = $get('branch_id');
                        if (!$branchId) {
                            return [];
                        }
                        return \App\Models\PersonalTrainer::where('status', true)
                            ->where('branch_id', $branchId)
                            ->pluck('name', 'code');
                    })
                    ->disabled(fn (Forms\Get $get): bool => ! filled($get('branch_id')))
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_card_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('account_officer_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_payment')
                    ->money('idr')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'primary' => 'active',
                        'warning' => 'expired',
                        'danger' => 'inactive',
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('active_until')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('personal_trainer_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
