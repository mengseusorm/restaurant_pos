<?php

namespace App\Services;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PointUsageRuleRequest;
use App\Models\PointUsageRule;
use App\Models\Member;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointUsageRuleService
{
    public $pointUsageRule;
    protected $pointUsageRuleFilter = [
        'name',
        'usage_type',
        'point_to_currency',
        'min_point_usage',
        'max_point_usage',
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

            return PointUsageRule::with('branch')->where(function ($query) use ($requests) {
                if (isset($requests["branch_id"])) {
                    $query->where("branch_id", $requests["branch_id"]);
                }

                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->pointUsageRuleFilter)) {
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
     * Get paginated point usage rules for index method
     */
    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = PointUsageRule::with('branch');

            // Apply filters
            if (!empty($filters['is_active'])) {
                $query->where('is_active', $filters['is_active'] === 'true');
            }

            if (!empty($filters['usage_type'])) {
                $query->where('usage_type', $filters['usage_type']);
            }

            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('point_to_currency', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('min_point_usage', 'like', '%' . $filters['search'] . '%');
                });
            }

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get all active point usage rules
     */
    public function getActive(): Collection
    {
        try {
            return PointUsageRule::where('is_active', true)->orderBy('name', 'asc')->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get active rules by usage type
     */
    public function getActiveByType(string $usageType): Collection
    {
        try {
            return PointUsageRule::where('is_active', true)
                ->where('usage_type', $usageType)
                ->orderBy('point_to_currency', 'desc')
                ->get();
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
    public function show(PointUsageRule $pointUsageRule): PointUsageRule
    {
        try {
            return $pointUsageRule;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(array $data): PointUsageRule
    {
        try {
            DB::transaction(function () use ($data) {
                $data['branch_id'] = $data['branch_id'] ?? auth()->user()->branch_id ?? 1;
                $this->pointUsageRule = PointUsageRule::create($data);
            });
            return $this->pointUsageRule;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(PointUsageRule $pointUsageRule, array $data): PointUsageRule
    {
        try {
            DB::transaction(function () use ($pointUsageRule, $data) {
                $pointUsageRule->update($data);
            });
            return PointUsageRule::find($pointUsageRule->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(PointUsageRule $pointUsageRule)
    {
        try {
            DB::transaction(function () use ($pointUsageRule) {
                $pointUsageRule->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function toggleStatus(PointUsageRule $pointUsageRule): PointUsageRule
    {
        try {
            DB::transaction(function () use ($pointUsageRule) {
                $pointUsageRule->update(['is_active' => !$pointUsageRule->is_active]);
            });
            return PointUsageRule::find($pointUsageRule->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Calculate currency value for points
     */
    public function calculateCurrencyForPoints(int $points, string $usageType = 'deduct_order'): float
    {
        try {
            $rule = PointUsageRule::where('is_active', true)
                ->where('usage_type', $usageType)
                ->where('min_point_usage', '<=', $points)
                ->where(function ($q) use ($points) {
                    $q->whereNull('max_point_usage')
                      ->orWhere('max_point_usage', '>=', $points);
                })
                ->first();

            if (!$rule) {
                return 0;
            }

            return $rule->calculateCurrencyForPoints($points);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return 0;
        }
    }

    /**
     * Validate point usage for member
     */
    public function canUsePoints(Member $member, int $points, string $usageType = 'deduct_order'): array
    {
        try {
            $result = [
                'can_use' => false,
                'message' => '',
                'currency_value' => 0,
                'rule' => null,
            ];

            // Check if member has enough points
            if ($member->point_balance < $points) {
                $result['message'] = 'Insufficient points balance';
                return $result;
            }

            // Find applicable rule
            $rule = PointUsageRule::where('is_active', true)
                ->where('usage_type', $usageType)
                ->where('min_point_usage', '<=', $points)
                ->where(function ($q) use ($points) {
                    $q->whereNull('max_point_usage')
                      ->orWhere('max_point_usage', '>=', $points);
                })
                ->first();

            if (!$rule) {
                $result['message'] = 'No applicable usage rule found';
                return $result;
            }

            if (!$rule->isValidPointAmount($points)) {
                $result['message'] = "Points must be between {$rule->min_point_usage} and " . 
                                    ($rule->max_point_usage ?? 'unlimited');
                return $result;
            }

            $result['can_use'] = true;
            $result['currency_value'] = $rule->calculateCurrencyForPoints($points);
            $result['rule'] = $rule;
            $result['message'] = 'Points can be used';

            return $result;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return [
                'can_use' => false,
                'message' => 'An error occurred while validating points usage',
                'currency_value' => 0,
                'rule' => null,
            ];
        }
    }

    /**
     * Get usage statistics
     */
    public function getStatistics(): array
    {
        try {
            $totalRules = PointUsageRule::count();
            $activeRules = PointUsageRule::where('is_active', true)->count();
            
            $byType = PointUsageRule::where('is_active', true)
                ->selectRaw('usage_type, count(*) as count')
                ->groupBy('usage_type')
                ->pluck('count', 'usage_type')
                ->toArray();

            return [
                'total_rules' => $totalRules,
                'active_rules' => $activeRules,
                'inactive_rules' => $totalRules - $activeRules,
                'by_type' => $byType,
                'best_conversion_rate' => $this->getBestConversionRate(),
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return [];
        }
    }

    /**
     * Get the best conversion rate (most currency per point)
     */
    private function getBestConversionRate(): ?array
    {
        try {
            $bestRule = PointUsageRule::where('is_active', true)
                ->orderBy('point_to_currency', 'desc')
                ->first();

            if (!$bestRule) {
                return null;
            }

            return [
                'name' => $bestRule->name,
                'usage_type' => $bestRule->usage_type,
                'point_to_currency' => $bestRule->point_to_currency,
                'rate_description' => "1 point = {$bestRule->point_to_currency} currency",
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return null;
        }
    }

    /**
     * Bulk update status for multiple rules
     *
     * @param array $ids
     * @param bool $status
     * @return bool
     */
    public function bulkUpdateStatus(array $ids, bool $status): bool
    {
        return DB::transaction(function () use ($ids, $status) {
            try {
                PointUsageRule::whereIn('id', $ids)->update(['is_active' => $status]);
                
                Log::info('Bulk status update completed for point usage rules', [
                    'ids' => $ids,
                    'status' => $status,
                    'count' => count($ids)
                ]);
                
                return true;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw $exception;
            }
        });
    }

    /**
     * Get rules for export
     */
    public function getForExport(): Collection
    {
        try {
            $rules = PointUsageRule::with('branch')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($rule) {
                    return [
                        'name' => $rule->name,
                        'usage_type' => $rule->usage_type,
                        'point_value' => $rule->point_value,
                        'currency_value' => $rule->currency_value,
                        'point_to_currency' => $rule->point_to_currency,
                        'minimum_points' => $rule->minimum_points,
                        'maximum_points' => $rule->maximum_points,
                        'valid_from' => $rule->valid_from?->format('Y-m-d'),
                        'valid_until' => $rule->valid_until?->format('Y-m-d'),
                        'branch' => $rule->branch?->name,
                        'is_active' => $rule->is_active ? 'Active' : 'Inactive',
                        'created_at' => $rule->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            Log::info('Export data prepared for point usage rules', [
                'count' => $rules->count()
            ]);

            return $rules;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return collect([]);
        }
    }

    /**
     * Get usage type options
     */
    public function getUsageTypeOptions(): array
    {
        try {
            $options = [
                'deduct_order' => 'Deduct from Order',
                'exchange_gift' => 'Exchange for Gift',
            ];

            Log::info('Usage type options retrieved', [
                'count' => count($options)
            ]);

            return $options;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return [];
        }
    }
}
