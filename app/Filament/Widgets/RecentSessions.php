<?php

namespace App\Filament\Widgets;

use App\Models\UsageSession;
use App\Models\Pump;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentSessions extends BaseWidget
{
    protected static ?string $heading = 'Recent Usage Sessions';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        
        $query = UsageSession::with(['user', 'pump']);
        
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }
        
        return $table
            ->query($query->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->visible($user->isAdmin()),
                Tables\Columns\TextColumn::make('pump.name')
                    ->label('Pump'),
                Tables\Columns\TextColumn::make('quota_kwh')
                    ->label('Quota')
                    ->suffix(' kWh'),
                Tables\Columns\TextColumn::make('actual_kwh')
                    ->label('Used')
                    ->suffix(' kWh'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'active',
                        'danger' => 'exceeded',
                        'secondary' => 'stopped',
                    ]),
                Tables\Columns\TextColumn::make('cost')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (UsageSession $record): string => route('filament.admin.resources.usage-sessions.edit', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}