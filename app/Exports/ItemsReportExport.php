<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\ItemService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ItemsReportExport implements FromCollection
{

    public ItemService $itemService;
    public PaginateRequest $request;

    public function __construct(ItemService $itemService, $request)
    {
        $this->itemService = $itemService;
        $this->request     = $request;
    }
    public function collection(): \Illuminate\Support\Collection
    {
        $itemsReportArray = [];

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

        $itemsReportArray[] = [
            'Item Report',
            '', '', '', '', '', ''
        ];

        $itemsReportArray[] = [
            'From: ' . $fromDate,
            'To: ' . $toDate,
            '', '', '', '', ''
        ];
        $itemsReportArray[] = ['', '', '', '', '', '', ''];

        $itemsReportArray[] = [
            trans('all.label.name'),
            trans('all.label.item_category_id'),
            trans('all.label.quantity'),
            trans('all.label.amount'),
            trans('all.label.vat'),
            trans('all.label.amount_vat'),
            trans('all.label.currency'),
        ];

        $items = $this->itemService->itemReport($this->request);

        $totalQuantity = 0;
        $totalAmount = 0;
        $totalVat = 0;
        $totalAmountVat = 0;

        foreach ($items as $item) {
            $quantity = $item->total_ordered_qty ?? 0;
            $amount = $item->current_total_price ?? 0;
            $vat = $item->total_tax ?? 0;
            $amountVat = $amount + $vat;

            $totalQuantity += $quantity;
            $totalAmount += $amount;
            $totalVat += $vat;
            $totalAmountVat += $amountVat;

            $itemsReportArray[] = [
                $item->name,
                $item->category_name,
                $quantity,
                AppLibrary::flatAmountFormat($amount),
                AppLibrary::flatAmountFormat($vat),
                AppLibrary::flatAmountFormat($amountVat),
                $item->order_currency,
            ];
        }

        $itemsReportArray[] = [
            trans('all.label.total'),
            '',
            $totalQuantity,
            AppLibrary::flatAmountFormat($totalAmount),
            AppLibrary::flatAmountFormat($totalVat),
            AppLibrary::flatAmountFormat($totalAmountVat),
            '',
        ];

        return collect($itemsReportArray);
    }
}
