<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PointUsageRuleExport;
use App\Http\Requests\PointUsageRuleRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\PointUsageRuleResource;
use App\Models\PointUsageRule;
use App\Services\PointUsageRuleService;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PointUsageRuleController extends AdminController
{
    public PointUsageRuleService $pointUsageRuleService;

    public function __construct(PointUsageRuleService $pointUsageRuleService)
    {
        parent::__construct();
        $this->pointUsageRuleService = $pointUsageRuleService;
        $this->middleware(['permission:point-usage-rules'])->only('index', 'export', 'statistics', 'active', 'calculateCurrency', 'usageTypes');
        $this->middleware(['permission:point-usage-rules_create'])->only('store');
        $this->middleware(['permission:point-usage-rules_edit'])->only('update', 'toggleStatus', 'bulkUpdateStatus');
        $this->middleware(['permission:point-usage-rules_show'])->only('show');
        $this->middleware(['permission:point-usage-rules_delete'])->only('destroy');
    }

    /**
     * Display a listing of point usage rules
     */
    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return PointUsageRuleResource::collection($this->pointUsageRuleService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get all active point usage rules
     */
    public function active(Request $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $usageType = $request->get('usage_type');
            
            if ($usageType) {
                $pointUsageRules = $this->pointUsageRuleService->getActiveByType($usageType);
            } else {
                $pointUsageRules = $this->pointUsageRuleService->getActive();
            }
            
            return PointUsageRuleResource::collection($pointUsageRules);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Store a newly created point usage rule
     */
    public function store(PointUsageRuleRequest $request): \Illuminate\Http\Response|PointUsageRuleResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $pointUsageRule = $this->pointUsageRuleService->store($request->validated());
            return new PointUsageRuleResource($pointUsageRule);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified point usage rule
     */
    public function show(PointUsageRule $pointUsageRule): \Illuminate\Http\Response|PointUsageRuleResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new PointUsageRuleResource($pointUsageRule->load('branch'));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update the specified point usage rule
     */
    public function update(PointUsageRuleRequest $request, PointUsageRule $pointUsageRule): \Illuminate\Http\Response|PointUsageRuleResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->pointUsageRuleService->update($pointUsageRule, $request->validated());
            return new PointUsageRuleResource($pointUsageRule->fresh());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified point usage rule
     */
    public function destroy(PointUsageRule $pointUsageRule): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->pointUsageRuleService->destroy($pointUsageRule);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Toggle point usage rule status
     */
    public function toggleStatus(PointUsageRule $pointUsageRule): \Illuminate\Http\Response|PointUsageRuleResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->pointUsageRuleService->toggleStatus($pointUsageRule);
            return new PointUsageRuleResource($pointUsageRule->fresh());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Calculate currency for points
     */
    public function calculateCurrency(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->validate([
                'points' => 'required|integer|min:1',
                'usage_type' => 'sometimes|string|in:deduct_order,exchange_gift',
            ]);

            $currency = $this->pointUsageRuleService->calculateCurrencyForPoints(
                $request->points,
                $request->get('usage_type', 'deduct_order')
            );
            
            return response([
                'status' => true,
                'data' => [
                    'points' => $request->points,
                    'currency_value' => $currency,
                    'usage_type' => $request->get('usage_type', 'deduct_order'),
                ],
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get point usage rule statistics
     */
    public function statistics(): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $statistics = $this->pointUsageRuleService->getStatistics();
            
            return response([
                'status' => true,
                'data' => $statistics,
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get usage type options
     */
    public function usageTypes(): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $usageTypes = $this->pointUsageRuleService->getUsageTypeOptions();
            
            return response([
                'status' => true,
                'data' => $usageTypes,
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:point-usage-rules,id',
                'is_active' => 'required|boolean',
            ]);

            $updated = $this->pointUsageRuleService->bulkUpdateStatus(
                $request->ids,
                $request->is_active
            );
            
            return response([
                'status' => true,
                'message' => "{$updated} point usage rules updated successfully",
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Export point usage rules
     */
    public function export(PaginateRequest $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new PointUsageRuleExport($this->pointUsageRuleService, $request), 'PointUsageRules.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
