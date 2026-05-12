<?php

namespace App\Services;


use Exception;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\ItemStockRequest;
use App\Models\ItemStock;

class ItemStockService
{
    protected $itemCateFilter = [
        'name',
        'price',
        'description',
        'branch_id'
    ];

    protected $exceptFilter = [
        'excepts'
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

            return ItemStock::with('branch')
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->itemCateFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            foreach (explode('|', $request) as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        }
                    }
                })->orderBy('id', 'desc')->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ItemStockRequest $request)
    {
        try {
            $itemStock = ItemStock::create([
                'name'        => $request->name,
                'description' => $request->description,
                'branch_id'   => $request->branch_id
            ]);
            return $itemStock;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemStockRequest $request,$id)
    {
        try {
            $itemStock = ItemStock::find($id);
            $itemStock->update($request->validated());
            return $itemStock;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy($id)
    {
        try {
            $itemStock = ItemStock::find($id);
            if (!blank($itemStock)) {
                $itemStock->delete();
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $itemStock->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(ItemStock $itemStock)
    {
        try {
            return $itemStock;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function sortCategory(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                foreach ($request->category_id as $index => $id) {
                    ItemCategory::where('id', $id)->update(['sort' => $index + 1]);
                }
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
