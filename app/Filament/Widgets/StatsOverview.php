<?php

namespace App\Filament\Widgets;

use App\Models\UsageSession;
use App\Models\Pump;
use App\Models\User;
use App\Models\Billing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return [
                Stat::make('Total Pumps', Pump::count())
                    ->description('Registered water pumps')
                    ->descriptionIcon('heroicon-m-wrench-screwdriver')
                    ->color('success'),
                    
                Stat::make('Active Sessions', UsageSession::where('status', 'active')->count())
                    ->description('Currently running pumps')
                    ->descriptionIcon('heroicon-m-play-circle')
                    ->color('warning'),
                    
                Stat::make('Total Users', User::where('role', 'farmer')->count())
                    ->description('Registered farmers')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('info'),
                    
                Stat::make('Pending Bills', Billing::where('status', 'pending')->count())
                    ->description('Awaiting payment')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('danger'),
            ];
        } else {
            // Farmer view
            return [
                Stat::make('My Sessions', UsageSession::where('user_id', $user->id)->count())
                    ->description('Total usage sessions')
                    ->descriptionIcon('heroicon-m-play-circle')
                    ->color('info'),
                    
                Stat::make('Active Now', UsageSession::where('user_id', $user->id)->where('status', 'active')->count())
                    ->description('Currently active pumps')
                    ->descriptionIcon('heroicon-m-bolt')
                    ->color('warning'),
                    
                Stat::make('Total Spent', 'Rp ' . number_format(
                    Billing::where('user_id', $user->id)->where('status', 'paid')->sum('amount'), 0, ',', '.'
                ))
                    ->description('Total payments made')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('success'),
                    
                Stat::make('Pending Bills', Billing::where('user_id', $user->id)->where('status', 'pending')->count())
                    ->description('Awaiting payment')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}