<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class BranchSalesSummaryExport implements WithMultipleSheets
{
    protected array $reportData;

    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    public function sheets(): array
    {
        $sheets = [];

        // KPIs Sheet
        $sheets[] = new BranchSalesSummaryKPISheet($this->reportData['kpis']);

        // Top Products Sheet
        $sheets[] = new BranchSalesSummaryTopProductsSheet($this->reportData['top_products']);

        // Payment Methods Sheet
        $sheets[] = new BranchSalesSummaryPaymentMethodsSheet($this->reportData['payment_methods']);

        // Customer Segments Sheet
        $sheets[] = new BranchSalesSummaryCustomerSegmentsSheet($this->reportData['customer_segments']);

        // Sales Trend Sheet
        $sheets[] = new BranchSalesSummaryTrendSheet($this->reportData['sales_trend'], 'Sales Trend');

        return $sheets;
    }
}

class BranchSalesSummaryKPISheet implements FromCollection, WithHeadings, WithTitle
{
    protected array $kpis;

    public function __construct(array $kpis)
    {
        $this->kpis = $kpis;
    }

    public function collection()
    {
        return collect([
            [
                'Total Sales Amount',
                number_format($this->kpis['total_sales'], 2) . ' ' . ($this->kpis['currency_symbol'] ?? '$')
            ],
            [
                'Total Number of Orders',
                $this->kpis['total_orders']
            ],
            [
                'Average Order Value',
                number_format($this->kpis['average_order_value'], 2) . ' ' . ($this->kpis['currency_symbol'] ?? '$')
            ],
            [
                'Total Items Sold',
                $this->kpis['total_items_sold']
            ],
            [
                'Gross Profit',
                number_format($this->kpis['gross_profit'], 2) . ' ' . ($this->kpis['currency_symbol'] ?? '$')
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'KPI',
            'Value'
        ];
    }

    public function title(): string
    {
        return 'KPIs';
    }
}

class BranchSalesSummaryTopProductsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected array $products;

    public function __construct(array $products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return collect($this->products);
    }

    public function map($product): array
    {
        return [
            $product['name'],
            $product['quantity_sold'],
            number_format($product['total_sales'], 2)
        ];
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Quantity Sold',
            'Total Sales'
        ];
    }

    public function title(): string
    {
        return 'Top Products';
    }
}

class BranchSalesSummaryPaymentMethodsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected array $paymentMethods;

    public function __construct(array $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
    }

    public function collection()
    {
        return collect($this->paymentMethods);
    }

    public function map($paymentMethod): array
    {
        return [
            $paymentMethod['method'],
            number_format($paymentMethod['amount'], 2),
            $paymentMethod['percentage'] . '%'
        ];
    }

    public function headings(): array
    {
        return [
            'Payment Method',
            'Amount',
            'Percentage'
        ];
    }

    public function title(): string
    {
        return 'Payment Methods';
    }
}

class BranchSalesSummaryCustomerSegmentsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected array $customerSegments;

    public function __construct(array $customerSegments)
    {
        $this->customerSegments = $customerSegments;
    }

    public function collection()
    {
        return collect($this->customerSegments);
    }

    public function map($segment): array
    {
        return [
            $segment['type'],
            $segment['count'],
            number_format($segment['total_sales'], 2)
        ];
    }

    public function headings(): array
    {
        return [
            'Customer Type',
            'Number of Customers',
            'Total Sales'
        ];
    }

    public function title(): string
    {
        return 'Customer Segments';
    }
}

class BranchSalesSummaryTrendSheet implements FromCollection, WithHeadings, WithTitle
{
    protected array $trendData;
    protected string $sheetTitle;

    public function __construct(array $trendData, string $sheetTitle)
    {
        $this->trendData = $trendData;
        $this->sheetTitle = $sheetTitle;
    }

    public function collection()
    {
        $data = [];
        $labels = $this->trendData['labels'] ?? [];
        $values = $this->trendData['data'] ?? [];

        for ($i = 0; $i < count($labels); $i++) {
            $data[] = [
                $labels[$i] ?? '',
                $values[$i] ?? 0
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Period',
            'Value'
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}