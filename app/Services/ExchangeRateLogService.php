<?php

namespace App\Services;

use Exception;
use App\Models\ExchangeRateLog;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;

class ExchangeRateLogService
{
    protected $exchangeRateLogFilter = [
        'base_currency',
        'target_currency',
        'source',
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return ExchangeRateLog::with(['baseCurrencyModel', 'targetCurrencyModel', 'creator'])
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->exchangeRateLogFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                })
                ->orderBy($orderColumn, $orderType)
                ->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
