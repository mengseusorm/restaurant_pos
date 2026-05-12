<?php

namespace App\Services;

use App\Models\PaywayTransaction;
use App\Http\Requests\PaginateRequest;
use Exception;
use Illuminate\Support\Facades\Log;

class PaywayTransactionService
{
    protected array $transactionFilter = [
        'tran_id',
        'order_id',
        'payment_status',
        'payment_status_code',
    ];

    /**
     * Get list of PayWay transactions with pagination and filters
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('per_page', 10);
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            return PaywayTransaction::with(['order', 'branch', 'paymentMethod'])->where(function ($query) use ($requests) {
                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $query->whereBetween('created_at', [
                            date('Y-m-d H:i:s', strtotime($requests['from_date'])),
                            date('Y-m-d H:i:s', strtotime($requests['to_date']))
                        ]);
                    }
        
                    // Apply filters
                    foreach ($requests as $key => $value) {
                        if (in_array($key, $this->transactionFilter) && !empty($value)) {
                            if ($key === 'payment_status_code') {
                                $query->where($key, (int)$value);
                            } else {
                                $query->where($key, 'like', '%' . $value . '%');
                            }
                        }
                    } 
                    // Apply branch filter if provided
                    if (!empty($requests['branch_id'])) {
                        $query->where('branch_id', $requests['branch_id']);
                    } 
            })->orderBy($orderColumn, $orderType)
            ->$method($methodValue);  

        } catch (Exception $exception) {
            Log::error('PaywayTransactionService list error: ' . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get single PayWay transaction
     */
    public function show(PaywayTransaction $paywayTransaction)
    {
        try {
            // Load all relationships including the transaction
            $paywayTransaction->load([
                'order' => function ($query) {
                    $query->with(['orderStatus', 'orderType']);
                },
                'branch',
                'paymentMethod',
                'transaction' // This uses the relationship defined in PaywayTransaction model
            ]);

            return $paywayTransaction;
        } catch (Exception $exception) {
            Log::error('PaywayTransactionService show error: ' . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Export PayWay transactions to Excel
     */
    public function export(PaginateRequest $request)
    {
        try {
            return $this->list($request);
        } catch (Exception $exception) {
            Log::error('PaywayTransactionService export error: ' . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
