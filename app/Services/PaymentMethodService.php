<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Models\Order;

class PaymentMethodService
{
    public object $order;
    public object $lastOrderTimeItems;
    protected array $orderFilter = [
        'order_serial_no',
        'user_id',
        'branch_id',
        'total',
        'order_type',
        'order_datetime',
        'payment_method',
        'payment_status',
        'status',
        'delivery_boy_id',
        'source',
        'currency'
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'order_date'; // Default to order_date
            $orderType = $request->get('order_by') ?? 'desc';


            $orders = Order::query()
                ->leftJoin('payment_methods', 'orders.pos_payment_method', '=', 'payment_methods.id')
                ->selectRaw('
                    orders.currency as order_currency,
                    orders.pos_payment_method as payment_method,
                    IFNULL(payment_methods.name, "Unknown") as payment_method_name,
                    COUNT(DISTINCT orders.id) as total_orders,
                    SUM(orders.total) as total,
                    SUM(orders.total_tax) as total_tax,
                    SUM(orders.total + orders.total_tax) as total_with_tax
                ')
                ->where(function ($query) use ($requests) {
                    // Date range filter
                    if (!empty($requests['from_date']) && !empty($requests['to_date'])) {
                        $first_date = date('Y-m-d H:i:s', strtotime($requests['from_date']));
                        $last_date = date('Y-m-d H:i:s', strtotime($requests['to_date']));
                        $query->whereBetween('orders.order_datetime', [$first_date, $last_date]);
                    } else {
                        // Default to yesterday to today with branch times
                        $branch = \App\Models\Branch::find(auth()->user()->branch_id ?? 1);

                        $first_date = \Carbon\Carbon::yesterday()->startOfDay();
                        if ($branch && $branch->open_time) {
                            $time = explode(':', $branch->open_time);
                            $first_date->setTime((int)$time[0], (int)$time[1], 0);
                        }

                        $last_date = \Carbon\Carbon::today()->startOfDay();
                        if ($branch && $branch->close_time) {
                            $time = explode(':', $branch->close_time);
                            $last_date->setTime((int)$time[0], (int)$time[1], 59);
                        } else {
                            $last_date->endOfDay();
                        }

                        $query->whereBetween('orders.order_datetime', [$first_date->format('Y-m-d H:i:s'), $last_date->format('Y-m-d H:i:s')]);
                    }

                    // Payment method filter
                    if (!empty($requests['payment_method'])) {
                        $query->where('orders.pos_payment_method', $requests['payment_method']);
                    }

                    // Apply filters from orderFilter array
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            if ($key === 'status') {
                                $query->where('orders.' . $key, (int)$request);
                            } else if ($key === 'payment_method') {
                                if ((int)$request < 0) {
                                    $query->where('orders.pos_payment_method', abs($request));
                                } else if ((int)$request > 0) {
                                    $query->where('orders.pos_payment_method', $request);
                                }
                            } else if ($key === 'currency') {
                                $query->where('orders.currency', $request);
                            } else {
                                $query->where('orders.' . $key, $request);
                            }
                        }

                        // Handle except filters
                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            foreach ($explodes as $explode) {
                                if (!empty($explode)) {
                                    $query->where('orders.order_type', '!=', $explode);
                                }
                            }
                        }
                    }
                })
                ->groupBy('orders.pos_payment_method', 'payment_methods.name', 'orders.currency')
                ->orderBy('orders.pos_payment_method', $orderType)
                ->$method($methodValue);
            return $orders;

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
