<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'pump_id',
        'usage_session_id',
        'voltage',
        'current',
        'power',
        'energy',
        'frequency',
        'power_factor',
        'recorded_at',
    ];

    protected $casts = [
        'voltage' => 'decimal:2',
        'current' => 'decimal:3',
        'power' => 'decimal:2',
        'energy' => 'decimal:4',
        'frequency' => 'decimal:2',
        'power_factor' => 'decimal:3',
        'recorded_at' => 'datetime',
    ];

    public function pump()
    {
        return $this->belongsTo(Pump::class);
    }

    public function usageSession()
    {
        return $this->belongsTo(UsageSession::class);
    }
}