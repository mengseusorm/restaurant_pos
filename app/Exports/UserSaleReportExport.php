<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\UserSaleReportService;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class UserSaleReportExport implements FromCollection, WithHeadings
{
    public UserSaleReportService $userSaleReportService;
    public PaginateRequest $request;

    public function __construct(UserSaleReportService $userSaleReportService, $request)
    {
        $this->userSaleReportService = $userSaleReportService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $userSaleReportArray = [];
        $userSaleReportsArray = $this->userSaleReportService->list($this->request);

        // Initialize totals
        $totalOrders = 0;
        $totalAmount = 0;
        $totalTax = 0;
        $totalWithTax = 0;

        foreach($userSaleReportsArray as $index => $userSaleReport){
            $totalOrders += $userSaleReport->total_orders;
            $totalAmount += $userSaleReport->total;
            $totalTax += $userSaleReport->total_tax;
            $totalWithTax += ($userSaleReport->total + $userSaleReport->total_tax);

            $userSaleReportArray[] = [
                $index + 1,
                $userSaleReport->user_name,
                $userSaleReport->total_orders,
                AppLibrary::flatAmountFormat($userSaleReport->total),
                AppLibrary::flatAmountFormat($userSaleReport->total_tax),
                AppLibrary::flatAmountFormat($userSaleReport->total + $userSaleReport->total_tax),
            ];
        }

        // Add totals row
        $userSaleReportArray[] = [
            '',
            trans('all.label.total'),
            $totalOrders,
            AppLibrary::flatAmountFormat($totalAmount),
            AppLibrary::flatAmountFormat($totalTax),
            AppLibrary::flatAmountFormat($totalWithTax),
        ];

        return collect($userSaleReportArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.no'),
            trans('all.label.user_name'),
            trans('all.label.total_order'),
            trans('all.label.amount'),
            trans('all.label.vat'),
            trans('all.label.amount_vat'),
        ];
    }
}
