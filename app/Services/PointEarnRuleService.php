<?php

namespace App\Services;

use App\Http\Requests\PointEarnRuleRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\PointEarnRule;
use App\Models\Order;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointEarnRuleService
{
    public $pointEarnRule;
    protected $pointEarnRuleFilter = [
        'name',
        'currency_amount',
        'point',
        'is_active',
        'branch_id',
        'except'
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

            return PointEarnRule::where(function ($query) use ($requests) {
                if (isset($requests["branch_id"])) {
                    $query->where("branch_id", $requests["branch_id"]);
                }

                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->pointEarnRuleFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get all active point earn rules
     */
    public function getActive(): Collection
    {
        try {
            return PointEarnRule::where('is_active', true)->orderBy('currency_amount', 'asc')->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @throws Exception
     */
    public function show(PointEarnRule $pointEarnRule): PointEarnRule
    {
        try {
            return $pointEarnRule;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(PointEarnRuleRequest $request, PointEarnRule $pointEarnRule): PointEarnRule
    {
        try {
            DB::transaction(function () use ($request, $pointEarnRule) {
                $this->pointEarnRule = $pointEarnRule->create($request->validated());
            });
            return $this->pointEarnRule;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(PointEarnRuleRequest $request, PointEarnRule $pointEarnRule): PointEarnRule
    {
        try {
            DB::transaction(function () use ($request, $pointEarnRule) {
                $pointEarnRule->update($request->validated());
            });
            return PointEarnRule::find($pointEarnRule->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(PointEarnRule $pointEarnRule)
    {
        try {
            DB::transaction(function () use ($pointEarnRule) {
                $pointEarnRule->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Calculate points for a given amount using active rules
     */
    public function calculatePointsForAmount(float $amount): int
    {
        Log::info("Calculating points for amount: $amount");
        try {
            $activeRule = PointEarnRule::where('is_active', true)->first();
            
            if (!$activeRule) {
                Log::info("No active point earn rule found.");
                return 0;
            }

            Log::info("Active rule found: " . json_encode($activeRule));

            // Calculate points: (amount / currency_amount) * point
            return (int) floor(($amount / $activeRule->currency_amount) * $activeRule->point);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return 0;
        }
    }

    /**
     * Get point earning statistics
     */
    public function getStatistics(): array
    {
        try {
            $activeRules = PointEarnRule::where('is_active', true)->count();
            $totalRules = PointEarnRule::count();
            
            return [
                'total_rules' => $totalRules,
                'active_rules' => $activeRules,
                'inactive_rules' => $totalRules - $activeRules,
                'best_rate' => $this->getBestEarningRate(),
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return [];
        }
    }

    /**
     * Get the best earning rate (most points per currency)
     */
    private function getBestEarningRate(): ?array
    {
        try {
            $bestRule = PointEarnRule::where('is_active', true)
                ->selectRaw('*, (point / currency_amount) as rate')
                ->orderBy('rate', 'desc')
                ->first();

            if (!$bestRule) {
                return null;
            }

            return [
                'currency_amount' => $bestRule->currency_amount,
                'points' => $bestRule->point,
                'rate' => round($bestRule->point / $bestRule->currency_amount, 2),
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return null;
        }
    }
}
