<?php

namespace App\Services;

use Exception;
use App\Models\ItemAttributeVariation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ItemAttributeVariationRequest;

class ItemAttributeVariationService
{
    public $itemAttributeVariation;
    protected $itemAttributeVariationFilter = [
        'item_attribute_id',
        'name',
        'status'
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

            return ItemAttributeVariation::with('itemAttribute')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemAttributeVariationFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
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
     * @throws Exception
     */
    public function show(ItemAttributeVariation $itemAttributeVariation): ItemAttributeVariation
    {
        try {
            return $itemAttributeVariation->load('itemAttribute');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ItemAttributeVariationRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->itemAttributeVariation = ItemAttributeVariation::create($request->validated());
            });
            return $this->itemAttributeVariation->load('itemAttribute');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemAttributeVariationRequest $request, ItemAttributeVariation $itemAttributeVariation): ItemAttributeVariation
    {
        try {
            DB::transaction(function () use ($request, $itemAttributeVariation) {
                $itemAttributeVariation->update($request->validated());
            });
            return $itemAttributeVariation->load('itemAttribute');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(ItemAttributeVariation $itemAttributeVariation)
    {
        try {
            DB::transaction(function () use ($itemAttributeVariation) {
                $itemAttributeVariation->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
