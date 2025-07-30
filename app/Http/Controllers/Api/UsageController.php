<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsageSession;
use App\Models\Pump;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class UsageController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'pump_id' => 'required|exists:pumps,id',
            'user_id' => 'required|exists:users,id',
            'quota_kwh' => 'required|numeric|min:0.1|max:100',
        ]);

        $pump = Pump::findOrFail($request->pump_id);
        
        // Check if pump is already in use
        if ($pump->isInUse()) {
            return response()->json([
                'success' => false,
                'message' => 'Pump is already in use',
            ], 400);
        }

        $currentTariff = Tariff::getCurrentRate();
        
        $session = UsageSession::create([
            'user_id' => $request->user_id,
            'pump_id' => $request->pump_id,
            'quota_kwh' => $request->quota_kwh,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'tariff_rate' => $currentTariff,
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'quota_kwh' => $session->quota_kwh,
            'tariff_rate' => $session->tariff_rate,
            'relay_status' => 'ON',
            'message' => 'Session started successfully',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|exists:usage_sessions,id',
            'current_kwh' => 'required|numeric|min:0',
        ]);

        $session = UsageSession::find($request->session_id);
        
        if (!$session || !$session->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive session',
                'relay_status' => 'OFF',
            ], 400);
        }

        $session->update([
            'actual_kwh' => $request->current_kwh,
            'cost' => $request->current_kwh * $session->tariff_rate,
        ]);

        // Check if quota is exceeded
        if ($session->isQuotaExceeded()) {
            $session->update([
                'status' => 'exceeded',
                'ended_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'quota_exceeded' => true,
                'relay_status' => 'OFF',
                'message' => 'Quota exceeded, pump stopped',
                'actual_kwh' => $session->actual_kwh,
                'quota_kwh' => $session->quota_kwh,
            ]);
        }

        return response()->json([
            'success' => true,
            'quota_exceeded' => false,
            'relay_status' => 'ON',
            'remaining_kwh' => $session->remaining_quota,
            'usage_percentage' => $session->usage_percentage,
            'actual_kwh' => $session->actual_kwh,
            'quota_kwh' => $session->quota_kwh,
        ]);
    }

    public function stop(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|exists:usage_sessions,id',
            'final_kwh' => 'required|numeric|min:0',
        ]);

        $session = UsageSession::find($request->session_id);
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        $session->update([
            'actual_kwh' => $request->final_kwh,
            'cost' => $request->final_kwh * $session->tariff_rate,
            'status' => 'completed',
            'ended_at' => Carbon::now(),
        ]);

        // Create billing record
        $session->billing()->create([
            'user_id' => $session->user_id,
            'usage_session_id' => $session->id,
            'amount' => $session->cost,
            'tariff_rate' => $session->tariff_rate,
            'kwh_used' => $session->actual_kwh,
            'due_date' => Carbon::now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session stopped successfully',
            'relay_status' => 'OFF',
            'total_cost' => $session->cost,
            'kwh_used' => $session->actual_kwh,
        ]);
    }
}