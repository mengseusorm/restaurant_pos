<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Branch;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use App\Libraries\AppLibrary;
use App\Enums\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BranchTrendReportService
{
    public function getTrendReportData(Request $request)
    {
        return [
            'trend_data' => $this->getBranchTrendData($request),
            'summary_data' => $this->getBranchSummaryData($request),
        ];
    }

    public function getBranchTrendData(Request $request)
    {
        $months = $request->months ?? 3; // Default to 3 months
        
        // Generate month range
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();
        
        $monthsArray = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $monthsArray[] = [
                'label' => $currentDate->format('M Y'),
                'year_month' => $currentDate->format('Y-m'),
                'start_date' => $currentDate->copy()->startOfMonth(),
                'end_date' => $currentDate->copy()->endOfMonth()
            ];
            $currentDate->addMonth();
        }

        // Get all active branches
        $branches = Branch::where('status', Status::ACTIVE)
            ->select('id', 'name')
            ->get();

        $trendData = [];

        foreach ($branches as $branch) {
            $branchData = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'monthly_data' => []
            ];

            foreach ($monthsArray as $month) {
                $salesData = Order::where('branch_id', $branch->id)
                    ->where('payment_status', PaymentStatus::PAID)
                    ->whereBetween('order_datetime', [$month['start_date'], $month['end_date']])
                    ->select(
                        'currency',
                        DB::raw('SUM(total + total_tax) as total_sales'),
                        DB::raw('COUNT(*) as total_orders')
                    )
                    ->groupBy('currency')
                    ->get();

                $monthlyAmounts = [];
                $totalOrders = 0;

                foreach ($salesData as $sale) {
                    $monthlyAmounts[$sale->currency] = AppLibrary::flatAmountFormat($sale->total_sales);
                    $totalOrders += $sale->total_orders;
                }

                $branchData['monthly_data'][] = [
                    'month' => $month['label'],
                    'year_month' => $month['year_month'],
                    'amounts' => $monthlyAmounts,
                    'total_orders' => $totalOrders
                ];
            }

            $trendData[] = $branchData;
        }

        return [
            'months' => $monthsArray,
            'branches' => $trendData
        ];
    }

    public function getBranchSummaryData(Request $request)
    {
        $months = $request->months ?? 3; // Default to 3 months
        
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();

        // Generate month range
        $monthsArray = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $monthsArray[] = [
                'label' => $currentDate->format('M Y'),
                'year_month' => $currentDate->format('Y-m'),
                'start_date' => $currentDate->copy()->startOfMonth(),
                'end_date' => $currentDate->copy()->endOfMonth()
            ];
            $currentDate->addMonth();
        }

        // Get all active branches
        $branches = Branch::where('status', Status::ACTIVE)
            ->select('id', 'name')
            ->get();

        // Get all available currencies from the date range
        $availableCurrencies = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('payment_status', PaymentStatus::PAID)
            ->distinct()
            ->pluck('currency')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
        
        // Ensure USD is included if no currencies found
        if (empty($availableCurrencies)) {
            $availableCurrencies = ['USD'];
        }

        $summaryData = [];

        foreach ($branches as $branch) {
            // Get monthly breakdown data
            $monthlyData = [];
            $totalAmounts = [];
            $totalOrders = 0;

            foreach ($monthsArray as $index => $month) {
                $monthlySalesData = Order::where('branch_id', $branch->id)
                    ->where('payment_status', PaymentStatus::PAID)
                    ->whereBetween('order_datetime', [$month['start_date'], $month['end_date']])
                    ->select(
                        'currency',
                        DB::raw('SUM(total + total_tax) as total_sales'),
                        DB::raw('COUNT(*) as total_orders')
                    )
                    ->groupBy('currency')
                    ->get();

                // Initialize monthly amounts with all currencies set to 0
                $monthlyAmounts = [];
                foreach ($availableCurrencies as $currency) {
                    $monthlyAmounts[$currency] = 0;
                }
                $monthlyOrders = 0;

                foreach ($monthlySalesData as $sale) {
                    $currency = $sale->currency ?? 'USD';
                    $amount = $sale->total_sales;
                    
                    $monthlyAmounts[$currency] = AppLibrary::flatAmountFormat($amount);
                    $monthlyOrders += $sale->total_orders;

                    // Add to totals
                    if (!isset($totalAmounts[$currency])) {
                        $totalAmounts[$currency] = 0;
                    }
                    $totalAmounts[$currency] += $amount;
                }

                $monthlyData[$index] = [
                    'amounts' => $monthlyAmounts,
                    'orders' => $monthlyOrders
                ];

                $totalOrders += $monthlyOrders;
            }

            // Calculate averages and format totals
            $averageAmounts = [];
            foreach ($availableCurrencies as $currency) {
                if (!isset($totalAmounts[$currency])) {
                    $totalAmounts[$currency] = 0;
                }
                $averageAmounts[$currency] = AppLibrary::flatAmountFormat($totalAmounts[$currency] / $months);
                $totalAmounts[$currency] = AppLibrary::flatAmountFormat($totalAmounts[$currency]);
            }

            $summaryData[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'monthly_data' => $monthlyData,
                'total_amounts' => $totalAmounts,
                'average_amounts' => $averageAmounts,
                'total_orders' => $totalOrders,
                'average_orders' => round($totalOrders / $months, 2)
            ];
        }

        return [
            'branches' => $summaryData,
            'available_currencies' => $availableCurrencies,
            'months_array' => $monthsArray
        ];
    }

    public function export(Request $request)
    {
        $summaryData = $this->getBranchSummaryData($request);
        
        // Return data for BranchTrendReportExport
        return [
            'data' => $summaryData,
            'filename' => 'branch_trend_report_' . now()->format('Y_m_d_H_i_s')
        ];
    }

    public function pdf(Request $request)
    {
        $summaryData = $this->getBranchSummaryData($request);
        
        // Return data for PDF generation
        return [
            'data' => $summaryData,
            'filename' => 'branch_trend_report_' . now()->format('Y_m_d_H_i_s')
        ];
    }
}
