<?php

namespace App\Filament\Widgets;

use App\Models\UsageSession;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class UsageChart extends ChartWidget
{
    protected static ?string $heading = 'Daily Usage (kWh)';

    protected function getData(): array
    {
        $user = auth()->user();
        
        // Get last 7 days of data
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M j');
            
            $query = UsageSession::whereDate('started_at', $date);
            
            if (!$user->isAdmin()) {
                $query->where('user_id', $user->id);
            }
            
            $data[] = $query->sum('actual_kwh');
        }

        return [
            'datasets' => [
                [
                    'label' => 'kWh Used',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}