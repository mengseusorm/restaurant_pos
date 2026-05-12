<?php

namespace App\Exports;

use App\Enums\OrderType;
use App\Enums\Source;
use App\Libraries\AppLibrary;
use App\Http\Requests\PaginateRequest;
use App\Services\SaleSummaryReportService;
use Illuminate\Support\Collection; 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesSummaryReportStaffExport implements FromCollection, WithHeadings
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

        //orderType
        $groupedOrderTypes = [];
        foreach ($orders as $order) {
            $type = $order->order_type;
            if (!isset($groupedOrderTypes[$type])) {
                $groupedOrderTypes[$type] = [];
            }
            $groupedOrderTypes[$type][] = $type;
        }

        // source
        $groupedSource = [];
        foreach ($orders as $orderSource) {
            $source = $orderSource->source;
            if (!isset($groupedSource[$source])) {
                $groupedSource[$source] = [];
            }
            $groupedSource[$source][] = $source;
        }

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
        $rows->push([]);

        $rows->push(['Order Type']);
        foreach ($groupedOrderTypes as $methodName => $ordersType) {
            $orderTypeLabel = $this->getOrderTypeLabel($methodName);
            $rows->push([
                $orderTypeLabel,
                count($ordersType),
            ]);
        }
        $rows->push([]);

        $rows->push(['Source']);
        foreach ($groupedSource as $methodName => $source) {
            $sourceLabel = $this->getSourceLabel($methodName);
            $rows->push([
                $sourceLabel,
                count($source),
            ]);
        }

        return $rows;
    }

    private function getOrderTypeLabel($methodName): string
    {
        return match ($methodName) {
            OrderType::DELIVERY => trans('all.label.delivery'),
            OrderType::TAKEAWAY => trans('all.label.takeaway'),
            OrderType::POS => trans('all.label.pos'),
            OrderType::DINING_TABLE => trans('all.label.dining_table'),
            OrderType::TOKEN => trans('all.label.token'),
            OrderType::ONLINE_ORDER => trans('all.label.online_order'),
            default => $methodName,
        };
    }

    private function getSourceLabel($methodName): string
    {
        return match ($methodName) {
            Source::WEB => trans('all.label.web'),
            Source::APP => trans('all.label.app'),
            Source::POS => trans('all.label.pos'),
            default => $methodName,
        };
    }

    public function headings(): array
    {
        return [];
    }
}
