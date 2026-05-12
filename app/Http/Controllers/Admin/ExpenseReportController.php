<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExpenseReportService;
use Illuminate\Http\Request;

class ExpenseReportController extends Controller
{
    private ExpenseReportService $expenseReportService;

    public function __construct(ExpenseReportService $expenseReportService)
    {
        $this->expenseReportService = $expenseReportService;
    }

    public function dailySummary(Request $request)
    {
        try {
            return response()->json($this->expenseReportService->dailySummary($request));
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function breakdownByType(Request $request)
    {
        try {
            return response()->json($this->expenseReportService->breakdownByType($request));
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function paymentMethodReport(Request $request)
    {
        try {
            return response()->json($this->expenseReportService->paymentMethodReport($request));
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
