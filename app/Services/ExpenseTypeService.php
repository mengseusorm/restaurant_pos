<?php

namespace App\Services;

use App\Http\Requests\ExpenseTypeRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\ExpenseType;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseTypeService
{
    public $expenseType;
    protected $expenseTypeFilter = [
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

            return ExpenseType::where(function ($query) use ($requests) {
                if (isset($requests["branch_id"])) {
                    $query->where("branch_id", $requests["branch_id"]);
                }

                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->expenseTypeFilter)) {
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
     * Get all active expense types
     */
    public function getActive(): Collection
    {
        try {
            return ExpenseType::active()->orderBy('name', 'asc')->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get expense types for export
     */
    public function getForExport()
    {
        try {
            return ExpenseType::with('branch')->orderBy('id', 'desc')->get();
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
    public function show(ExpenseType $expenseType): ExpenseType
    {
        try {
            return $expenseType;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ExpenseTypeRequest $request, ExpenseType $expenseType): ExpenseType
    {
        try {
            DB::transaction(function () use ($request, $expenseType) {
                $this->expenseType = $expenseType->create($request->validated());
            });
            return $this->expenseType;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ExpenseTypeRequest $request, ExpenseType $expenseType): ExpenseType
    {
        try {
            DB::transaction(function () use ($request, $expenseType) {
                $expenseType->update($request->validated());
            });
            return ExpenseType::find($expenseType->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(ExpenseType $expenseType)
    {
        try {
            DB::transaction(function () use ($expenseType) {
                $expenseType->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get expense type statistics
     */
    public function getStatistics(): array
    {
        try {
            $activeTypes = ExpenseType::active()->count();
            $totalTypes = ExpenseType::count();
            
            return [
                'total_types' => $totalTypes,
                'active_types' => $activeTypes,
                'inactive_types' => $totalTypes - $activeTypes,
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return [];
        }
    }
}
