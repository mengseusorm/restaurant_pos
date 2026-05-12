<?php

namespace App\Services;


use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Models\Order;
use Carbon\Carbon;

class OrderTypeReportService
{
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
        'source'
    ];

    protected $exceptFilter = [
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
            $orderColumn = $request->get('order_column') ?? 'order_date';
            $orderType = $request->get('order_by') ?? 'desc';

            return Order::query()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->selectRaw('
                    orders.currency as order_currency,
                    orders.order_type,
                    COUNT(DISTINCT orders.id) as total_order_type,
                    SUM(order_items.tax_amount) as total_tax,
                    SUM(order_items.price * order_items.quantity) as total_price
                ')->where(function ($query) use ($requests) {
                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = Carbon::parse($requests['from_date']);
                        $last_date  = Carbon::parse($requests['to_date']);

                        $query->whereBetween('orders.order_datetime', [$first_date, $last_date]);
                    } else {
                        $first_date = Carbon::today()->startOfDay();
                        $last_date  = Carbon::today()->endOfDay();

                        $query->whereBetween('orders.order_datetime', [$first_date, $last_date]);
                    }

                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            if ($key === "status") {
                                $query->where('orders.' . $key, (int)$request);
                            } else {
                                $query->where('orders.' . $key, 'like', '%' . $request . '%');
                            }
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('orders.order_type', '!=', $explode);
                                }
                            }
                        }
                    }
                })
                ->groupBy('orders.order_type', 'orders.currency')
                ->orderBy('orders.order_type', $orderType)
                ->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

}
