<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TariffResource\Pages;
use App\Models\Tariff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TariffResource extends Resource
{
    protected static ?string $model = Tariff::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Billing Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rate_per_kwh')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->prefix('Rp')
                    ->label('Rate per kWh'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\DateTimePicker::make('effective_from')
                    ->required()
                    ->default(now()),
                Forms\Components\DateTimePicker::make('effective_until'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rate_per_kwh')
                    ->money('IDR')
                    ->sortable()
                    ->label('Rate per kWh'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('effective_from')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_until')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(function (Tariff $record) {
                        $now = now();
                        if (!$record->is_active) return 'Inactive';
                        if ($record->effective_from > $now) return 'Future';
                        if ($record->effective_until && $record->effective_until < $now) return 'Expired';
                        return 'Active';
                    })
                    ->colors([
                        'success' => 'Active',
                        'warning' => 'Future',
                        'danger' => 'Expired',
                        'secondary' => 'Inactive',
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
                Tables\Actions\Action::make('activate')
                    ->label('Set as Active')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Tariff $record) => !$record->is_active)
                    ->action(function (Tariff $record) {
                        // Deactivate all other tariffs
                        Tariff::where('id', '!=', $record->id)->update(['is_active' => false]);
                        // Activate this tariff
                        $record->update(['is_active' => true]);
                    }),
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
            'index' => Pages\ListTariffs::route('/'),
            'create' => Pages\CreateTariff::route('/create'),
            'edit' => Pages\EditTariff::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('manage-tariffs');
    }
}