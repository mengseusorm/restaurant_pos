<?php

namespace App\Services;

use Exception;
use App\Models\ExchangeRate;
use App\Models\ExchangeRateLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ExchangeRateRequest;
use App\Http\Requests\PaginateRequest;

class ExchangeRateService
{
    protected $exchangeRateFilter = [
        'base_currency',
        'target_currency',
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

            return ExchangeRate::with(['baseCurrencyModel', 'targetCurrencyModel'])
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->exchangeRateFilter)) {
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

    /**
     * @throws Exception
     */
    public function store(ExchangeRateRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();
                
                // Create or update the exchange rate
                $exchangeRate = ExchangeRate::updateOrCreate(
                    [
                        'base_currency' => $validated['base_currency'],
                        'target_currency' => $validated['target_currency'],
                    ],
                    [
                        'rate' => $validated['rate'],
                        'effective_at' => $validated['effective_at'] ?? now(),
                    ]
                );

                // Log the rate change
                ExchangeRateLog::logRate(
                    $validated['base_currency'],
                    $validated['target_currency'],
                    $validated['rate'],
                    $validated['source'] ?? 'manual',
                    auth()->id()
                );

                return $exchangeRate->fresh(['baseCurrencyModel', 'targetCurrencyModel']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ExchangeRateRequest $request, ExchangeRate $exchangeRate)
    {
        try {
            return DB::transaction(function () use ($request, $exchangeRate) {
                $validated = $request->validated();
                
                // Update the exchange rate
                $exchangeRate->update([
                    'rate' => $validated['rate'],
                    'effective_at' => $validated['effective_at'] ?? now(),
                ]);

                // Log the rate change
                ExchangeRateLog::logRate(
                    $exchangeRate->base_currency,
                    $exchangeRate->target_currency,
                    $validated['rate'],
                    $validated['source'] ?? 'manual',
                    auth()->id()
                );

                return $exchangeRate->fresh(['baseCurrencyModel', 'targetCurrencyModel']);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(ExchangeRate $exchangeRate): void
    {
        try {
            DB::transaction(function () use ($exchangeRate) {
                // Log the deletion
                ExchangeRateLog::create([
                    'base_currency' => $exchangeRate->base_currency,
                    'target_currency' => $exchangeRate->target_currency,
                    'rate' => $exchangeRate->rate,
                    'source' => 'deleted',
                    'created_by' => auth()->id(),
                ]);

                // Delete the exchange rate
                $exchangeRate->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
