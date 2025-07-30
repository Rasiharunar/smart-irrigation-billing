<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pump extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'description',
        'is_active',
        'relay_pin',
        'max_power_kwh',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_power_kwh' => 'decimal:2',
    ];

    public function usageSessions()
    {
        return $this->hasMany(UsageSession::class);
    }

    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentSession()
    {
        return $this->hasOne(UsageSession::class)
            ->where('status', 'active')
            ->latest();
    }

    public function isInUse(): bool
    {
        return $this->currentSession()->exists();
    }
}