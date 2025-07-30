<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PumpResource\Pages;
use App\Models\Pump;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PumpResource extends Resource
{
    protected static ?string $model = Pump::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Equipment Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\TextInput::make('relay_pin')
                    ->numeric()
                    ->placeholder('e.g. 2'),
                Forms\Components\TextInput::make('max_power_kwh')
                    ->numeric()
                    ->step(0.01)
                    ->default(0),
                Forms\Components\Select::make('created_by')
                    ->relationship('createdBy', 'name')
                    ->required()
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('relay_pin')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_power_kwh')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('current_status')
                    ->getStateUsing(fn (Pump $record) => $record->isInUse() ? 'In Use' : 'Available')
                    ->colors([
                        'success' => 'Available',
                        'warning' => 'In Use',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPumps::route('/'),
            'create' => Pages\CreatePump::route('/create'),
            'edit' => Pages\EditPump::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('manage-pumps');
    }
}