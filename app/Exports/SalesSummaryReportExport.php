<?php

namespace App\Exports;

use App\Libraries\AppLibrary; 
use App\Http\Requests\PaginateRequest;
use App\Services\SaleSummaryReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesSummaryReportExport implements FromCollection, WithHeadings
{
    protected SaleSummaryReportService $SaleSummaryReportService;
    protected PaginateRequest $request;

    public function __construct(SaleSummaryReportService $SaleSummaryReportService, PaginateRequest $request)
    {
        $this->SaleSummaryReportService = $SaleSummaryReportService;
        $this->request = $request;
    }

    public function collection(): Collection
    {
        $orders = $this->SaleSummaryReportService->saleSummaryReportList($this->request);
        $rows = collect();

        $sorted = $orders->sortBy('order_datetime');
        $dateFrom = optional($sorted->first())->order_datetime
            ? \Carbon\Carbon::parse($sorted->first()->order_datetime)->format('m/d/Y, h:i A')
            : '';
        $dateTo = optional($sorted->last())->order_datetime
            ? \Carbon\Carbon::parse($sorted->last()->order_datetime)->format('m/d/Y, h:i A')
            : '';

        $totalSales = $orders->sum('subtotal');
        $vat = $orders->sum('total_tax');
        $netSale = $totalSales - $vat;

        $uniqueCustomerCount = $orders->unique('user_id')->count();
        $orderCount = $orders->count();

        $grouped = $orders->groupBy(fn($order) => $order->paymentMethod->name ?? 'N/A');

        $rows->push(['Date From:', $dateFrom]);
        $rows->push(['Date To:', $dateTo]);
        $rows->push([]);
        $rows->push(['Sale Summary Report']);
        $rows->push([]);

        $rows->push(['Sales Summary']);
        $rows->push(['Total sales', AppLibrary::flatAmountFormat($totalSales)]);
        $rows->push(['VAT', AppLibrary::flatAmountFormat($vat)]);
        $rows->push(['Net sale', AppLibrary::flatAmountFormat($netSale)]);
        $rows->push([]);

        $rows->push(['Customer & Transaction Info']);
        $rows->push(['Number of Customer', $uniqueCustomerCount]);
        $rows->push(['Settlement number (number of orders)', $orderCount]);
        $rows->push([]);

        $rows->push(['Payment Methods']);
        foreach ($grouped as $paymentMethod => $groupOrders) {
            $rows->push([
                $paymentMethod,
                AppLibrary::flatAmountFormat($groupOrders->sum('total')),
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        // No fixed heading row; we're manually building all rows
        return [];
    }
}
