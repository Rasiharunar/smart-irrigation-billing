<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsageSessionResource\Pages;
use App\Models\UsageSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsageSessionResource extends Resource
{
    protected static ?string $model = UsageSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    protected static ?string $navigationGroup = 'Usage Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('pump_id')
                    ->relationship('pump', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('quota_kwh')
                    ->required()
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0.1)
                    ->maxValue(100),
                Forms\Components\TextInput::make('actual_kwh')
                    ->required()
                    ->numeric()
                    ->step(0.0001)
                    ->minValue(0)
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'stopped' => 'Stopped',
                        'exceeded' => 'Exceeded',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('started_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('ended_at'),
                Forms\Components\TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('tariff_rate')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->prefix('Rp'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pump.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quota_kwh')
                    ->numeric()
                    ->suffix(' kWh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_kwh')
                    ->numeric()
                    ->suffix(' kWh')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'active',
                        'danger' => 'exceeded',
                        'secondary' => 'stopped',
                    ]),
                Tables\Columns\TextColumn::make('usage_percentage')
                    ->label('Usage %')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'stopped' => 'Stopped',
                        'exceeded' => 'Exceeded',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('pump_id')
                    ->relationship('pump', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
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
            'index' => Pages\ListUsageSessions::route('/'),
            'create' => Pages\CreateUsageSession::route('/create'),
            'edit' => Pages\EditUsageSession::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is not admin, only show their own sessions
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        
        return $query;
    }
}