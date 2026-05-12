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
use App\Models\StockRecord;
use Carbon\Carbon;

class StockReportService
{
    protected $StockRecordFilter = [
        'item_id',
        'stock_id',
        'user_id',
        'order_id',
        'quantity',
        'created_at'
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
            if ($request->has('from_date') && $request->has('to_date')) {
                $startOfDay = Carbon::parse($request->input('from_date'))->startOfDay()->toDateTimeString();
                $endOfDay = Carbon::parse($request->input('to_date'))->endOfDay()->toDateTimeString();
            } else {
                $startOfDay = Carbon::now()->startOfDay()->toDateTimeString();
                $endOfDay = Carbon::now()->endOfDay()->toDateTimeString();
            }

            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';

            $stockRecordsQuery = DB::table('stock_records')
                ->join('items', 'stock_records.item_id', '=', 'items.id')
                ->join('branches', 'items.branch_id', '=', 'branches.id')
                ->join('item_stocks', 'item_stocks.id', '=', 'stock_records.stock_id')
                ->select(
                    'branches.id as branch_id',
                    'branches.name as branch_name',
                    'item_stocks.id as stock_id',
                    'item_stocks.name as stock_name',
                    'stock_records.item_id',
                    'items.name as item_name',
                    'items.barcode as item_barcode',
                    DB::raw("(SELECT SUM(quantity) FROM stock_records WHERE item_id = stock_records.item_id AND created_at < '$startOfDay') AS start_stock"),
                    DB::raw("SUM(quantity) AS current_remain_stock"),
                    DB::raw("SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) AS stock_in"),
                    DB::raw("SUM(CASE WHEN quantity < 0 THEN quantity ELSE 0 END) AS stock_out"),
                    DB::raw("MAX(stock_records.created_at) as created_at")
                )
                ->whereBetween('stock_records.created_at', [$startOfDay, $endOfDay])
                ->where(function ($query) use ($requests) {
                    // Add name filter using item relationship
                    if(isset($requests['name'])){
                        $query->where('items.name', 'like', '%' . $requests['name'] . '%');
                    }

                    foreach ($requests as $key => $value) {
                        if (in_array($key, $this->StockRecordFilter)) {
                            if ($key == 'except') {
                                $explodes = explode('|', $value);
                                foreach ($explodes as $exclude) {
                                    $query->where('items.id', '!=', $exclude);
                                }
                            } else {
                                $query->where($key, 'like', "%{$value}%");
                            }
                        }
                    }
                });

            // Add stock_id filter if present in request
            if ($request->has('stock_id')) {
                $stockRecordsQuery->where('stock_records.stock_id', $request->input('stock_id'));
            }

            $stockRecords = $stockRecordsQuery
                ->groupBy(
                    'branches.id', 'branches.name',
                    'item_stocks.id', 'item_stocks.name',
                    'stock_records.item_id', 'items.name', 'items.barcode'
                )
                ->$method($methodValue);

            foreach ($stockRecords as $record) {
                $record->remaining_stock = $record->start_stock + $record->stock_in + $record->stock_out;
            }
            return $stockRecords;

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
            $StockRecord = StockRecord::create($request->validated()); 
            return $StockRecord;
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
