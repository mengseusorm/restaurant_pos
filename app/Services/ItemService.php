<?php

namespace App\Services;


use App\Enums\Ask;
use App\Enums\PaymentStatus;
use App\Enums\Status;
use Exception;
use App\Models\Item;
use Illuminate\Support\Str;
use App\Models\ItemVariation;
use App\Http\Requests\ItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ChangeImageRequest;
use App\Http\Resources\ItemDetailReportResource;
use App\Libraries\AppLibrary;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use item as GlobalItem;

class ItemService
{
    public $item;
    protected $itemFilter = [
        'name',
        'slug',
        'item_category_id',
        'price',
        'is_featured',
        'item_type',
        'tax_id',
        'status',
        'order',
        'description',
        'except',
        'kitchen_printer_id',
        'label_printer_id',
        'branch_id',
        'barcode',
        'last_updated',
        'can_input_custom_name',
        'can_input_custom_unit_price',
        'item_kind',
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
            return Item::with('media', 'category', 'tax')->where(function ($query) use ($requests) {

                if (isset($requests["branch_id"])) {
                    $query->where("branch_id", $requests["branch_id"]);
                }

                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } elseif ($key == "last_updated") {
                            // Filter items updated after the provided timestamp
                            $query->where('updated_at', '>', $request);
                        } else {
                            if ($key == "item_category_id") {
                                $query->where($key, $request);
                            } else {
                                Log::info($request);
                                // $query->where($key, 'like', '%' . $request . '%')->orwhere('barcode','like', '%' .$request.'%');
                                $query->where(function ($q) use ($key, $request) {
                                    $q->where($key, 'like', '%' . $request . '%')
                                      ->orWhere('barcode', 'like', '%' . $request . '%');
                                });
                            }
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
     * @throws Exception
     */
    public function store(ItemRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->item = Item::create($request->validated() + [
                    'slug' => Str::slug($request->name),

                ]);

                if ($request->image) {
                    $this->item->addMedia($request->image)->toMediaCollection('item');
                }
                if ($request->variations) {
                    $this->item->variations()->createMany(json_decode($request->variations, true));
                }
            });
            return $this->item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemRequest $request, Item $item): Item
    {
        try {
            DB::transaction(function () use ($request, $item) {
                $item->update($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $item->addMedia($request->image)->toMediaCollection('item');
                }
                if ($request->variations) {
                    $variationIdsArray    = [];
                    $variationDeleteArray = [];
                    $oldVariations        = $item->variations->pluck('id')->toArray();
                    foreach (json_decode($request->variations, true) as $variation) {
                        if (isset($variation['id'])) {
                            $variationIdsArray[] = $variation['id'];
                            ItemVariation::where('id', $variation['id'])->update([
                                'name'             => $variation['name'],
                                'price' => $variation['price'],
                            ]);
                        } else {
                            $item->variations()->create($variation);
                        }
                    }

                    if ($variationIdsArray) {
                        foreach ($oldVariations as $oldVariation) {
                            if (!in_array($oldVariation, $variationIdsArray)) {
                                $variationDeleteArray[] = $oldVariation;
                            }
                        }
                    }

                    if ($variationDeleteArray) {
                        ItemVariation::whereIn('id', $variationDeleteArray)->delete();
                    }
                }
            });
            return Item::find($item->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Item $item)
    {
        try {
            DB::transaction(function () use ($item) {
                $item->variations()->delete();
                $item->extras()->delete();
                $item->addons()->delete();
                $item->delete();
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
    public function show(Item $item): Item
    {
        try {
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeImage(ChangeImageRequest $request, Item $item): Item
    {
        try {
            if ($request->image) {
                $item->clearMediaCollection('item');
                $item->addMedia($request->image)->toMediaCollection('item');
            }
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function uploadImages(ChangeImageRequest $request)
    {
        try {
            if ($request->hasFile('files')) {
                DB::transaction(function () use ($request) {
                    foreach ($request->file('files') as $imageFile) {
                        // Extract filename without extension
                        $fileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                        // Find item by item_code matching the filename
                        $item = Item::where('item_code', $fileName)->first();

                        if ($item) {
                            // Clear existing media and upload new image
                            Log::info('Uploading image to existing item: ' . $fileName . ' (ID: ' . $item->id . ')');
                            $item->clearMediaCollection('item');
                            $item->addMedia($imageFile)->toMediaCollection('item');
                        } else {
                            // Log if no matching item found
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

    public function featuredItems()
    {
        try {
            return Item::where(['is_featured' => Ask::YES, 'status' => Status::ACTIVE])->inRandomOrder()->limit(8)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function mostPopularItems()
    {
        try {
            return Item::withCount('orders')->where(['status' => Status::ACTIVE])->orderBy('orders_count', 'desc')->limit(6)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function itemReport(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';

            $items = Item::join('order_items', 'items.id', '=', 'order_items.item_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('item_categories', 'items.item_category_id', '=', 'item_categories.id')
                ->select(
                    'orders.currency as order_currency',
                    'items.id',
                    'items.name',
                    'item_categories.name as category_name',
                    'item_categories.id as category_id',
                    DB::raw('SUM(order_items.quantity) as total_ordered_qty'),
                    DB::raw('SUM(order_items.tax_amount) as total_tax'),
                    DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                    DB::raw('(SUM(order_items.total_price)) as current_total_price'),
                    DB::raw('(SUM(order_items.total_price + order_items.tax_amount)) as current_total_price_with_tax'),
                )->where('payment_status', PaymentStatus::PAID)
                ->where(function ($query) use ($requests) {

                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = AppLibrary::filterDateTime($requests['from_date']);
                        $last_date  = AppLibrary::filterDateTime($requests['to_date']);

                        $query->whereBetween('orders.created_at', [$first_date, $last_date]);
                    } else {
                        $branch = \App\Models\Branch::find(auth()->user()->branch_id ?? 1);

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

                        $query->whereBetween('orders.created_at', [$first_date, $last_date]);
                    }

                    if (!empty($requests['name']) || !empty($requests['item_name'])) {
                        $name = $requests['name'] ?? $requests['item_name'];
                        $query->where('items.name', 'like', '%' . $name . '%');
                    }

                    $itemFilters = ['description', 'price', 'code', 'item_code'];

                    foreach ($requests as $key => $value) {
                        if (in_array($key, $itemFilters) && !empty($value)) {
                            if ($key === 'description') {
                                $query->where('items.description', 'like', '%' . $value . '%');
                            } elseif ($key === 'price') {
                                $query->where('items.price', 'like', '%' . $value . '%');
                            } elseif ($key === 'code' || $key === 'item_code') {
                                $query->where('items.code', 'like', '%' . $value . '%');
                            } else if ($key === 'item_type' || $key === 'item_code') {
                                $query->where('items.item_type', 'like', '%' . $value . '%');
                            }
                        } elseif ($key === 'except' && !empty($value)) {
                            $excludes = explode('|', $value);
                            $query->whereNotIn('items.id', array_filter($excludes, 'is_numeric'));
                        }
                    }
                })
                ->groupBy('items.id', 'orders.currency')
                ->$method($methodValue);
            return $items;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function itemReportDetailList(PaginateRequest $request)
    {
        try {  
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $tableNamesSubquery = $this->tableNamesSubquery();

            return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('items', 'order_items.item_id', '=', 'items.id')
                ->leftJoin('payment_methods', 'orders.payment_method', '=', 'payment_methods.id')
                ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
                ->select(
                    'orders.invoice_number as invoice_number',
                    'orders.order_serial_no',
                    'orders.created_at as order_date',
                    'items.item_code as item_code',
                    'items.name as item_name',
                    'order_items.quantity',
                    'order_items.price',
                    'order_items.total_price',
                    DB::raw('(order_items.price * order_items.quantity) as sub_total'),
                    'order_items.discount',
                    'order_items.discount_percentage',
                    'order_items.tax_amount', 
                    'orders.pos_received_amount as dollar_amount', 
                    'orders.customer_name',
                    'orders.order_type', 
                    'payment_methods.name as payment_method',
                    'branches.name as branch_name',
                    'orders.change_amount as change',
                    DB::raw('(orders.pos_received_amount - orders.total) as change_amount'),
                    DB::raw('CASE WHEN orders.currency = "KHR" THEN orders.pos_received_amount ELSE 0 END as riel_amount'),
                    DB::raw("({$tableNamesSubquery}) as table_names"),
                )->where('orders.payment_status', PaymentStatus::PAID)
                ->where(function ($query) use ($requests) {
                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = AppLibrary::filterDateTime($requests['from_date']);
                        $last_date = AppLibrary::filterDateTime($requests['to_date']);
                        $query->whereBetween('orders.created_at', [$first_date, $last_date]);
                    } else {
                        $branch = \App\Models\Branch::find(auth()->user()->branch_id ?? 1);
                        $first_date = \Carbon\Carbon::yesterday()->startOfDay();

                        if ($branch && $branch->open_time) {
                            $time = explode(':', $branch->open_time);
                            $first_date->setTime((int)$time[0], (int)$time[1], 0);
                        }

                        $last_date = \Carbon\Carbon::today()->startOfDay();
                        if ($branch && $branch->close_time) {
                            $time = explode(':', $branch->close_time);
                            $last_date->setTime((int)$time[0], (int)$time[1], 59);
                        } else {
                            $last_date->endOfDay();
                        }

                        $query->whereBetween('orders.created_at', [$first_date, $last_date]);
                    }

                    if (auth()->user()->branch_id) {
                        $query->where('orders.branch_id', auth()->user()->branch_id);
                    }

                    $itemFilters = ['name'];

                    foreach ($requests as $key => $value) {
                        if (in_array($key, $itemFilters) && !empty($value)) {
                            if ($key === 'name') {
                                $query->where('items.name', 'like', "%{$value}%")
                                    ->orWhere('items.item_code', 'like', "%{$value}%")
                                    ->orWhere('orders.order_serial_no', 'like', "%{$value}%")
                                    ->orWhere('orders.created_at', 'like', "%{$value}%")
                                    ->orWhere('order_items.quantity', 'like', "%{$value}%")
                                    ->orWhere('order_items.price', 'like', "%{$value}%")
                                    ->orWhere('order_items.total_price', 'like', "%{$value}%")
                                    ->orWhere('order_items.discount', 'like', "%{$value}%")
                                    ->orWhere('order_items.discount_percentage', 'like', "%{$value}%")
                                    ->orWhere('order_items.tax_amount', 'like', "%{$value}%")
                                    ->orWhere('orders.pos_received_amount', 'like', "%{$value}%")
                                    ->orWhere('orders.currency', 'like', "%{$value}%")
                                    ->orWhere('orders.total', 'like', "%{$value}%")
                                    ->orWhere('orders.subtotal', 'like', "%{$value}%")
                                    ->orWhere('orders.discount', 'like', "%{$value}%")
                                    ->orWhere('orders.customer_name', 'like', "%{$value}%")
                                    ->orWhere('orders.order_type', 'like', "%{$value}%")
                                    ->orWhere('orders.change_amount', 'like', "%{$value}%")
                                    ->orWhere('payment_methods.name', 'like', "%{$value}%")
                                    ->orWhere('branches.name', 'like', "%{$value}%");
                            }
                        } elseif ($key === 'except' && !empty($value)) {
                            $excludes = explode('|', $value);
                            $query->whereNotIn('items.id', array_filter($excludes, 'is_numeric'));
                        }
                    }
                })->$method($methodValue);  
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function tableNamesSubquery(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "SELECT GROUP_CONCAT(dt.name, ', ') FROM order_dinings od LEFT JOIN dining_tables dt ON od.dining_table_id = dt.id WHERE od.order_id = orders.id";
        }

        if ($driver === 'pgsql') {
            return "SELECT STRING_AGG(dt.name, ', ') FROM order_dinings od LEFT JOIN dining_tables dt ON od.dining_table_id = dt.id WHERE od.order_id = orders.id";
        }

        return "SELECT GROUP_CONCAT(dt.name SEPARATOR ', ') FROM order_dinings od LEFT JOIN dining_tables dt ON od.dining_table_id = dt.id WHERE od.order_id = orders.id";
    }
}
