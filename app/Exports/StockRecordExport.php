<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Http\Requests\PaginateRequest;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Order;
use App\Models\User;
use App\Services\StockRecordService;
use Illuminate\Support\Facades\Log;

class StockRecordExport implements FromCollection,WithHeadings
{
     /**
    * @return \Illuminate\Support\Collection
    */
    public StockRecordService $StockRecordService;
    public PaginateRequest $request;

    public function __construct(StockRecordService $StockRecordService, $request)
    {
        $this->StockRecordService = $StockRecordService;
        $this->request            = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $itemStockArray = [];
        $stockRecords     = $this->StockRecordService->list($this->request);
        
        foreach ($stockRecords as $stockRecord) { 
            $itemStockArray[] = [
                optional(Item::find($stockRecord->item_id))->name, 
                optional(ItemStock::find($stockRecord->stock_id))->name, 
                optional(User::find($stockRecord->user_id))->name, 
                optional(Order::find($stockRecord->order_id))->name, 
                $stockRecord->quantity,
            ];
        }
        return collect($itemStockArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.item'),
            trans('all.label.stock'),
            trans('all.label.user'),
            trans('all.label.order'),
            trans('all.label.quantity'),
        ];
    }
}
