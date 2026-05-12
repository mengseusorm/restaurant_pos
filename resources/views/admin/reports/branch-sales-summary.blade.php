<!DOCTYPE html>
<html>
<head>
    <title>Branch Sales Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .report-period {
            font-size: 14px;
            color: #888;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .kpi-card {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            background-color: #f9f9f9;
        }
        
        .kpi-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .currency {
            font-weight: bold;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->company_name ?? 'Restaurant POS' }}</div>
        <div class="report-title">Branch Sales Summary Report</div>
        <div class="report-period">
            Branch: {{ $reportData['branch']->name }}
            @if(isset($filters['from_date']) && $filters['from_date'])
                | From: {{ \Carbon\Carbon::parse($filters['from_date'])->format('M d, Y H:i') }}
            @endif
            @if(isset($filters['to_date']) && $filters['to_date'])
                | To: {{ \Carbon\Carbon::parse($filters['to_date'])->format('M d, Y H:i') }}
            @endif
        </div>
    </div>

    <!-- KPIs Section -->
    <div class="section">
        <div class="section-title">Overall Sales Summary</div>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total Sales Amount</div>
                <div class="kpi-value currency">
                    {{ number_format($reportData['kpis']['total_sales'], 2) }}{{ $reportData['kpis']['currency_symbol'] }}
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Orders</div>
                <div class="kpi-value">{{ number_format($reportData['kpis']['total_orders']) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Average Order Value</div>
                <div class="kpi-value currency">
                    {{ number_format($reportData['kpis']['average_order_value'], 2) }}{{ $reportData['kpis']['currency_symbol'] }}
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Items Sold</div>
                <div class="kpi-value">{{ number_format($reportData['kpis']['total_items_sold']) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Gross Profit</div>
                <div class="kpi-value currency">
                    {{ number_format($reportData['kpis']['gross_profit'], 2) }}{{ $reportData['kpis']['currency_symbol'] }}
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Section -->
    <div class="section">
        <div class="section-title">Top-Selling Products</div>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product Name</th>
                    <th class="text-right">Quantity Sold</th>
                    <th class="text-right">Total Sales</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['top_products'] as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $product['name'] }}</td>
                    <td class="text-right">{{ number_format($product['quantity_sold']) }}</td>
                    <td class="text-right currency">
                        {{ number_format($product['total_sales'], 2) }}{{ $reportData['kpis']['currency_symbol'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Payment Methods Section --> 
    <div class="section">
        <div class="section-title">Sales by Payment Method</div>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['payment_methods'] as $payment)
                <tr>
                    <td>{{ $payment['method'] }}</td>
                    <td class="text-right currency">
                        {{ number_format($payment['amount'], 2) }}{{ $reportData['kpis']['currency_symbol'] }}
                    </td>
                    <td class="text-right">{{ $payment['percentage'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Customer Segments Section -->
    <div class="section page-break">
        <div class="section-title">Sales by Customer Type</div>
        <table>
            <thead>
                <tr>
                    <th>Customer Type</th>
                    <th class="text-right">Number of Customers</th>
                    <th class="text-right">Total Sales</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['customer_segments'] as $segment)
                <tr>
                    <td>{{ $segment['type'] }}</td>
                    <td class="text-right">{{ number_format($segment['count']) }}</td>
                    <td class="text-right currency">
                        {{ number_format($segment['total_sales'], 2) }}{{ $reportData['kpis']['currency_symbol'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Refunds Summary Section -->
    <div class="section">
        <div class="section-title">Refunds / Returns Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="text-right">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Refunds</td>
                    <td class="text-right">{{ number_format($reportData['refunds']['count']) }}</td>
                </tr>
                <tr>
                    <td>Refund Amount</td>
                    <td class="text-right currency">
                        {{ number_format($reportData['refunds']['amount'], 2) }}{{ $reportData['kpis']['currency_symbol'] }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Net Sales After Refunds</strong></td>
                    <td class="text-right currency">
                        <strong>{{ number_format($reportData['refunds']['net_sales'], 2) }}{{ $reportData['kpis']['currency_symbol'] }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Branch Comparison Section -->
    @if(count($reportData['branch_comparison']['labels']) > 1)
    <div class="section">
        <div class="section-title">Branch Performance Comparison</div>
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th class="text-right">Total Sales</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['branch_comparison']['labels'] as $index => $branchLabel)
                <tr>
                    <td>{{ $branchLabel }}</td>
                    <td class="text-right currency">
                        {{ number_format($reportData['branch_comparison']['data'][$index], 2) }}{{ $reportData['kpis']['currency_symbol'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('M d, Y H:i:s') }}
    </div>
</body>
</html>