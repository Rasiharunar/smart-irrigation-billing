<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use App\Models\UsageSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class SensorController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'pump_id' => 'required|exists:pumps,id',
            'voltage' => 'required|numeric',
            'current' => 'required|numeric',
            'power' => 'required|numeric',
            'energy' => 'required|numeric',
            'frequency' => 'required|numeric',
            'power_factor' => 'required|numeric|between:0,1',
            'session_id' => 'nullable|exists:usage_sessions,id',
        ]);

        $sensorReading = SensorReading::create([
            'pump_id' => $request->pump_id,
            'usage_session_id' => $request->session_id,
            'voltage' => $request->voltage,
            'current' => $request->current,
            'power' => $request->power,
            'energy' => $request->energy,
            'frequency' => $request->frequency,
            'power_factor' => $request->power_factor,
            'recorded_at' => Carbon::now(),
        ]);

        // If there's an active session, update the actual kWh
        if ($request->session_id) {
            $session = UsageSession::find($request->session_id);
            if ($session && $session->isActive()) {
                $session->update([
                    'actual_kwh' => $request->energy,
                    'cost' => $request->energy * $session->tariff_rate,
                ]);
                
                // Check if quota exceeded
                if ($session->isQuotaExceeded()) {
                    $session->update([
                        'status' => 'exceeded',
                        'ended_at' => Carbon::now(),
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Sensor data recorded',
                        'quota_exceeded' => true,
                        'relay_command' => 'OFF',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sensor data recorded successfully',
            'reading_id' => $sensorReading->id,
            'quota_exceeded' => false,
            'relay_command' => 'ON',
        ]);
    }
}