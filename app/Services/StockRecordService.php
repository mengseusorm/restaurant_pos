<?php

namespace App\Services;


use Exception;
use Illuminate\Support\Str; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary; 
use App\Http\Requests\StockRecordRequest;
// use App\Models\ItemStock;
use App\Enums\PaymentStatus;

use App\Models\StockRecord;

class StockRecordService
{


    protected $StockRecordFilter = [
        'item_id',
        'stock_id',
        'user_id',
        'order_id',
        'quantity'
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
            $orderType   = $request->get('order_by') ?? 'desc';

            $stockReports = StockRecord::with('item') 
            ->where(function ($query) use ($requests) {    

                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = Date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = Date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('created_at', '>=', $first_date)->whereDate(
                        'created_at',
                        '<=',
                        $last_date
                    );
                } 

                if(isset($requests['name'])){
                    $query->whereHas('item', function($q) use ($requests) {
                        $q->where('name', 'like', '%' . $requests['name'] . '%');
                    });
                }
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->StockRecordFilter)) { 
                        $query->where($key, 'like', '%' . $request . '%'); 
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('order_type', '!=', $explode);
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );     
            return $stockReports;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(StockRecordRequest $request)
    {
        try { 
            
           $StockRecord = StockRecord::create([
                'item_id' => $request->item_id,
                'stock_id' => $request->stock_id,
                'user_id' => $request->user_id,
                'quantity' => $request->quantity,
                'record_type' => 'STOCK_IN',
                'from_warehouse_id' => null,
                'to_warehouse_id' => null
            ]);

            return $StockRecord;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function storeStockTransfer(StockRecordRequest $request)
    {
        try { 
            // Validate request
            if(!$request->validated()){
                throw new Exception('Invalid request data', 422);
            } 

            // Create stock out record
            $stockOutRecord = StockRecord::create([
                'item_id' => $request->item_id,
                'stock_id' => $request->from_warehouse_id,
                'user_id' => $request->user_id,
                'quantity' => $request->quantity,
                'record_type' => 'STOCK_OUT',
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id]);

            // Create stock in record
            $stockInRecord = StockRecord::create([
                'item_id' => $request->item_id,
                'stock_id' => $request->to_warehouse_id,
                'user_id' => $request->user_id,
                'quantity' => $request->quantity,
                'record_type' => 'STOCK_IN',
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id]);

            return [
                'stock_out' => $stockOutRecord,
                'stock_in' => $stockInRecord
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(StockRecordRequest $request, $id)
    {
        try {
            $StockRecord = StockRecord::find($id);
            $StockRecord->update($request->validated()); 
            return $StockRecord;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy($StockRecord)
    {
        try {
            $checkItem = StockRecord::find($StockRecord);

            if (!blank($checkItem)) {
                $checkItem->delete();
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $checkItem->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
            return $checkItem;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(StockRecord $StockRecord)
    {
        try {
            return $StockRecord;
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
                    StockRecord::where('id', $id)->update(['sort' => $index + 1]);
                }
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
    public function cutstock(StockRecordRequest $request)
    {
        try { 
            $StockRecord = StockRecord::create($request->validated());  
            return $StockRecord;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

}
