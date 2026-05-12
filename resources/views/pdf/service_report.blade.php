<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Profile Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; color: #1F1F39; margin: 0; padding: 0; }
        .container { width: 100%; margin: 0; padding: 0; }
        .report { width: 100%; text-align: center; margin-bottom: 50px; }
        p { margin: 0 0 16px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px auto; }
        th, td { border: 1px solid #EFF0F6; padding: 8px; text-align: left; font-size: 10px; font-weight: 400; }
        th { background-color: #F8FBFB; font-weight: 600; }
        tbody { color: #6E7191; }
        .total { color: #1F1F39; font-weight: 600; }
        .footer { width: 100%; text-align: center; font-size: 10px; font-weight: 400; margin-top: 30px; padding-top: 10px; border-top: 1px solid #EFF0F6; }
    </style>
</head>
<body>
    @php
        $total_orders    = 0;
        $total_customers = 0;
        $total_hours     = 0;
        $total_revenue   = 0;
    @endphp
    <div class="container">
        <div class="report">
            <p style="margin: 0px 0px 8px 0px; font-size: 16px; font-weight: bold;">{{ App\Libraries\AppLibrary::textShortener($company['company_name'], 60) }}</p>
            <p>{{ App\Libraries\AppLibrary::textShortener($company['company_address'], 60) }}</p>
            <p style="color: #ff006b; margin: 0px 0px 8px 0px; font-size: 16px; font-weight: bold;">Therapist Profile Report</p>
            <p style="margin: 0px 0px 16px 0px; font-size: 12px;">
                <strong>From:</strong> {{ $fromDate }}
                <strong style="margin-left: 20px;">To:</strong> {{ $toDate }}
            </p>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Therapist</th>
                        <th>Orders</th>
                        <th>Customers</th>
                        <th>Total Hours</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $index => $report)
                        @php
                            $total_orders    += $report->total_orders;
                            $total_customers += $report->total_customers;
                            $total_hours     += $report->total_hours;
                            $total_revenue   += $report->total_revenue;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $report->therapist_name ?: 'N/A' }}</td>
                            <td>{{ $report->total_orders }}</td>
                            <td>{{ $report->total_customers }}</td>
                            <td>{{ number_format((float)$report->total_hours, 2) }}</td>
                            <td>
                                {{ $branch
                                    ? App\Libraries\AppLibrary::branchCurrencyAmountFormat($report->total_revenue, $branch)
                                    : App\Libraries\AppLibrary::flatAmountFormat($report->total_revenue) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td colspan="2">{{ trans('all.label.total', [], 'en') }}</td>
                        <td>{{ $total_orders }}</td>
                        <td>{{ $total_customers }}</td>
                        <td>{{ number_format($total_hours, 2) }}</td>
                        <td>
                            {{ $branch
                                ? App\Libraries\AppLibrary::branchCurrencyAmountFormat($total_revenue, $branch)
                                : App\Libraries\AppLibrary::flatAmountFormat($total_revenue) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="footer">
            {{ $copyright }}
        </div>
    </div>
</body>
</html>
