<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use Exception;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use Carbon\Carbon;

class SaleSummaryReportService
{
    protected array $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function saleSummaryReportList(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            return Order::with('transaction', 'orderItems', 'orderDinings', 'paymentMethod','user', 'orderUser')
                ->where('payment_status', PaymentStatus::PAID)
                ->where(function ($query) use ($requests) {

                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = Carbon::parse($requests['from_date']);
                        $last_date  = Carbon::parse($requests['to_date']);

                        $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    } else {
                        $first_date = Carbon::today()->startOfDay();
                        $last_date  = Carbon::today()->endOfDay();

                        $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    }
                })->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
    public function saleSummaryReportByStaffList(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            return Order::with('transaction', 'orderItems', 'orderDinings', 'paymentMethod', 'user', 'orderUser')
                ->where('payment_status', PaymentStatus::PAID)
                ->where(function ($query) use ($requests) {
                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = Carbon::parse($requests['from_date']);
                        $last_date  = Carbon::parse($requests['to_date']);

                        $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    } else {
                        $first_date = Carbon::today()->startOfDay();
                        $last_date  = Carbon::today()->endOfDay();

                        $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    }
                })->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
