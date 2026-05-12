<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use App\Libraries\AppLibrary;
use App\Enums\Role as EnumRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{

    public function totalSales(Request $request)
    {
        try { 
            if ($request->from_date && $request->to_date) {
                $first_date = Carbon::parse($request->from_date);
                $last_date  = Carbon::parse($request->to_date);
            } else {
                $first_date = Carbon::today()->startOfDay();
                $last_date  = Carbon::today()->endOfDay();
            }

            $order = new Order();
            $total = $order
                ->where('payment_status', PaymentStatus::PAID)
                ->whereBetween('order_datetime', [$first_date, $last_date])
                ->sum(DB::raw('total + total_tax'));
            return AppLibrary::flatAmountFormat($total);


        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
    public function salesSummary(Request $request)
    {
        $order = new Order;

        if ($request->from_date && $request->to_date) {
            $first_date = Carbon::parse($request->from_date);
            $last_date  = Carbon::parse($request->to_date);
        } else {
            $first_date = Carbon::today()->startOfDay();
            $last_date  = Carbon::today()->endOfDay();
        }

        $date = date_diff(date_create($first_date), date_create($last_date));
        $date_diff = $date->days + 1; // Add 1 to include both start and end dates

        $total_sales_raw = $order->whereBetween('order_datetime', [$first_date, $last_date])
            ->where('payment_status', PaymentStatus::PAID)
            ->sum(DB::raw('total + total_tax'));

        $total_sales = AppLibrary::flatAmountFormat($total_sales_raw);

        $dateRangeArray = [];
        $currentDate = Carbon::parse($first_date);
        $lastDate = Carbon::parse($last_date);

        while ($currentDate <= $lastDate) {
            $dateRangeArray[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $dateRangeValueArray = [];
        foreach ($dateRangeArray as $date) {
            $per_day = AppLibrary::flatAmountFormat($order->whereDate('order_datetime', $date)
                ->where('payment_status', PaymentStatus::PAID)
                ->sum(DB::raw('total + total_tax')));
            $dateRangeValueArray[] = floatval($per_day);
        }

        $salesSummaryArray = [
            'total_sales' => $total_sales,
            'avg_per_day' => AppLibrary::flatAmountFormat($date_diff > 0 ? $total_sales_raw / $date_diff : $total_sales_raw),
            'per_day_sales' => $dateRangeValueArray
        ];

        return $salesSummaryArray;
    }

    public function customerStates(Request $request)
    {
        $order = new Order;

        if ($request->from_date && $request->to_date) {
            $first_date = Carbon::parse($request->from_date);
            $last_date  = Carbon::parse($request->to_date);
        } else {
            $first_date = Carbon::today()->startOfDay();
            $last_date  = Carbon::today()->endOfDay();
        }


        $timeArray = ["06:00", "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00", "22:00", "23:00"];

        $customerSateArray = [];
        $totalCustomerArray = [];
        $first_time = "";
        $last_time = "";
        for ($i = 0; $i <= count($timeArray) - 1; $i++) {

            $first_time = date('H:i', strtotime($timeArray[$i]));
            $last_time = date('H:i', strtotime($timeArray[$i] . ' +59 minutes'));

            $total_customer     = $order->whereBetween('order_datetime', [$first_date, $last_date])
                ->whereTime('order_datetime', '>=', Carbon::parse($first_time))
                ->whereTime('order_datetime', '<=', Carbon::parse($last_time))
                ->get()->count();
            $totalCustomerArray[] = $total_customer;
        }

        $customerSateArray['total_customers'] = $totalCustomerArray;
        $customerSateArray['times'] = $timeArray;

        return $customerSateArray;
    }

    public function totalOrders(Request $request)
    {
        try {

            if ($request->from_date && $request->to_date) {
                $first_date = Carbon::parse($request->from_date);
                $last_date  = Carbon::parse($request->to_date);
            } else {
                $first_date = Carbon::today()->startOfDay();
                $last_date  = Carbon::today()->endOfDay();
            }

            return Order::where('status', OrderStatus::DELIVERED)
                ->whereBetween('order_datetime', [$first_date, $last_date])
                ->count();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function totalCustomers(Request $request)
    {
        try {
            if ($request->from_date && $request->to_date) {
                $first_date = Carbon::parse($request->from_date);
                $last_date  = Carbon::parse($request->to_date);
            } else {
                $first_date = Carbon::today()->startOfDay();
                $last_date  = Carbon::today()->endOfDay();
            }

            return User::role(EnumRole::CUSTOMER)
                ->whereBetween('created_at', [$first_date, $last_date])
                ->count();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function totalMenuItems(Request $request)
    {
        try {
            if ($request->from_date && $request->to_date) {
                $first_date = Carbon::parse($request->from_date);
                $last_date  = Carbon::parse($request->to_date);
            } else {
                $first_date = Carbon::today()->startOfDay();
                $last_date  = Carbon::today()->endOfDay();
            }

            return Item::whereBetween('created_at', [$first_date, $last_date])
                ->count();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
