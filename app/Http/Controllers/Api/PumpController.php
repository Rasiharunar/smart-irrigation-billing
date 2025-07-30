<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pump;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PumpController extends Controller
{
    public function status($id): JsonResponse
    {
        $pump = Pump::find($id);
        
        if (!$pump) {
            return response()->json([
                'success' => false,
                'message' => 'Pump not found',
            ], 404);
        }

        $currentSession = $pump->currentSession;
        $relayStatus = 'OFF';
        $sessionData = null;

        if ($currentSession && $currentSession->isActive()) {
            $relayStatus = 'ON';
            $sessionData = [
                'session_id' => $currentSession->id,
                'user_id' => $currentSession->user_id,
                'quota_kwh' => $currentSession->quota_kwh,
                'actual_kwh' => $currentSession->actual_kwh,
                'remaining_kwh' => $currentSession->remaining_quota,
                'usage_percentage' => $currentSession->usage_percentage,
                'started_at' => $currentSession->started_at,
            ];
        }

        return response()->json([
            'success' => true,
            'pump_id' => $pump->id,
            'pump_name' => $pump->name,
            'is_active' => $pump->is_active,
            'relay_status' => $relayStatus,
            'relay_pin' => $pump->relay_pin,
            'in_use' => $pump->isInUse(),
            'current_session' => $sessionData,
        ]);
    }
}