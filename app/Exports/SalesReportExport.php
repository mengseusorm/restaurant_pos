<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\OrderService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\SaleReportResource; 
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class SalesReportExport implements FromCollection, WithHeadings
{
    protected OrderService $orderService;
    protected PaginateRequest $request;

    public function __construct(OrderService $orderService, PaginateRequest $request)
    {
        $this->orderService = $orderService;
        $this->request = $request;
    }

    public function collection(): Collection
    {
        try {
            $salesReportArray = [];
            
            $salesReports = $this->orderService->list($this->request);  
    
            $totalAmount = 0;
            $totalTax = 0;
            $totalAmountVat = 0;
     
    
            $recordCount = 0;
            foreach ($salesReports as $order) {  
                $recordCount++;
                $totalAmount += $order->total ?? 0;
                $totalTax += $order->total_tax ?? 0;
                $totalAmountVat += (($order->total ?? 0) + ($order->total_tax ?? 0));
                
                // Use SaleReportResource to transform the data
                $resource = new SaleReportResource($order);
                $resourceData = $resource->toArray(request());
                
                // Get payment method name from the resource
                $paymentMethodName = '';
                if (isset($resourceData['payment_method']) && $resourceData['payment_method']) {
                    $paymentMethodName = is_object($resourceData['payment_method']) 
                        ? ($resourceData['payment_method']->name ?? '') 
                        : (is_array($resourceData['payment_method']) ? ($resourceData['payment_method']['name'] ?? '') : '');
                }

                $salesReportArray[] = [
                    $resourceData['order_serial_no'] ?? '',
                    $resourceData['user'] ? $resourceData['user']->name : '', 
                    $resourceData['created_at'],
                    $resourceData['total_currency_price'],
                    $resourceData['total_tax_currency_price'],
                    $resourceData['total_amount_price'],
                    $resourceData['discount_amount_price'],
                    $resourceData['delivery_charge_amount_price'],
                    $paymentMethodName,
                    trans('payment_status.' . ($resourceData['payment_status'] ?? 'unknown')), 
                    $resourceData['total_amount_price'],
                    $order->currency ?? '',
                ]; 
            } 
        } catch (\Exception $e) {
            Log::error('SalesReportExport collection error: ' . $e->getMessage());
            return collect([]);
        }

        return collect($salesReportArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.order_serial_no'),
            trans('all.label.user'),
            trans('all.label.date'),
            trans('all.label.amount'),
            trans('all.label.vat'),
            trans('all.label.amount_vat'),
            trans('all.label.discount'),
            trans('all.label.delivery_charge'),
            trans('all.label.payment_type'),
            trans('all.label.payment_status'),
            trans('all.label.total'), 
            trans('all.label.currency'),
        ];
    }
}
