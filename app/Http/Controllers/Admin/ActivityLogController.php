<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Services\ActivityLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends AdminController
{
    private ActivityLoggerService $activityLoggerService;

    public function __construct(ActivityLoggerService $activityLoggerService)
    {
        parent::__construct();
        $this->activityLoggerService = $activityLoggerService;
        
        $this->middleware(['permission:activity_log'])->only(['index', 'show', 'byType', 'byUser', 'statistics']);
        $this->middleware(['permission:activity_logs_delete'])->only(['destroy', 'clean']);
    }

    /**
     * Get paginated activity logs
     */
    public function index(PaginateRequest $request): JsonResponse
    {
        Log::info('callbackurl: ', ['url' => route('payway.callback')]);
        Log::info('absolute callbackurl: ', ['url' => route('payway.callback', [], true)]);
        
        try {
            $filters = [
                'log_name' => $request->get('log_name'),
                'user_id' => $request->get('user_id'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'per_page' => $request->get('per_page', 50),
            ];

            $activities = $this->activityLoggerService->getActivityLogs($filters);
            
            return new JsonResponse([
                'data' => ActivityLogResource::collection($activities->items()),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ]
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get activity log by ID
     */
    public function show(ActivityLog $activityLog): JsonResponse
    {
        try {
            return new JsonResponse(['data' => new ActivityLogResource($activityLog->load(['causer', 'subject']))]);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get activity logs by type
     */
    public function byType(Request $request, string $logName): JsonResponse
    {
        try {
            $activities = ActivityLog::byLogName($logName)
                ->with(['causer', 'subject'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 50));

            return new JsonResponse([
                'data' => ActivityLogResource::collection($activities->items()),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ]
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get activity logs for a specific user
     */
    public function byUser(Request $request, int $userId): JsonResponse
    {
        try {
            $activities = ActivityLog::byCauser($userId)
                ->with(['causer', 'subject'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 50));

            return new JsonResponse([
                'data' => ActivityLogResource::collection($activities->items()),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ]
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Delete a specific activity log
     */
    public function destroy(ActivityLog $activityLog): JsonResponse
    {
        try {
            $activityLog->delete();
            
            // Log the deletion activity
            $this->activityLoggerService->logSystemActivity('activity log deleted', [
                'deleted_activity_id' => $activityLog->id,
                'deleted_activity_description' => $activityLog->description,
            ]);
            
            return new JsonResponse(['status' => true, 'message' => 'Activity log deleted successfully']);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Clean old activity logs
     */
    public function clean(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 365);
            $deletedCount = $this->activityLoggerService->cleanOldLogs($days);
            
            // Log the cleanup activity
            $this->activityLoggerService->logSystemActivity('activity logs cleaned', [
                'days_threshold' => $days,
                'deleted_count' => $deletedCount,
            ]);
            
            return new JsonResponse([
                'status' => true, 
                'message' => "Successfully cleaned {$deletedCount} old activity logs"
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get activity log statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_activities' => ActivityLog::count(),
                'activities_today' => ActivityLog::whereDate('created_at', today())->count(),
                'activities_this_week' => ActivityLog::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'activities_this_month' => ActivityLog::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'by_type' => ActivityLog::select('log_name')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('log_name')
                    ->orderByDesc('count')
                    ->get()
                    ->pluck('count', 'log_name'),
                'most_active_users' => ActivityLog::select('causer_id')
                    ->selectRaw('COUNT(*) as activity_count')
                    ->where('causer_type', 'App\\Models\\User')
                    ->whereNotNull('causer_id')
                    ->groupBy('causer_id')
                    ->orderByDesc('activity_count')
                    ->limit(10)
                    ->with('causer:id,name,email')
                    ->get(),
            ];

            return new JsonResponse(['data' => $stats]);
        } catch (\Exception $exception) {
            return new JsonResponse(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
