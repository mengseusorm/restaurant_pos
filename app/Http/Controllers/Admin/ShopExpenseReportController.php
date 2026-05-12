<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShopExpenseReportService;
use Illuminate\Http\Request;

class ShopExpenseReportController extends Controller
{
    private ShopExpenseReportService $shopExpenseReportService;

    public function __construct(ShopExpenseReportService $shopExpenseReportService)
    {
        $this->shopExpenseReportService = $shopExpenseReportService;
    }

    public function dailySummary(Request $request)
    {
        try {
            return response()->json($this->shopExpenseReportService->dailySummary($request));
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function breakdownByType(Request $request)
    {
        try {
            return response()->json($this->shopExpenseReportService->breakdownByType($request));
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function paymentMethodReport(Request $request)
    {
        try {
            return response()->json($this->shopExpenseReportService->paymentMethodReport($request));
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
