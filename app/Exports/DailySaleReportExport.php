<?php

namespace App\Exports;

use App\Http\Requests\PaginateRequest; 
use App\Libraries\AppLibrary;
use App\Services\DailySaleReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailySaleReportExport implements FromCollection, WithHeadings
{
    public DailySaleReportService $dailySaleReportService;
    public PaginateRequest $request;

    public function __construct(DailySaleReportService $dailySaleReportService, $request)
    {
        $this->dailySaleReportService = $dailySaleReportService;
        $this->request      = $request;
    }

    public function collection() : \Illuminate\Support\Collection
    {
        $dailySaleReportArray = [];
        $dailySaleReportsArray = $this->dailySaleReportService->list($this->request);

        // Initialize totals
        $total_orders = 0;
        $total_price = 0;
        $total_tax = 0;
        $total_price_with_tax = 0;

        foreach ($dailySaleReportsArray as $dailySaleReport) {
            $total_orders += $dailySaleReport->total_orders;
            $total_price += $dailySaleReport->total;
            $total_tax += $dailySaleReport->total_tax;
            $total_price_with_tax += ($dailySaleReport->total + $dailySaleReport->total_tax);

            $dailySaleReportArray[] = [
                $dailySaleReport->order_date,
                $dailySaleReport->total_orders,
                AppLibrary::flatAmountFormat($dailySaleReport->total),
                AppLibrary::flatAmountFormat($dailySaleReport->total_tax),
                AppLibrary::flatAmountFormat($dailySaleReport->total + $dailySaleReport->total_tax),
            ];
        }

        // Add totals row
        $dailySaleReportArray[] = [
            trans('all.label.total'),
            $total_orders,
            AppLibrary::flatAmountFormat($total_price),
            AppLibrary::flatAmountFormat($total_tax),
            AppLibrary::flatAmountFormat($total_price_with_tax),
        ];

        return collect($dailySaleReportArray);
    }
    public function headings(): array
    {
        return [
            trans('all.label.order_date'),
            trans('all.label.total_order'),
            trans('all.label.amount'),
            trans('all.label.vat'),
            trans('all.label.amount_vat'),
        ];
    }
}
