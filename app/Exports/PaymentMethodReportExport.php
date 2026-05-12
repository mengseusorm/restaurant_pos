<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Http\Requests\PaginateRequest;
use App\Services\PaymentMethodService; 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentMethodReportExport implements FromCollection
{
    public PaymentMethodService $paymentMethodService;
    public PaginateRequest $request;

    public function __construct(paymentMethodService $paymentMethodService, $request)
    {
        $this->paymentMethodService = $paymentMethodService;
        $this->request              = $request;
    }

    public function collection() : \Illuminate\Support\Collection
    {
        $paymentMethodArray  = [];
        
        // Get date range
        $branch = \App\Models\Branch::find(auth()->user()->branch_id);
        
        if ($this->request->get('from_date')) {
            $fromDate = \Carbon\Carbon::parse($this->request->get('from_date'))->format('m/d/Y, h:i A');
        } else {
            $fromDate = \Carbon\Carbon::now()->subDay()->startOfDay();
            if ($branch && $branch->open_time) {
                $time = explode(':', $branch->open_time);
                $fromDate->setTime((int)$time[0], (int)$time[1], 0);
            }
            $fromDate = $fromDate->format('m/d/Y, h:i A');
        }
        
        if ($this->request->get('to_date')) {
            $toDate = \Carbon\Carbon::parse($this->request->get('to_date'))->format('m/d/Y, h:i A');
        } else {
            $toDate = \Carbon\Carbon::now()->startOfDay();
            if ($branch && $branch->close_time) {
                $time = explode(':', $branch->close_time);
                $toDate->setTime((int)$time[0], (int)$time[1], 59);
            } else {
                $toDate->endOfDay();
            }
            $toDate = $toDate->format('m/d/Y, h:i A');
        }
        
        // Add title row
        $paymentMethodArray[] = [
            'Payment Method Report',
            '', '', '', '', ''
        ];
        
        // Add date range row
        $paymentMethodArray[] = [
            'From: ' . $fromDate,
            'To: ' . $toDate,
            '', '', '', ''
        ];
        $paymentMethodArray[] = ['', '', '', '', '', '']; // Empty row
        
        // Add header row
        $paymentMethodArray[] = [
            trans('all.label.payment_method'),
            trans('all.label.total_order'),
            trans('all.label.amount'),
            trans('all.label.vat'),
            trans('all.label.amount_vat'),
            trans('all.label.currency'),
        ];
        
        $paymentMethodsArray = $this->paymentMethodService->list($this->request);

        // Initialize totals
        $total_orders = 0;
        $total_price = 0;
        $total_tax = 0;
        $total_price_with_tax = 0;

        foreach ($paymentMethodsArray as $item) {
            $total_orders += $item->total_orders;
            $total_price += $item->total;
            $total_tax += $item->total_tax;
            $total_price_with_tax += $item->total_with_tax;

            $paymentMethodArray[] = [
                $item->payment_method_name,
                $item->total_orders,
                AppLibrary::flatAmountFormat($item->total),
                AppLibrary::flatAmountFormat($item->total_tax),
                AppLibrary::flatAmountFormat($item->total_with_tax),
                $item->order_currency,
            ];
        }

        // Add totals row
        $paymentMethodArray[] = [
            trans('all.label.total'),
            $total_orders,
            AppLibrary::flatAmountFormat($total_price),
            AppLibrary::flatAmountFormat($total_tax),
            AppLibrary::flatAmountFormat($total_price_with_tax),
            '',
        ];

        return collect($paymentMethodArray);
    }
}
