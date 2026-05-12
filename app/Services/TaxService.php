<?php

namespace App\Services;

use Exception;
use App\Models\Tax;
use App\Enums\TaxType;
use App\Http\Requests\TaxRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Models\Item;

class TaxService
{
    protected array $taxFilter = [
        'name',
        'code',
        'tax_rate',
        'type',
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

            return Tax::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->taxFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                }
            })->withCount('items')->orderBy($orderColumn, $orderType)->$method(
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
    public function store(TaxRequest $request)
    {
        try {
            return Tax::create($request->validated() + ['type' => TaxType::PERCENTAGE]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(TaxRequest $request, Tax $tax)
    {
        try {
            return tap($tax)->update($request->validated());
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Tax $tax): void
    {
        try {
            // $checkItem = $tax->items->whereNull('deleted_at');

            // if (!blank($checkItem)) {
            //     $tax->delete();
            // } else {

            //     DB::statement('SET FOREIGN_KEY_CHECKS=0');
            //     $tax->delete();
            //     DB::statement('SET FOREIGN_KEY_CHECKS=1');
            // }

            $checkItem = $tax->items()->whereNull('deleted_at')->exists();

            if ($checkItem) {
                throw new Exception('Cannot delete tax because it is being used by one or more items.', 422);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $tax->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (Exception $exception) {
            Log::info(QueryExceptionLibrary::message($exception));
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroyFromItem(Tax $tax): void
    {
        try {
            $items = Item::where('tax_id', $tax->id)->whereNull('deleted_at')->get();

            foreach ($items as $item) {
                $item->update([
                    'tax_id' => null,
                    'tax_name' => null,
                    'tax_rate' => 0,
                    'tax_type' => null,
                    'tax_amount' => 0,
                    'price_with_tax' => $item->price
                ]);
            }
        } catch (Exception $exception) {
            Log::info(QueryExceptionLibrary::message($exception));
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function applyTaxToAllItem(Tax $tax): void
    {
        try {
            $items = Item::whereNull('deleted_at')->get();
            if ($items->isEmpty()) {
                throw new Exception('No items found to apply tax.', 422);
            }
            // Update each item with the tax details
            foreach ($items as $item) {
                $taxRate = $tax->tax_rate ?? 0;
                $taxType = $tax->type;
                $taxAmount = $taxType === TaxType::FIXED ? $taxRate : ($item->price * $taxRate) / 100;
                $priceWithTax = $item->price + $taxAmount;

                Log::info("Updating item ID: {$item->id} with tax details: Name: {$tax->name}, Rate: {$taxRate}, Type: {$taxType}, Amount: {$taxAmount}, Price with Tax: {$priceWithTax}");

                $item->update([
                    'tax_id' => $tax->id,
                    'tax_name' => $tax->name,
                    'tax_rate' => $taxRate,
                    'tax_type' => $taxType,
                    'tax_amount' => $taxAmount,
                    'price_with_tax' => $priceWithTax,
                ]);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }


    /**
     * @throws Exception
     */
    public function show(Tax $tax): Tax
    {
        try {
            return $tax;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
