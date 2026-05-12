<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Scopes\BranchScope;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Libraries\AppLibrary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class BranchSalesSummaryService
{
    /**
     * Generate comprehensive branch sales summary report
     */
    public function generateReport(array $filters): array
    {
        $branchId = $filters['branch_id'];
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        // Get branch information
        $branch = Branch::with('currency')->find($branchId);
        
        // Build base query - use payment_status for sales reports
        $ordersQuery = Order::withoutGlobalScope(BranchScope::class)
            ->with('orderItems', 'paymentMethod', 'posPaymentMethod')
            ->where('branch_id', $branchId)
            ->where('payment_status', PaymentStatus::PAID);

        if ($fromDate) {
            $ordersQuery->where('order_datetime', '>=', AppLibrary::filterDateTime($fromDate));
        }

        if ($toDate) {
            $ordersQuery->where('order_datetime', '<=', AppLibrary::filterDateTime($toDate));
        }

        $orders = $ordersQuery->get();

        Log::info("BranchSalesSummary: Found {$orders->count()} completed orders for branch {$branchId} between {$fromDate} and {$toDate}");

        return [
            'branch' => $branch,
            'kpis' => $this->calculateKPIs($orders, $branch),
            'sales_trend' => $this->getSalesTrend($orders, $fromDate, $toDate),
            'orders_trend' => $this->getOrdersTrend($orders, $fromDate, $toDate),
            'category_sales' => $this->getCategorySales($orders),
            // 'top_products' => $this->getTopProducts($orders),
            'payment_methods' => $this->getPaymentMethodBreakdown($orders),
            // 'customer_segments' => $this->getCustomerSegments($orders),
            'hourly_distribution' => $this->getHourlyDistribution($orders),
            'daily_distribution' => $this->getDailyDistribution($orders),
            'refunds' => $this->getRefundsData($orders, $branchId, $fromDate, $toDate),
        ];
    }

    /**
     * Calculate overall KPIs
     */
    private function calculateKPIs(Collection $orders, Branch $branch): array
    {
        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();
        $totalItemsSold = $orders->sum(function ($order) {
            return $order->orderItems->sum('quantity');
        });

        // Calculate gross profit (if cost data is available)
        $totalCost = $orders->sum(function ($order) {
            return $order->orderItems->sum(function ($item) {
                return ($item->item->cost ?? 0) * $item->quantity;
            });
        });

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'average_order_value' => $totalOrders > 0 ? $totalSales / $totalOrders : 0,
            'total_items_sold' => $totalItemsSold,
            'gross_profit' => $totalSales - $totalCost,
            'currency_symbol' => $branch->currency->symbol ?? '$'
        ];
    }

    /**
     * Get sales trend over time
     */
    private function getSalesTrend(Collection $orders, ?string $fromDate, ?string $toDate): array
    {
        $groupBy = $this->determineGroupBy($fromDate, $toDate);
        
        $salesByPeriod = $orders->groupBy(function ($order) use ($groupBy) {
            return $this->formatDateByGroupBy($order->order_datetime, $groupBy);
        })->map(function ($periodOrders) {
            return $periodOrders->sum('total');
        })->sortKeys();

        return [
            'labels' => $salesByPeriod->keys()->toArray(),
            'data' => $salesByPeriod->values()->toArray()
        ];
    }

    /**
     * Get orders trend over time
     */
    private function getOrdersTrend(Collection $orders, ?string $fromDate, ?string $toDate): array
    {
        $groupBy = $this->determineGroupBy($fromDate, $toDate);
        
        $ordersByPeriod = $orders->groupBy(function ($order) use ($groupBy) {
            return $this->formatDateByGroupBy($order->order_datetime, $groupBy);
        })->map(function ($periodOrders) {
            return $periodOrders->count();
        })->sortKeys();

        return [
            'labels' => $ordersByPeriod->keys()->toArray(),
            'data' => $ordersByPeriod->values()->toArray()
        ];
    }

    /**
     * Get sales by category
     */
    private function getCategorySales(Collection $orders): array
    {
        $categorySales = collect();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $orderItem) {
                $categoryName = $orderItem->item->itemCategory->name ?? 'Uncategorized';
                $itemTotal = $orderItem->price * $orderItem->quantity;
                
                if ($categorySales->has($categoryName)) {
                    $categorySales[$categoryName] += $itemTotal;
                } else {
                    $categorySales[$categoryName] = $itemTotal;
                }
            }
        }

        $categorySales = $categorySales->sortDesc();

        return [
            'labels' => $categorySales->keys()->toArray(),
            'data' => $categorySales->values()->toArray()
        ];
    }

    /**
     * Get top-selling products
     */
    private function getTopProducts(Collection $orders, int $limit = 10): array
    {
        $productSales = collect();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $orderItem) {
                $itemId = $orderItem->item_id;
                $itemName = $orderItem->item->name;
                $quantity = $orderItem->quantity;
                $total = $orderItem->price * $quantity;

                if ($productSales->has($itemId)) {
                    $productSales[$itemId]['quantity_sold'] += $quantity;
                    $productSales[$itemId]['total_sales'] += $total;
                } else {
                    $productSales[$itemId] = [
                        'id' => $itemId,
                        'name' => $itemName,
                        'quantity_sold' => $quantity,
                        'total_sales' => $total
                    ];
                }
            }
        }

        return $productSales->sortByDesc('total_sales')->take($limit)->values()->toArray();
    }

    /**
     * Get payment method breakdown
     */
    private function getPaymentMethodBreakdown(Collection $orders): array
    {
        $paymentBreakdown = $orders->groupBy('payment_method_id')
            ->map(function ($methodOrders, $methodId) {
                $total = $methodOrders->sum('total');
                $methodName = $methodOrders->first()->paymentMethod->name ?? 'Unknown';
                
                return [
                    'method' => $methodName,
                    'amount' => $total
                ];
            });

        $totalAmount = $paymentBreakdown->sum('amount');

        return $paymentBreakdown->map(function ($item) use ($totalAmount) {
            $item['percentage'] = $totalAmount > 0 ? round(($item['amount'] / $totalAmount) * 100, 2) : 0;
            return $item;
        })->values()->toArray();
    }

    /**
     * Get customer segments data
     */
    private function getCustomerSegments(Collection $orders): array
    {
        $newCustomers = collect();
        $returningCustomers = collect();
        $walkInCustomers = collect();

        foreach ($orders as $order) {
            if (!$order->customer_id) {
                // Walk-in customer
                $walkInCustomers->push($order);
            } else {
                // Check if this is the customer's first order
                $customerFirstOrder = Order::withoutGlobalScope(BranchScope::class)
                    ->where('customer_id', $order->customer_id)
                    ->where('payment_status', PaymentStatus::PAID)
                    ->orderBy('order_datetime')
                    ->first();

                if ($customerFirstOrder && $customerFirstOrder->id === $order->id) {
                    $newCustomers->push($order);
                } else {
                    $returningCustomers->push($order);
                }
            }
        }

        return [
            [
                'type' => 'New Customers',
                'count' => $newCustomers->count(),
                'total_sales' => $newCustomers->sum('total')
            ],
            [
                'type' => 'Returning Customers',
                'count' => $returningCustomers->count(),
                'total_sales' => $returningCustomers->sum('total')
            ],
            [
                'type' => 'Walk-in Customers',
                'count' => $walkInCustomers->count(),
                'total_sales' => $walkInCustomers->sum('total')
            ]
        ];
    }

    /**
     * Get hourly sales distribution
     */
    private function getHourlyDistribution(Collection $orders): array
    {
        $hourlyData = collect(range(0, 23))->mapWithKeys(function ($hour) {
            return [sprintf('%02d:00', $hour) => 0];
        });

        $hourlySales = $orders->groupBy(function ($order) {
            return Carbon::parse($order->order_datetime)->format('H:00');
        })->map(function ($hourOrders) {
            return $hourOrders->sum('total');
        });

        $hourlyData = $hourlyData->merge($hourlySales);

        return [
            'labels' => $hourlyData->keys()->toArray(),
            'data' => $hourlyData->values()->toArray()
        ];
    }

    /**
     * Get daily sales distribution
     */
    private function getDailyDistribution(Collection $orders): array
    {
        $dailyData = collect([
            'Sunday' => 0,
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0
        ]);

        $dailySales = $orders->groupBy(function ($order) {
            return Carbon::parse($order->order_datetime)->format('l'); // Full day name
        })->map(function ($dayOrders) {
            return $dayOrders->sum('total');
        });

        $dailyData = $dailyData->merge($dailySales);

        return [
            'labels' => $dailyData->keys()->toArray(),
            'data' => $dailyData->values()->toArray()
        ];
    }

    /**
     * Get refunds/returns data
     */
    private function getRefundsData(Collection $orders, int $branchId, ?string $fromDate, ?string $toDate): array
    {
        // Get refunded orders
        $refundsQuery = Order::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', OrderStatus::RETURNED);

        if ($fromDate) {
            $refundsQuery->where('order_datetime', '>=', Carbon::parse($fromDate));
        }

        if ($toDate) {
            $refundsQuery->where('order_datetime', '<=', Carbon::parse($toDate));
        }

        $refunds = $refundsQuery->get();
        $refundAmount = $refunds->sum('total');
        $totalSales = $orders->sum('total');

        return [
            'count' => $refunds->count(),
            'amount' => $refundAmount,
            'net_sales' => $totalSales - $refundAmount
        ];
    }

    /**
     * Determine grouping method based on date range
     */
    private function determineGroupBy(?string $fromDate, ?string $toDate): string
    {
        if (!$fromDate || !$toDate) {
            return 'day';
        }

        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);
        $daysDiff = $from->diffInDays($to);

        if ($daysDiff <= 7) {
            return 'day';
        } elseif ($daysDiff <= 31) {
            return 'day';
        } elseif ($daysDiff <= 365) {
            return 'week';
        } else {
            return 'month';
        }
    }

    /**
     * Format date based on grouping method
     */
    private function formatDateByGroupBy(Carbon $date, string $groupBy): string
    {
        switch ($groupBy) {
            case 'day':
                return $date->format('M d');
            case 'week':
                return 'Week of ' . $date->startOfWeek()->format('M d');
            case 'month':
                return $date->format('M Y');
            default:
                return $date->format('M d');
        }
    }
}
