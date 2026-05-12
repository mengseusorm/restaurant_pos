<?php

namespace App\Exports;

use App\Http\Requests\PaginateRequest;
use App\Services\ItemStockService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemStockExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public ItemStockService $itemStockService;
    public PaginateRequest $request;

    public function __construct(ItemStockService $itemStockService, $request)
    {
        $this->itemStockService = $itemStockService;
        $this->request            = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $itemStockArray     = [];
        $itemStocksArray    = $this->itemStockService->list($this->request); 

        foreach ($itemStocksArray as $itemStock) {
            $itemStockArray[] = [
                $itemStock->name,
                $itemStock->price,
                $itemStock->branch['name'],
            ];
        }
        return collect($itemStockArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.name'),
            trans('all.label.price'),
            trans('all.label.branch'),
        ];
    }
}
