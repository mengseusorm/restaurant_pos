<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Libraries\AppLibrary;
use App\Services\HQDashboardService;
use Illuminate\Http\Request;
use App\Http\Resources\HQDashboardResource;
use Illuminate\Support\Facades\Log;

class HQDashboardController extends AdminController
{
    private HQDashboardService $hqDashboardService;

    public function __construct(HQDashboardService $hqDashboardService)
    {
        parent::__construct();
        $this->hqDashboardService = $hqDashboardService;
        $this->middleware(['permission:hq-dashboard'])->only(
            'index',
            'totalSales',
            'totalOrders',
            'totalCustomers',
            'totalBranches',
            'branchSalesComparison',
            'topPerformingBranches',
            'orderStatusSummary',
            'paymentMethodSummary',
            'salesTrend',
            'shopCategorySalesSummary'
        );
    }

    public function index(Request $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\JsonResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new HQDashboardResource($this->hqDashboardService->getDashboardData($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function totalSales(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_sales' => $this->hqDashboardService->totalSales($request)]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function totalOrders(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_orders' => $this->hqDashboardService->totalOrders($request)]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function totalCustomers(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_customers' => $this->hqDashboardService->totalCustomers($request)]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function totalBranches(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_branches' => $this->hqDashboardService->totalBranches($request)]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function branchSalesComparison(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => $this->hqDashboardService->branchSalesComparison($request)];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function topPerformingBranches(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => $this->hqDashboardService->topPerformingBranches($request)];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function orderStatusSummary(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => $this->hqDashboardService->orderStatusSummary($request)];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function paymentMethodSummary(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => $this->hqDashboardService->paymentMethodSummary($request)];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function salesTrend(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => $this->hqDashboardService->salesTrend($request)];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function shopCategorySalesSummary(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => $this->hqDashboardService->shopCategorySalesSummary($request)];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
