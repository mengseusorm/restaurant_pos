<?php

namespace App\Exports;

use App\Enums\Source;
use App\Services\OrderSourceReportService;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderSourceExport implements FromCollection, WithHeadings
{

    public OrderSourceReportService $orderSourceReportService;
    public PaginateRequest $request;

    public function __construct(OrderSourceReportService $orderSourceReportService, $request)
    {
        $this->orderSourceReportService = $orderSourceReportService;
        $this->request      = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $orders  = [];
        $ordersArray = $this->orderSourceReportService->list($this->request);
        foreach ($ordersArray as $item) {
            $orders[] = [
                 match ($item->source) {
                    Source::WEB => trans('all.label.web'),
                    Source::APP => trans('all.label.app'),
                    Source::POS => trans('all.label.pos'),
                default => '',
                },
                $item->total,
                $item->total_tax,
                $item->total_tax + $item->total,
                $item->order_currency,
            ];
        }
        return collect($orders);
    }

    public function headings(): array
    {
        return [
            trans('all.label.order_source'),
            trans('all.label.amount'),
            trans('all.label.vat'),
            trans('all.label.amount_vat'),
            trans('all.label.currency'),
        ];
    }

}
