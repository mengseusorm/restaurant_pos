<?php

namespace App\Exports;

use App\Enums\IsAdvance;
use App\Enums\PaymentStatus;
use App\Enums\Source;
use App\Libraries\AppLibrary;
use App\Services\OrderService;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderExport implements FromCollection, WithHeadings
{

    public OrderService $orderService;
    public PaginateRequest $request;

    public function __construct(OrderService $orderService, $request)
    {
        $this->orderService = $orderService;
        $this->request      = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $orderArray  = [];
        $ordersArray = $this->orderService->list($this->request);

        foreach ($ordersArray as $order) { 
            $orderArray[] = [
                '#'.$order->waiting_number, 
                $order->order_serial_no,
                trans('orderType.' . $order->order_type),
                optional($order->user)->name,
                AppLibrary::flatAmountFormat($order->total),
                AppLibrary::datetime($order->order_datetime),
                trans('orderStatus.' . $order->status) . ($order->is_advance_order == IsAdvance::YES ? '/' . trans('all.label.advance') : ''),
                $order->source == Source::WEB ? 'WEB' : ($order->source == Source::POS ? 'POS' : ($order->source == Source::APP ? 'APP' : '')), 
                $order->payment_status == PaymentStatus::PAID ? trans('all.label.paid') : ($order->payment_status == PaymentStatus::UNPAID ? trans('all.label.unpaid') : ''), 
            ];
        }
        return collect($orderArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.waiting_number'),  
            trans('all.label.order_serial_no'),
            trans('all.label.order_type'),
            trans('all.label.customer'),
            trans('all.label.amount'),
            trans('all.label.date'),
            trans('all.label.status'),
            trans('all.label.source'),
            trans('all.label.payment_status'),
        ];
    }

}