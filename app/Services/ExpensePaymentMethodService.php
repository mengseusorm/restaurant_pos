<?php

namespace App\Services;

use App\Http\Requests\ExpensePaymentMethodRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\ExpensePaymentMethod;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpensePaymentMethodService
{
    public $expensePaymentMethod;
    protected $expensePaymentMethodFilter = [
        'name',
        'description',
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

            return ExpensePaymentMethod::where(function ($query) use ($requests) {
                if (isset($requests["branch_id"])) {
                    $query->where("branch_id", $requests["branch_id"]);
                }

                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->expensePaymentMethodFilter)) {
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
     * Get all active expense payment methods
     */
    public function getActive(): Collection
    {
        try {
            return ExpensePaymentMethod::active()->orderBy('name', 'asc')->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get expense payment methods for export
     */
    public function getForExport()
    {
        try {
            return ExpensePaymentMethod::with('branch')->orderBy('id', 'desc')->get();
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
    public function show(ExpensePaymentMethod $expensePaymentMethod): ExpensePaymentMethod
    {
        try {
            return $expensePaymentMethod;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ExpensePaymentMethodRequest $request, ExpensePaymentMethod $expensePaymentMethod): ExpensePaymentMethod
    {
        try {
            DB::transaction(function () use ($request, $expensePaymentMethod) {
                $this->expensePaymentMethod = $expensePaymentMethod->create($request->validated());
            });
            return $this->expensePaymentMethod;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ExpensePaymentMethodRequest $request, ExpensePaymentMethod $expensePaymentMethod): ExpensePaymentMethod
    {
        try {
            DB::transaction(function () use ($request, $expensePaymentMethod) {
                $expensePaymentMethod->update($request->validated());
            });
            return ExpensePaymentMethod::find($expensePaymentMethod->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(ExpensePaymentMethod $expensePaymentMethod)
    {
        try {
            DB::transaction(function () use ($expensePaymentMethod) {
                $expensePaymentMethod->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get expense payment method statistics
     */
    public function getStatistics(): array
    {
        try {
            $activeMethods = ExpensePaymentMethod::active()->count();
            $totalMethods = ExpensePaymentMethod::count();
            
            return [
                'total_methods' => $totalMethods,
                'active_methods' => $activeMethods,
                'inactive_methods' => $totalMethods - $activeMethods,
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return [];
        }
    }
}
