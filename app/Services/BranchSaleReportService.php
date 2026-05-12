<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Http\Requests\BranchRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\Branch;
use Exception;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;
use App\Libraries\QueryExceptionLibrary;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BranchSaleReportService
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
    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {

            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderType = $request->get('order_by') ?? 'desc';
 

            $orders = DB::table('orders')
                ->join('branches', 'orders.branch_id', '=', 'branches.id') 
                ->selectRaw('
                    orders.currency as order_currency,
                    branches.id as branch_id,
                    branches.name as branch_name,
                    COUNT(DISTINCT orders.id) as total_orders,
                    SUM(orders.total) as total,
                    SUM(orders.total_tax) as total_tax
                ')->where('payment_status', PaymentStatus::PAID)
                ->where(function ($query) use ($requests) {  
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
                            } else if ($key === 'payment_method' && (int)$request < 0) {
                                $query->where('orders.pos_payment_method', abs($request));
                            } else if ( $key === 'branch_id' ) { 
                                $query->where('branches.id', (int)$request);
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
                })->groupBy('branches.id', 'branches.name', 'orders.currency')
                    ->orderBy('branches.id', $orderType)
                    ->$method($methodValue);  
            return $orders;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
