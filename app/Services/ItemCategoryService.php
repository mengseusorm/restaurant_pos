<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Http\Requests\ChangeImageRequest;
use Exception;
use Illuminate\Support\Str;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\AppLibrary;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\ItemCategoryRequest;
use App\Models\Item;
use App\Traits\DefaultAccessModelTrait;
use Carbon\Carbon;

class ItemCategoryService
{
    use DefaultAccessModelTrait;
    protected $itemCateFilter = [
        'name',
        'slug',
        'description',
        'status',
        'branch_id',
        'last_updated'
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
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return ItemCategory::with('media')->withCount('items')
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->itemCateFilter)) {
                            if ($key == 'last_updated') {
                                // Filter categories updated after the provided timestamp
                                $query->where('updated_at', '>', $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
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
                })->orderBy('sort', $orderType)->$method(
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
    public function store(ItemCategoryRequest $request)
    {
        try {
            $itemCategory = ItemCategory::create($request->validated() + ['slug' => Str::slug($request->name)]);
            if ($request->image) {
                $itemCategory->addMediaFromRequest('image')->toMediaCollection('item-category');
            }
            return $itemCategory;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemCategoryRequest $request, ItemCategory $itemCategory): ItemCategory
    {
        try {
            $itemCategory->update($request->validated() + ['slug' => Str::slug($request->name)]);
            if ($request->image) {
                $itemCategory->clearMediaCollection('item-category');
                $itemCategory->addMediaFromRequest('image')->toMediaCollection('item-category');
            }
            return $itemCategory;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(ItemCategory $itemCategory)
    {
        try {
            // $checkItem = $itemCategory->items->whereNull('deleted_at');
            // if (!blank($checkItem)) {
            //     $itemCategory->delete();
            // } else {
            //     DB::statement('SET FOREIGN_KEY_CHECKS=0');
            //     $itemCategory->delete();
            //     DB::statement('SET FOREIGN_KEY_CHECKS=1');
            // }
            $checkItem = $itemCategory->items()->whereNull('deleted_at')->exists();

            if ($checkItem) {
                throw new Exception('Cannot delete itemCategory because it is being used by one or more items.', 422);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $itemCategory->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroyFromItem(ItemCategory $itemCategory)
    {
        try {
            Item::where('item_category_id', $itemCategory->id)->update(['item_category_id' => 0]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(ItemCategory $itemCategory)
    {
        try {
            return $itemCategory;
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

    public function itemCategoryReport(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'total_items';
            $orderType   = $request->get('order_type') ?? 'desc';


            return DB::table('orders as o')
                ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
                ->join('items as i', 'oi.item_id', '=', 'i.id')
                ->join('item_categories as ic', 'i.item_category_id', '=', 'ic.id')
                ->join('branches as b', 'o.branch_id', '=', 'b.id')
                ->select(
                    'o.currency as order_currency',
                    'ic.id as category_id',
                    'ic.name as category_name',
                    'b.id as branch_id',
                    'b.name as branch_name',
                    DB::raw('SUM(oi.tax_amount) as total_tax'),
                    DB::raw('COUNT(oi.id) as total_items'),
                    DB::raw('SUM(oi.price * oi.quantity) as total_price'),

                    DB::raw('COUNT(DISTINCT o.id) as total_orders')
                )->where('payment_status', PaymentStatus::PAID)
                ->where(function ($query) use ($requests) {
                    $query->where('o.branch_id', $this->branch());

                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = AppLibrary::filterDateTime($requests['from_date']);
                        $last_date  = AppLibrary::filterDateTime($requests['to_date']);
                        $query->whereBetween('o.created_at', [$first_date, $last_date]);
                    } else {
                        // Default to yesterday to today with branch times
                        $branch = \App\Models\Branch::find($this->branch());

                        $first_date = Carbon::yesterday()->startOfDay();
                        if ($branch && $branch->open_time) {
                            $time = explode(':', $branch->open_time);
                            $first_date->setTime((int)$time[0], (int)$time[1], 0);
                        }

                        $last_date = Carbon::today()->startOfDay();
                        if ($branch && $branch->close_time) {
                            $time = explode(':', $branch->close_time);
                            $last_date->setTime((int)$time[0], (int)$time[1], 59);
                        } else {
                            $last_date->endOfDay();
                        }

                        $query->whereBetween('o.created_at', [$first_date, $last_date]);
                    }

                    if (isset($requests['item_category_id'])) {
                        $query->where('ic.id', $requests['item_category_id']);
                    }

                foreach ($requests as $key => $request) {
                        if (in_array($key, $this->itemCateFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        }
                    }
                }) ->groupBy('ic.id','ic.name', 'o.currency', 'b.id', 'b.name')
                ->orderBy('ic.sort', 'desc')
                ->{$method}($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function applyItemCategoryPrinter(Request $request)
    {
        try {
            return Item::where('item_category_id', $request->id)->update([
                'kitchen_printer_id' => $request->kitchen_printer_id
            ]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function uploadImages(ChangeImageRequest $request)
    {
        try {
            if ($request->hasFile('files')) {
                DB::transaction(function () use ($request) {
                    foreach ($request->file('files') as $imageFile) {
                        $fileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $itemCategory = ItemCategory::where('item_category_code', $fileName)->first();
                        if ($itemCategory) {
                            $itemCategory->clearMediaCollection('item-category');
                            $itemCategory->addMedia($imageFile)->toMediaCollection('item-category');
                        } else {
                            Log::warning('No item found with item_code: ' . $fileName);
                        }
                    }
                });
            }
        } catch (Exception $exception) {
            Log::info('Upload Images Error: ' . $exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
