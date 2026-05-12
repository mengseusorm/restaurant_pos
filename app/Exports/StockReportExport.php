<?php

namespace App\Exports;

use App\Http\Requests\PaginateRequest;
use App\Services\StockReportService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockReportExport implements FromCollection,WithHeadings
{
   
    public StockReportService $stockReportService;
    public PaginateRequest $request;

    public function __construct(StockReportService $stockReportService, $request)
    {
        $this->stockReportService = $stockReportService;
        $this->request     = $request;
    }

    public function collection() : \Illuminate\Support\Collection
    {
        $stockReportArray  = [];
        $stockReportsArray = $this->stockReportService->list($this->request);

        foreach ($stockReportsArray as $item) { 
            $stockReportArray[] = [
                $item->item_name,
                $item->stock_in,
                $item->stock_out,
                $item->remaining_stock,
            ];
        }
        return collect($stockReportArray);
    }

    public function headings() : array
    {
        return [
            trans('all.label.item_name'),
            trans('all.label.stock_in'),
            trans('all.label.stock_out'),
            trans('all.label.remaining_stock'),
        ];
    }
}
