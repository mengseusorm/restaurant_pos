<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;

class UserSaleReportService
{
    public $userSaleFilter = [];
    public $exceptFilter = [];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'total_orders';
            $orderType   = $request->get('order_type') ?? 'desc';

            $results = DB::table('orders')
                ->join('users as u', 'orders.order_user_id', '=', 'u.id')
                ->select(
                    'orders.currency as order_currency',
                    'u.id as user_id',
                    'u.name as user_name',
                    DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                    DB::raw('SUM(orders.total) as total'),
                    DB::raw('SUM(orders.total_tax) as total_tax')
                )
                ->where(function ($query) use ($requests) {
                    if(isset($requests['from_date']) && !empty($requests['from_date'])) {
                        $first_date = date('Y-m-d H:i:s', strtotime($requests['from_date']));
                        if(isset($requests['to_date']) && !empty($requests['to_date'])) {
                            $last_date = date('Y-m-d H:i:s', strtotime($requests['to_date']));
                        } else {
                            $last_date = date('Y-m-d 23:59:59');
                        }
                        $query->whereBetween('orders.created_at', [$first_date, $last_date]);
                    } else {
                        $today = date('Y-m-d');
                        $query->whereDate('orders.created_at', '=', $today);
                    }

                    // Filter by specific user if provided
                    if(isset($requests['user_id']) && !empty($requests['user_id'])) {
                        $query->where('u.id', $requests['user_id']);
                    }

                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->userSaleFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        }
                    }
                })
                ->whereNotNull('orders.order_user_id') // Only include orders with a staff member assigned
                ->groupBy('u.id', 'u.name', 'orders.currency')
                ->orderBy($orderColumn, $orderType)
                ->$method($methodValue); 

            return $results;     
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        } 
    }
}
