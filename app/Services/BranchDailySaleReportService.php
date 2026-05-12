<?php

namespace App\Services;

use App\Enums\Status;
use App\Enums\PaymentStatus;
use App\Libraries\AppLibrary;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchDailySaleReportService
{
    public function getDailySaleData(Request $request)
    {
        // Get date range from request or default to current month
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        // Generate date range array
        $dateRange = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateRange[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->format('j'), // Day without leading zeros (1, 2, 3, ..., 31)
                'label' => $currentDate->format('jS'), // 1st, 2nd, 3rd, etc.
                'full_date' => $currentDate->format('M j, Y')
            ];
            $currentDate->addDay();
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
            $dailyData = [];
            $totalAmounts = [];
            $totalOrders = 0;

            // Initialize totals for each currency
            foreach ($availableCurrencies as $currency) {
                $totalAmounts[$currency] = 0;
            }

            foreach ($dateRange as $dateInfo) {
                $date = $dateInfo['date'];
                
                // Get daily sales data for this branch and date
                $dailySalesData = Order::where('branch_id', $branch->id)
                    ->where('payment_status', PaymentStatus::PAID)
                    ->whereDate('order_datetime', $date)
                    ->select(
                        'currency',
                        DB::raw('SUM(total + total_tax) as total_sales'),
                        DB::raw('COUNT(*) as total_orders')
                    )
                    ->groupBy('currency')
                    ->get();

                // Initialize daily amounts with all currencies set to 0
                $dailyAmounts = [];
                foreach ($availableCurrencies as $currency) {
                    $dailyAmounts[$currency] = 0;
                }
                $dailyOrders = 0;

                foreach ($dailySalesData as $sale) {
                    $currency = $sale->currency ?? 'USD';
                    $amount = $sale->total_sales;
                    
                    $dailyAmounts[$currency] = AppLibrary::flatAmountFormat($amount);
                    $dailyOrders += $sale->total_orders;

                    // Add to totals
                    if (!isset($totalAmounts[$currency])) {
                        $totalAmounts[$currency] = 0;
                    }
                    $totalAmounts[$currency] += $amount;
                }

                $dailyData[$date] = [
                    'amounts' => $dailyAmounts,
                    'orders' => $dailyOrders
                ];

                $totalOrders += $dailyOrders;
            }

            // Format totals
            foreach ($availableCurrencies as $currency) {
                if (!isset($totalAmounts[$currency])) {
                    $totalAmounts[$currency] = 0;
                }
                $totalAmounts[$currency] = AppLibrary::flatAmountFormat($totalAmounts[$currency]);
            }

            $summaryData[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'daily_data' => $dailyData,
                'total_amounts' => $totalAmounts,
                'total_orders' => $totalOrders
            ];
        }

        return [
            'branches' => $summaryData,
            'date_range' => $dateRange,
            'available_currencies' => $availableCurrencies,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ];
    }
}
