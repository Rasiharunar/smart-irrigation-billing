<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pump_id',
        'quota_kwh',
        'actual_kwh',
        'status',
        'started_at',
        'ended_at',
        'cost',
        'tariff_rate',
    ];

    protected $casts = [
        'quota_kwh' => 'decimal:4',
        'actual_kwh' => 'decimal:4',
        'cost' => 'decimal:2',
        'tariff_rate' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pump()
    {
        return $this->belongsTo(Pump::class);
    }

    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class);
    }

    public function billing()
    {
        return $this->hasOne(Billing::class);
    }

    public function getRemainingQuotaAttribute(): float
    {
        return max(0, $this->quota_kwh - $this->actual_kwh);
    }

    public function getUsagePercentageAttribute(): float
    {
        if ($this->quota_kwh == 0) return 0;
        return min(100, ($this->actual_kwh / $this->quota_kwh) * 100);
    }

    public function isQuotaExceeded(): bool
    {
        return $this->actual_kwh >= $this->quota_kwh;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}