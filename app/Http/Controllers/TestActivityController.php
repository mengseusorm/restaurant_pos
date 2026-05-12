<?php

namespace App\Http\Controllers;

use App\Services\ActivityLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestActivityController extends Controller
{
    protected ActivityLoggerService $activityLogger;

    public function __construct(ActivityLoggerService $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    public function testAuth(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Test authentication logging
        $this->activityLogger->logAuthentication('test via API', $user, [
            'test_timestamp' => now()->toISOString(),
            'endpoint' => 'test-activity-auth'
        ]);

        return response()->json([
            'message' => 'Authentication activity logged successfully',
            'user_id' => $user->id,
            'branch_id' => $user->branch_id
        ]);
    }

    public function testSystem(Request $request): JsonResponse
    {
        // Test system activity logging
        $this->activityLogger->logSystemActivity('API test performed', [
            'endpoint' => 'test-activity-system',
            'timestamp' => now()->toISOString(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'message' => 'System activity logged successfully'
        ]);
    }
}
