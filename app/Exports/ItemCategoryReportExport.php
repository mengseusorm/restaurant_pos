<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\ItemCategoryService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ItemCategoryReportExport implements FromCollection
{

    public ItemCategoryService $itemCategoryService;
    public PaginateRequest $request;

    public function __construct(ItemCategoryService $itemCategoryService, $request)
    {
        $this->itemCategoryService = $itemCategoryService;
        $this->request      = $request;
    }

    public function collection() : \Illuminate\Support\Collection
    {
        $itemCategoryReportArray  = [];

        // Get date range
        $branch = \App\Models\Branch::find(auth()->user()->branch_id);

        if ($this->request->get('from_date')) {
            $fromDate = \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($this->request->get('from_date')));
        } else {
            $fromDate = \Carbon\Carbon::now()->subDay()->startOfDay();
            if ($branch && $branch->open_time) {
                $time = explode(':', $branch->open_time);
                $fromDate->setTime((int)$time[0], (int)$time[1], 0);
            }
            $fromDate = \App\Libraries\AppLibrary::datetime($fromDate);
        }

        if ($this->request->get('to_date')) {
            $toDate = \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($this->request->get('to_date')));
        } else {
            $toDate = \Carbon\Carbon::now()->startOfDay();
            if ($branch && $branch->close_time) {
                $time = explode(':', $branch->close_time);
                $toDate->setTime((int)$time[0], (int)$time[1], 59);
            } else {
                $toDate->endOfDay();
            }
            $toDate = \App\Libraries\AppLibrary::datetime($toDate);
        }

        // Add title row
        $itemCategoryReportArray[] = [
            'Items Category Report',
            '', '', '', '', '', ''
        ];

        // Add date range row
        $itemCategoryReportArray[] = [
            'From: ' . $fromDate,
            'To: ' . $toDate,
            '', '', '', '', ''
        ];
        $itemCategoryReportArray[] = ['', '', '', '', '', '', '']; // Empty row

        // Add header row
        $itemCategoryReportArray[] = [
            trans('all.label.category_name'),
            trans('all.label.quantity'),
            trans('all.label.total_order'),
            trans('all.label.amount'),
            trans('all.label.vat'),
            trans('all.label.amount_vat'),
            trans('all.label.currency'),
        ];

        $itemCategoryReportsArray = $this->itemCategoryService->itemCategoryReport($this->request);

        // Initialize totals
        $total_items = 0;
        $total_orders = 0;
        $total_price = 0;
        $total_tax = 0;
        $total_price_with_tax = 0;

        foreach($itemCategoryReportsArray as $itemCategoryReport){
            $total_items += $itemCategoryReport->total_items;
            $total_orders += $itemCategoryReport->total_orders;
            $total_price += $itemCategoryReport->total_price;
            $total_tax += $itemCategoryReport->total_tax;
            $total_price_with_tax += ($itemCategoryReport->total_price + $itemCategoryReport->total_tax);

            $itemCategoryReportArray[] = [
                $itemCategoryReport->category_name,
                $itemCategoryReport->total_items,
                $itemCategoryReport->total_orders,
                AppLibrary::flatAmountFormat($itemCategoryReport->total_price),
                AppLibrary::flatAmountFormat($itemCategoryReport->total_tax),
                AppLibrary::flatAmountFormat($itemCategoryReport->total_price + $itemCategoryReport->total_tax),
                $itemCategoryReport->order_currency,
            ];
        }

        // Add totals row
        $itemCategoryReportArray[] = [
            trans('all.label.total'),
            $total_items,
            $total_orders,
            AppLibrary::flatAmountFormat($total_price),
            AppLibrary::flatAmountFormat($total_tax),
            AppLibrary::flatAmountFormat($total_price_with_tax),
            '',
        ];

        return collect($itemCategoryReportArray);
    }
}
