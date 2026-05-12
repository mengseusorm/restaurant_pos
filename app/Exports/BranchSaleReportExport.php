<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\OrderService;
use App\Http\Requests\PaginateRequest;
use App\Services\BranchSaleReportService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class BranchSaleReportExport implements FromCollection, WithHeadings
{

    public BranchSaleReportService $branchSaleReportService;
    public PaginateRequest $request;

    public function __construct(BranchSaleReportService $branchSaleReportService, $request)
    {
        $this->branchSaleReportService = $branchSaleReportService;
        $this->request      = $request;
    }

    public function collection() : \Illuminate\Support\Collection
    {
        $branchSaleReportArray  = [];
        $branchSaleReportsArray = $this->branchSaleReportService->list($this->request);
        foreach ($branchSaleReportsArray as $item) {
            $branchSaleReportArray[] = [
                $item->branch_name,
                $item->total_orders,
                AppLibrary::flatAmountFormat($item->total),
                AppLibrary::flatAmountFormat($item->total_tax),
                AppLibrary::flatAmountFormat($item->total + $item->total_tax),
                $item->order_currency,
            ];
        }
        return collect($branchSaleReportArray);
    }

    public function headings() : array
    {
        return [
            trans('all.label.branch'),
            trans('all.label.total_order'),
            trans('all.label.total_item'),
            trans('all.label.amount'),
            trans('all.label.vat'),
            trans('all.label.amount_vat'),
            trans('all.label.currency'),

        ];
    }
}
