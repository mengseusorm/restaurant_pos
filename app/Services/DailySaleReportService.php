<?php


namespace App\Services;

use App\Enums\PaymentStatus;
use App\Http\Requests\PaginateRequest;
use App\Libraries\AppLibrary;
use App\Models\Order; 
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailySaleReportService
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

    protected array $exceptFilter = [
        'excepts'
    ];
    public function list(PaginateRequest $request)
    {

        try {

            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'order_date'; // Default to order_date
            $orderType = $request->get('order_by') ?? 'desc';


            $orders = Order::query()
                ->selectRaw('
                    orders.currency as order_currency,
                    DATE(orders.order_datetime) as order_date,
                    COUNT(DISTINCT orders.id) as total_orders,
                    SUM(orders.total) as total,
                    SUM(orders.total_tax) as total_tax
                ')->where('payment_status', PaymentStatus::PAID)
                ->where(function ($query) use ($requests) {
                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = AppLibrary::filterDateTime($requests['from_date'])->toDateTimeString();
                        $last_date  = AppLibrary::filterDateTime($requests['to_date'])->toDateTimeString();
                        $query->whereBetween('orders.order_datetime', [$first_date, $last_date]);
                    }

                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            if ($key === "status") {
                                $query->where('orders.' . $key, (int)$request);
                            } else if ($key === 'payment_method' && (int)$request < 0) {
                                $query->where('orders.pos_payment_method', abs($request));
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
                ->groupBy(DB::raw('DATE(orders.order_datetime)'), 'orders.currency')
                ->orderBy('order_date', $orderType)
                ->$method($methodValue);
            return $orders;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

}
