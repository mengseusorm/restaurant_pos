<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Branch Trend Report') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 5px;
        }
        .report-date {
            font-size: 12px;
            color: #888;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .branch-name {
            font-weight: bold;
        }
        .currency-amount {
            text-align: right;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .grand-total-row {
            background-color: #f3f4f6 !important;
            font-weight: 600 !important;
            color: #374151 !important;
            border-top: 1px solid #d1d5db !important;
            border-bottom: 1px solid #d1d5db !important;
        }
        .no-data {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 40px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company['company_name'] ?? 'Restaurant POS' }}</div>
        <div class="report-title">{{ __('Branch Trend Report') }}</div>
        <div class="report-date">{{ __('Generated on') }}: {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    @if(count($summary) > 0)
        <table class="summary-table">
            <thead>
                <tr>
                    <th rowspan="2">{{ __('Branch') }}</th>
                    @foreach($monthsArray as $monthIndex => $month)
                        <th colspan="{{ count($availableCurrencies) + 1 }}">{{ $month['label'] }}</th>
                    @endforeach
                    <th colspan="{{ count($availableCurrencies) + 1 }}">{{ __('Total') }}</th>
                    <th colspan="{{ count($availableCurrencies) + 1 }}">{{ __('Average') }}</th>
                </tr>
                <tr>
                    @foreach($monthsArray as $monthIndex => $month)
                        @foreach($availableCurrencies as $currency)
                            <th>{{ __('Amount') }} ({{ $currency }})</th>
                        @endforeach
                        <th>{{ __('Orders') }}</th>
                    @endforeach
                    @foreach($availableCurrencies as $currency)
                        <th>{{ __('Amount') }} ({{ $currency }})</th>
                    @endforeach
                    <th>{{ __('Orders') }}</th>
                    @foreach($availableCurrencies as $currency)
                        <th>{{ __('Amount') }} ({{ $currency }})</th>
                    @endforeach
                    <th>{{ __('Orders') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $branch)
                    <tr>
                        <td class="branch-name">{{ $branch['branch_name'] }}</td>
                        @foreach($monthsArray as $monthIndex => $month)
                            @foreach($availableCurrencies as $currency)
                                <td class="currency-amount">
                                    @if(isset($branch['monthly_data'][$monthIndex]['amounts'][$currency]) && $branch['monthly_data'][$monthIndex]['amounts'][$currency] > 0)
                                        {{ number_format($branch['monthly_data'][$monthIndex]['amounts'][$currency], 2) }}
                                    @else
                                        --
                                    @endif
                                </td>
                            @endforeach
                            <td class="currency-amount">
                                @if(isset($branch['monthly_data'][$monthIndex]['orders']) && $branch['monthly_data'][$monthIndex]['orders'] > 0)
                                    {{ number_format($branch['monthly_data'][$monthIndex]['orders']) }}
                                @else
                                    --
                                @endif
                            </td>
                        @endforeach
                        @foreach($availableCurrencies as $currency)
                            <td class="currency-amount">
                                @if(isset($branch['total_amounts'][$currency]) && $branch['total_amounts'][$currency] > 0)
                                    {{ number_format($branch['total_amounts'][$currency], 2) }}
                                @else
                                    --
                                @endif
                            </td>
                        @endforeach
                        <td class="currency-amount">{{ number_format($branch['total_orders']) }}</td>
                        @foreach($availableCurrencies as $currency)
                            <td class="currency-amount">
                                @if(isset($branch['average_amounts'][$currency]) && $branch['average_amounts'][$currency] > 0)
                                    {{ number_format($branch['average_amounts'][$currency], 2) }}
                                @else
                                    --
                                @endif
                            </td>
                        @endforeach
                        <td class="currency-amount">{{ number_format($branch['average_orders'], 1) }}</td>
                    </tr>
                @endforeach
                
                <!-- Grand Total Row -->
                @if(count($summary) > 0)
                    <tr class="grand-total-row">
                        <td class="branch-name">{{ __('GRAND TOTAL') }}</td>
                        @foreach($monthsArray as $monthIndex => $month)
                            @foreach($availableCurrencies as $currency)
                                @php
                                    $monthlyGrandTotal = 0;
                                    foreach($summary as $branch) {
                                        if(isset($branch['monthly_data'][$monthIndex]['amounts'][$currency])) {
                                            $monthlyGrandTotal += $branch['monthly_data'][$monthIndex]['amounts'][$currency];
                                        }
                                    }
                                @endphp
                                <td class="currency-amount">
                                    @if($monthlyGrandTotal > 0)
                                        {{ number_format($monthlyGrandTotal, 2) }}
                                    @else
                                        --
                                    @endif
                                </td>
                            @endforeach
                            @php
                                $monthlyGrandTotalOrders = 0;
                                foreach($summary as $branch) {
                                    if(isset($branch['monthly_data'][$monthIndex]['orders'])) {
                                        $monthlyGrandTotalOrders += $branch['monthly_data'][$monthIndex]['orders'];
                                    }
                                }
                            @endphp
                            <td class="currency-amount">{{ number_format($monthlyGrandTotalOrders) }}</td>
                        @endforeach
                        @foreach($availableCurrencies as $currency)
                            @php
                                $finalGrandTotal = 0;
                                foreach($summary as $branch) {
                                    if(isset($branch['total_amounts'][$currency])) {
                                        $finalGrandTotal += $branch['total_amounts'][$currency];
                                    }
                                }
                            @endphp
                            <td class="currency-amount">
                                @if($finalGrandTotal > 0)
                                    {{ number_format($finalGrandTotal, 2) }}
                                @else
                                    --
                                @endif
                            </td>
                        @endforeach
                        @php
                            $finalGrandTotalOrders = 0;
                            foreach($summary as $branch) {
                                $finalGrandTotalOrders += $branch['total_orders'];
                            }
                        @endphp
                        <td class="currency-amount">{{ number_format($finalGrandTotalOrders) }}</td>
                        @foreach($availableCurrencies as $currency)
                            @php
                                $avgGrandTotal = 0;
                                $branchCount = count($summary);
                                foreach($summary as $branch) {
                                    if(isset($branch['average_amounts'][$currency])) {
                                        $avgGrandTotal += $branch['average_amounts'][$currency];
                                    }
                                }
                                $avgGrandTotal = $branchCount > 0 ? ($avgGrandTotal / $branchCount) : 0;
                            @endphp
                            <td class="currency-amount">
                                @if($avgGrandTotal > 0)
                                    {{ number_format($avgGrandTotal, 2) }}
                                @else
                                    --
                                @endif
                            </td>
                        @endforeach
                        @php
                            $avgGrandTotalOrders = 0;
                            $branchCount = count($summary);
                            foreach($summary as $branch) {
                                $avgGrandTotalOrders += $branch['average_orders'];
                            }
                            $avgGrandTotalOrders = $branchCount > 0 ? ($avgGrandTotalOrders / $branchCount) : 0;
                        @endphp
                        <td class="currency-amount">{{ number_format($avgGrandTotalOrders, 1) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <div class="no-data">
            {{ __('No data available for the selected period') }}
        </div>
    @endif

    <div class="footer">
        {{ __('This report was generated automatically by the Restaurant POS System') }}
    </div>
</body>
</html>
</html>
