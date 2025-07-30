<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate_per_kwh',
        'description',
        'is_active',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'rate_per_kwh' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', now());
            });
    }

    public static function getCurrentRate(): float
    {
        $activeTariff = static::active()->first();
        return $activeTariff ? $activeTariff->rate_per_kwh : 0;
    }
}