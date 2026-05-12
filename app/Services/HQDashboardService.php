<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Branch;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use App\Libraries\AppLibrary;
use App\Enums\Role as EnumRole;
use App\Enums\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HQDashboardService
{
    public function getDashboardData(Request $request)
    {
        return [
            'total_sales' => $this->totalSales($request),
            'total_orders' => $this->totalOrders($request),
            'total_customers' => $this->totalCustomers($request),
            'total_branches' => $this->totalBranches($request),
            'branch_sales_comparison' => $this->branchSalesComparison($request),
            'top_performing_branches' => $this->topPerformingBranches($request),
            'order_status_summary' => $this->orderStatusSummary($request),
            'payment_method_summary' => $this->paymentMethodSummary($request),
            'sales_trend' => $this->salesTrend($request),
            'shop_category_sales' => $this->shopCategorySalesSummary($request),
        ];
    }

    public function totalSales(Request $request)
    {
        $query = Order::query()
            ->select('currency', DB::raw('SUM(total + total_tax) as total_sales'))
            ->where('payment_status', PaymentStatus::PAID);
        
        if ($request->first_date && $request->last_date) {
            $first_date = Carbon::createFromFormat('Y-m-d', $request->first_date)->startOfDay();
            $last_date = Carbon::createFromFormat('Y-m-d', $request->last_date)->endOfDay();
            $query->where('order_datetime', '>=', $first_date)
                  ->where('order_datetime', '<=', $last_date);
        } else {
            // Default to current month
            $first_date = date('Y-m-01', strtotime(Carbon::today()->toDateString()));
            $last_date = date('Y-m-t', strtotime(Carbon::today()->toDateString()));
            $query->whereDate('order_datetime', '>=', $first_date)
                  ->whereDate('order_datetime', '<=', $last_date);
        }

        // Remove branch scope for HQ view - we want all branches
        return $query->withoutGlobalScopes()
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->currency => AppLibrary::flatAmountFormat($item->total_sales)];
            });
    }

    public function totalOrders(Request $request)
    {
        $query = Order::query();
        
        if ($request->first_date && $request->last_date) {
            $first_date = Carbon::createFromFormat('Y-m-d', $request->first_date)->startOfDay();
            $last_date = Carbon::createFromFormat('Y-m-d', $request->last_date)->endOfDay();
            $query->where('order_datetime', '>=', $first_date)
                  ->where('order_datetime', '<=', $last_date);
        } else {
            // Default to current month
            $first_date = date('Y-m-01', strtotime(Carbon::today()->toDateString()));
            $last_date = date('Y-m-t', strtotime(Carbon::today()->toDateString()));
            $query->whereDate('order_datetime', '>=', $first_date)
                  ->whereDate('order_datetime', '<=', $last_date);
        }

        return $query->withoutGlobalScopes()->count();
    }

    public function totalCustomers(Request $request)
    {
        $query = Order::query();
        
        if ($request->first_date && $request->last_date) {
            $first_date = Carbon::createFromFormat('Y-m-d', $request->first_date)->startOfDay();
            $last_date = Carbon::createFromFormat('Y-m-d', $request->last_date)->endOfDay();
            $query->where('order_datetime', '>=', $first_date)
                  ->where('order_datetime', '<=', $last_date);
        } else {
            // Default to current month
            $first_date = date('Y-m-01', strtotime(Carbon::today()->toDateString()));
            $last_date = date('Y-m-t', strtotime(Carbon::today()->toDateString()));
            $query->whereDate('order_datetime', '>=', $first_date)
                  ->whereDate('order_datetime', '<=', $last_date);
        }

        return $query->withoutGlobalScopes()
                     ->distinct('customer_name')
                     ->whereNotNull('customer_name')
                     ->count('customer_name');
    }

    public function totalBranches(Request $request)
    {
        return Branch::where('status', Status::ACTIVE)->count();
    }

    public function branchSalesComparison(Request $request)
    {
        $query = Order::query()
            ->select('branch_id', 'branches.name as branch_name', 'currency', DB::raw('SUM(total + total_tax) as total_sales'), DB::raw('COUNT(*) as total_orders'))
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->where('payment_status', PaymentStatus::PAID);

        if ($request->first_date && $request->last_date) {
            $first_date = Carbon::createFromFormat('Y-m-d', $request->first_date)->startOfDay();
            $last_date = Carbon::createFromFormat('Y-m-d', $request->last_date)->endOfDay();
            $query->where('order_datetime', '>=', $first_date)
                  ->where('order_datetime', '<=', $last_date);
        } else {
            // Default to current month
            $first_date = date('Y-m-01', strtotime(Carbon::today()->toDateString()));
            $last_date = date('Y-m-t', strtotime(Carbon::today()->toDateString()));
            $query->whereDate('order_datetime', '>=', $first_date)
                  ->whereDate('order_datetime', '<=', $last_date);
        }

        return $query->withoutGlobalScopes()
            ->groupBy('branch_id', 'branches.name', 'currency')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->groupBy('branch_id')
            ->map(function ($branchGroup) {
                $firstItem = $branchGroup->first();
                $currencies = $branchGroup->mapWithKeys(function ($item) {
                    return [$item->currency => AppLibrary::flatAmountFormat($item->total_sales)];
                });
                
                return [
                    'branch_id' => $firstItem->branch_id,
                    'branch_name' => $firstItem->branch_name,
                    'total_sales' => $currencies,
                    'total_orders' => $branchGroup->sum('total_orders'),
                ];
            })
            ->values();
    }

    public function topPerformingBranches(Request $request, $limit = 5)
    {
        return $this->branchSalesComparison($request)->take($limit);
    }

    public function orderStatusSummary(Request $request)
    {
        $query = Order::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status');

        if ($request->first_date && $request->last_date) {
            $first_date = Carbon::createFromFormat('Y-m-d', $request->first_date)->startOfDay();
            $last_date = Carbon::createFromFormat('Y-m-d', $request->last_date)->endOfDay();
            $query->where('order_datetime', '>=', $first_date)
                  ->where('order_datetime', '<=', $last_date);
        } else {
            // Default to current month
            $first_date = date('Y-m-01', strtotime(Carbon::today()->toDateString()));
            $last_date = date('Y-m-t', strtotime(Carbon::today()->toDateString()));
            $query->whereDate('order_datetime', '>=', $first_date)
                  ->whereDate('order_datetime', '<=', $last_date);
        }

        return $query->withoutGlobalScopes()
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'status_name' => $this->getOrderStatusName($item->status),
                    'count' => $item->count,
                ];
            });
    }

    public function paymentMethodSummary(Request $request)
    {
        $query = Order::query()
            ->select('payment_method', 'payment_methods.name as payment_method_name', 'currency', DB::raw('SUM(total + total_tax) as total_amount'), DB::raw('COUNT(*) as total_orders'))
            ->leftJoin('payment_methods', 'orders.payment_method', '=', 'payment_methods.id')
            ->where('payment_status', PaymentStatus::PAID);

        if ($request->first_date && $request->last_date) {
            $first_date = Carbon::createFromFormat('Y-m-d', $request->first_date)->startOfDay();
            $last_date = Carbon::createFromFormat('Y-m-d', $request->last_date)->endOfDay();
            $query->where('order_datetime', '>=', $first_date)
                  ->where('order_datetime', '<=', $last_date);
        } else {
            // Default to current month
            $first_date = date('Y-m-01', strtotime(Carbon::today()->toDateString()));
            $last_date = date('Y-m-t', strtotime(Carbon::today()->toDateString()));
            $query->whereDate('order_datetime', '>=', $first_date)
                  ->whereDate('order_datetime', '<=', $last_date);
        }

        return $query->withoutGlobalScopes()
            ->groupBy('payment_method', 'payment_methods.name', 'currency')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->groupBy('payment_method')
            ->map(function ($paymentGroup) {
                $firstItem = $paymentGroup->first();
                $currencies = $paymentGroup->mapWithKeys(function ($item) {
                    return [$item->currency => AppLibrary::flatAmountFormat($item->total_amount)];
                });
                
                return [
                    'payment_method' => $firstItem->payment_method,
                    'payment_method_name' => $firstItem->payment_method_name ?? 'Unknown',
                    'total_amount' => $currencies,
                    'total_orders' => $paymentGroup->sum('total_orders'),
                ];
            })
            ->values();
    }

    public function salesTrend(Request $request)
    {
        if ($request->first_date && $request->last_date) {
            $first_date = $request->first_date;
            $last_date = $request->last_date;
        } else {
            // Default to current month
            $first_date = date('Y-m-01', strtotime(Carbon::today()->toDateString()));
            $last_date = date('Y-m-t', strtotime(Carbon::today()->toDateString()));
        }

        $dateRangeArray = [];
        for ($currentDate = strtotime($first_date); $currentDate <= strtotime($last_date); $currentDate += (86400)) {
            $date = date('Y-m-d', $currentDate);
            $dateRangeArray[] = $date;
        }

        $dateRangeLabelArray = [];
        $currencyData = [];
        
        // Get all unique currencies first
        $currencies = Order::query()
            ->whereDate('order_datetime', '>=', $first_date)
            ->whereDate('order_datetime', '<=', $last_date)
            ->where('payment_status', PaymentStatus::PAID)
            ->withoutGlobalScopes()
            ->distinct('currency')
            ->pluck('currency')
            ->filter()
            ->toArray();

        // Initialize currency data structure
        foreach ($currencies as $currency) {
            $currencyData[$currency] = [];
        }
        
        foreach ($dateRangeArray as $date) {
            $dailySalesByCurrency = Order::query()
                ->select('currency', DB::raw('SUM(total + total_tax) as total_sales'))
                ->whereDate('order_datetime', $date)
                ->where('payment_status', PaymentStatus::PAID)
                ->withoutGlobalScopes()
                ->groupBy('currency')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->currency => floatval(AppLibrary::flatAmountFormat($item->total_sales))];
                });
                
            foreach ($currencies as $currency) {
                $currencyData[$currency][] = $dailySalesByCurrency[$currency] ?? 0;
            }
            
            $dateRangeLabelArray[] = date('M d', strtotime($date));
        }

        return [
            'labels' => $dateRangeLabelArray,
            'data' => $currencyData,
        ];
    }

    public function topSellingItems(Request $request, $limit = 10)
    {
        $query = OrderItem::query()
            ->select('item_id', 'items.name as item_name', 'orders.currency', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.total_price) as total_sales'))
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->where('orders.payment_status', PaymentStatus::PAID);

        if ($request->first_date && $request->last_date) {
            $first_date = Carbon::createFromFormat('Y-m-d', $request->first_date)->startOfDay();
            $last_date = Carbon::createFromFormat('Y-m-d', $request->last_date)->endOfDay();
            $query->where('orders.order_datetime', '>=', $first_date)
                  ->where('orders.order_datetime', '<=', $last_date);
        }

        return $query->withoutGlobalScopes()
            ->groupBy('item_id', 'items.name', 'orders.currency')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->groupBy('item_id')
            ->map(function ($itemGroup) {
                $firstItem = $itemGroup->first();
                $currencies = $itemGroup->mapWithKeys(function ($item) {
                    return [$item->currency => AppLibrary::flatAmountFormat($item->total_sales)];
                });
                
                return [
                    'item_id' => $firstItem->item_id,
                    'item_name' => $firstItem->item_name,
                    'total_quantity' => $itemGroup->sum('total_quantity'),
                    'total_sales' => $currencies,
                ];
            })
            ->values();
    }

    public function shopCategorySalesSummary(Request $request)
    {
        $query = Order::query()
            ->select('shop_categories.id as category_id', 'shop_categories.name as category_name', 'currency', DB::raw('SUM(orders.total + orders.total_tax) as total_sales'), DB::raw('COUNT(*) as total_orders'))
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->leftJoin('shop_categories', 'branches.shop_category_id', '=', 'shop_categories.id')
            ->where('orders.payment_status', PaymentStatus::PAID);

        if ($request->first_date && $request->last_date) {
            $first_date = Carbon::createFromFormat('Y-m-d', $request->first_date)->startOfDay();
            $last_date = Carbon::createFromFormat('Y-m-d', $request->last_date)->endOfDay();
            $query->where('orders.order_datetime', '>=', $first_date)
                  ->where('orders.order_datetime', '<=', $last_date);
        } else {
            // Default to current month
            $first_date = date('Y-m-01', strtotime(Carbon::today()->toDateString()));
            $last_date = date('Y-m-t', strtotime(Carbon::today()->toDateString()));
            $query->whereDate('orders.order_datetime', '>=', $first_date)
                  ->whereDate('orders.order_datetime', '<=', $last_date);
        }

        return $query->withoutGlobalScopes()
            ->groupBy('shop_categories.id', 'shop_categories.name', 'currency')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->groupBy('category_id')
            ->map(function ($categoryGroup) {
                $firstItem = $categoryGroup->first();
                $currencies = $categoryGroup->mapWithKeys(function ($item) {
                    return [$item->currency => AppLibrary::flatAmountFormat($item->total_sales)];
                });
                
                return [
                    'category_id' => $firstItem->category_id,
                    'category_name' => $firstItem->category_name ?? 'Uncategorized',
                    'total_sales' => $currencies,
                    'total_orders' => $categoryGroup->sum('total_orders'),
                ];
            })
            ->values();
    }

    private function getOrderStatusName($status)
    {
        $statuses = [
            OrderStatus::PENDING => 'Pending',
            OrderStatus::PROCESSING => 'Processing',
            OrderStatus::OUT_FOR_DELIVERY => 'Out for Delivery',
            OrderStatus::DELIVERED => 'Delivered',
            OrderStatus::CANCELED => 'Canceled',
            OrderStatus::RETURNED => 'Returned',
            OrderStatus::REJECTED => 'Rejected',
        ];

        return $statuses[$status] ?? 'Unknown';
    }
}
