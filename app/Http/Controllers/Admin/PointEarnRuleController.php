<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PointEarnRuleExport;
use App\Http\Requests\PointEarnRuleRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\PointEarnRuleResource;
use App\Models\PointEarnRule;
use App\Services\PointEarnRuleService;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PointEarnRuleController extends AdminController
{
    public PointEarnRuleService $pointEarnRuleService;

    public function __construct(PointEarnRuleService $pointEarnRuleService)
    {
        parent::__construct();
        $this->pointEarnRuleService = $pointEarnRuleService;
        $this->middleware(['permission:point-earn-rules'])->only('index', 'export', 'statistics', 'active', 'calculatePoints');
        $this->middleware(['permission:point-earn-rules_create'])->only('store');
        $this->middleware(['permission:point-earn-rules_edit'])->only('update');
        $this->middleware(['permission:point-earn-rules_show'])->only('show');
        $this->middleware(['permission:point-earn-rules_delete'])->only('destroy');
    }

    /**
     * Display a listing of point earn rules
     */
    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return PointEarnRuleResource::collection($this->pointEarnRuleService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get all active point earn rules
     */
    public function active(): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $pointEarnRules = PointEarnRule::where('is_active', true)->get();
            return PointEarnRuleResource::collection($pointEarnRules);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Store a newly created point earn rule
     */
    public function store(PointEarnRuleRequest $request): \Illuminate\Http\Response|PointEarnRuleResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new PointEarnRuleResource($this->pointEarnRuleService->store($request, new PointEarnRule()));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified point earn rule
     */
    public function show(PointEarnRule $pointEarnRule): \Illuminate\Http\Response|PointEarnRuleResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new PointEarnRuleResource($this->pointEarnRuleService->show($pointEarnRule));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update the specified point earn rule
     */
    public function update(PointEarnRuleRequest $request, PointEarnRule $pointEarnRule): \Illuminate\Http\Response|PointEarnRuleResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new PointEarnRuleResource($this->pointEarnRuleService->update($request, $pointEarnRule));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified point earn rule
     */
    public function destroy(PointEarnRule $pointEarnRule): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->pointEarnRuleService->destroy($pointEarnRule);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Export point earn rules
     */
    public function export(PaginateRequest $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new PointEarnRuleExport($this->pointEarnRuleService, $request), 'PointEarnRules.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Calculate points for amount
     */
    public function calculatePoints(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0',
            ]);

            $points = $this->pointEarnRuleService->calculatePointsForAmount($request->amount);
            
            return response([
                'status' => true,
                'data' => [
                    'amount' => $request->amount,
                    'points_earned' => $points,
                ],
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Get point earn rule statistics
     */
    public function statistics(): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $statistics = $this->pointEarnRuleService->getStatistics();
            
            return response([
                'status' => true,
                'data' => $statistics,
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
