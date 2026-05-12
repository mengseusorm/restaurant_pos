<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sale Summary Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #2d3748;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .container {
            width: 100%;
            margin: 0;
            padding: 20px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid #e2e8f0;
        }

        .report-header h1 {
            margin: 0 0 12px 0;
            font-size: 22px;
            font-weight: bold;
            color: #1a202c;
        }

        .report-header p {
            margin: 0 0 6px 0;
            font-size: 12px;
            color: #718096;
        }

        .report-title {
            color: #3182ce;
            margin: 16px 0 12px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .date-range {
            margin: 0 0 20px 0;
            font-size: 13px;
            color: #4a5568;
            font-weight: 600;
        }

        .section {
            margin-bottom: 30px;
            background: #f7fafc;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            padding: 15px 20px;
            background: #edf2f7;
            color: #2d3748;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-content {
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        tr {
            border-bottom: 1px solid #e2e8f0;
        }

        tr:last-child {
            border-bottom: none;
        }

        th,
        td {
            padding: 12px 20px;
            font-size: 11px;
        }

        th {
            background-color: #f1f5f9;
            font-weight: 600;
            text-align: left;
            color: #475569;
        }

        td {
            color: #64748b;
            background: #ffffff;
        }

        td:first-child {
            font-weight: 500;
            color: #374151;
        }

        td:last-child {
            text-align: right;
            font-weight: 600;
            color: #1f2937;
        }

        .no-data {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
        }

        .footer {
            width: 100%;
            text-align: center;
            font-size: 10px;
            font-weight: 400;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-item {
            display: table-cell;
            width: 50%;
            padding: 15px;
            text-align: center;
            border: 0.5px solid #e2e8f0;
            background: #ffffff;
        }

        .summary-label {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
        }
    </style>
</head>

<body>
    @php
        // Get the first (and only) item from the collection
        $data = !empty($items) && isset($items[0]) ? $items[0] : null;
        $total_invoice = $data ? intval($data->total_invoices) : 0;
        $void_invoice = $data ? intval($data->void_invoice) : 0; 
        $total_void_item_order = $data ? intval($data->deleted_order_items) : 0;
        $total_revenue = $data ? floatval($data->total_revenue) : 0;
        $total_discount = $data ? floatval($data->total_discount) : 0;
        $sale_items = $data && isset($data->sale_items) ? $data->sale_items : 
                     ($data && isset($data->sale_items_by_printer) ? $data->sale_items_by_printer : []);
        $payment_methods = $data && $data->payment_methods ? $data->payment_methods : [];
    @endphp

    <div class="container">
        <div class="report-header" style="border-bottom: 0.5px solid #e2e8f0;">
            <h1>{{ App\Libraries\AppLibrary::textShortener($company['company_name'], 60) }}</h1>
            <p>{{ App\Libraries\AppLibrary::textShortener($company['company_address'], 60) }}</p>
            <p class="report-title">{{ trans('all.label.daily_sale_summary_report', [], 'en') }}</p>
            <p class="date-range">
            <strong>{{ trans('all.label.date', [], 'en') }}:</strong> {{ $fromDate }} - {{ $toDate }}
            </p>
        </div>

        <!-- Unified Report Table -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; border: 0.5px solid #000;">
            <tbody>
                <!-- Header Info -->
                <tr>
                    <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ trans('all.label.cashier', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ $data->users ?? Auth::user()->name ?? '' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ trans('all.label.start_date', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ $fromDate }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ trans('all.label.end_date', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ $toDate }}</td>
                </tr>

                <!-- Invoice Summary -->
                <tr style="background-color: #f1f5f9;">
                    <td style="padding: 8px; text-left; font-weight: bold; color: #374151; border: 0.5px solid #000;">{{ trans('all.label.invoice_summary', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; color: #374151; border: 0.5px solid #000;" colspan="3"></td>
                </tr>
                <tr>
                    <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ trans('all.label.total_invoice', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; font-weight: 600; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ $total_invoice }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ trans('all.label.total_void_invoice', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; font-weight: 600; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ $void_invoice }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ trans('all.label.total_void_item_order', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; font-weight: 600; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ $total_void_item_order }}</td>
                </tr>
                <!-- Sale Items Header -->
                <tr style="background-color: #f1f5f9;">
                    <td style="padding: 8px; text-left; font-weight: bold; color: #374151; border: 0.5px solid #000;">{{ trans('all.label.total_sale_items', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; font-weight: bold; color: #374151; border: 0.5px solid #000;">{{ trans('all.label.name', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; font-weight: bold; color: #374151; border: 0.5px solid #000;">{{ trans('all.label.total_item', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; font-weight: bold; color: #374151; border: 0.5px solid #000;">{{ trans('all.label.amount', [], 'en') }}</td>
                </tr>
                @if(!empty($sale_items))
                    @foreach ($sale_items as $item)
                        <tr>
                            <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ $item['item_name'] ?? '' }}</td>
                            <td style="padding: 8px; text-align: right; color: #1f2937; border: 0.5px solid #000; font-weight: 600;">{{ $item['printer_name'] ?? '' }}</td>
                            <td style="padding: 8px; text-align: right; color: #1f2937; border: 0.5px solid #000; font-weight: 600;">{{ $item['total_quantity'] ?? 0 }}</td>
                            <td style="padding: 8px; text-align: right; color: #1f2937; border: 0.5px solid #000; font-weight: 600;">{{ number_format($item['total_price'], 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="padding: 8px; text-align: center; color: #9ca3af; font-style: italic; border: 0.5px solid #000;">{{ trans('all.label.no_data_found', [], 'en') }}</td>
                    </tr>
                @endif

                <!-- Financial Summary -->
                <tr style="background-color: #f1f5f9;">
                    <td style="padding: 8px; text-left; font-weight: bold; color: #374151; border: 0.5px solid #000;">{{ trans('all.label.financial_summary', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; color: #374151; border: 0.5px solid #000;" colspan="3"></td>
                </tr>
                <tr>
                    <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ trans('all.label.total_revenue', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; font-weight: 600; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ App\Libraries\AppLibrary::flatAmountFormat($total_revenue) }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000;">{{ trans('all.label.total_discount', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; font-weight: 600; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ App\Libraries\AppLibrary::flatAmountFormat($total_discount) }}</td>
                </tr>
                <tr style="background-color: #f1f5f9;">
                    <td style="padding: 8px; text-left; font-weight: bold; color: #374151; border: 0.5px solid #000;">Total</td>
                    <td style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 0.5px solid #000;" colspan="3">{{ App\Libraries\AppLibrary::flatAmountFormat($total_revenue - $total_discount) }}</td>
                </tr>

                <!-- Payment Methods -->
                <tr style="background-color: #f1f5f9;">
                    <td style="padding: 8px; text-left; font-weight: bold; color: #374151; border: 0.5px solid #000;">{{ trans('all.label.net_sale', [], 'en') }} - {{ trans('all.label.payment_method', [], 'en') }}</td>
                    <td style="padding: 8px; text-align: right; color: #374151; border: 0.5px solid #000;" colspan="3"></td>
                </tr>
                @if(!empty($payment_methods))
                    @foreach ($payment_methods as $item)
                        <tr @if($item['method_name'] === 'Total') style="background-color: #f1f5f9;" @endif>
                            <td style="padding: 8px; text-left; color: #64748b; border: 0.5px solid #000; @if($item['method_name'] === 'Total') font-weight: bold; @endif">{{ $item['method_name'] }}</td>
                            <td style="padding: 8px; text-align: right; color: #1f2937; border: 0.5px solid #000; @if($item['method_name'] === 'Total') font-weight: bold; @else font-weight: 600; @endif" colspan="3">{{ App\Libraries\AppLibrary::flatAmountFormat($item['amount']) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="padding: 8px; text-align: center; color: #9ca3af; font-style: italic; border: 0.5px solid #000;">{{ trans('all.label.no_data_found', [], 'en') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>

</html>
