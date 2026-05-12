<?php

namespace App\Exports;
 
use App\Enums\OrderType; 
use App\Services\OrderTypeReportService;
use App\Http\Requests\PaginateRequest; 
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderTypeExport implements FromCollection, WithHeadings
{

    public OrderTypeReportService $orderTypeReportService;
    public PaginateRequest $request;

    public function __construct(OrderTypeReportService $orderTypeReportService, $request)
    {
        $this->orderTypeReportService = $orderTypeReportService;
        $this->request      = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {  
        $order  = [];
        $ordersArray = $this->orderTypeReportService->list($this->request);  

        foreach ($ordersArray as $item) {
            $order[] = [ 
                match ($item->order_type) {
                    OrderType::DELIVERY => trans('all.label.delivery'),
                    OrderType::TAKEAWAY => trans('all.label.app'),
                    OrderType::POS => trans('all.label.web'),
                    OrderType::DINING_TABLE => trans('all.label.web'),
                    OrderType::TOKEN => trans('all.label.web'),
                    OrderType::ONLINE_ORDER => trans('all.label.web'),
                default => '',
                },
                $item->total_order_type,
                $item->total_price,   
                $item->total_tax,  
                $item->total_tax + $item->total_price,  
            ];
        }    
        return collect($order);
    }

    public function headings(): array
    {
        return [  
            trans('all.label.order_type'),
            trans('all.label.total_order_type'),
            trans('all.label.amount'), 
            trans('all.label.vat'),
            trans('all.label.amount_vat'), 
        ];
    }

}