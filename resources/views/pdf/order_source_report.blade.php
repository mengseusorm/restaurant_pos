<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Source Report</title>
   <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #1F1F39;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .report {
            width: 100%;
            text-align: center;
            margin-bottom: 50px;
        }

        img {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
        }

        p {
            margin: 0 0 16px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
        }

        th,
        td {
            border: 1px solid #EFF0F6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 400;
        }

        th {
            background-color: #F8FBFB;
            font-weight: 600;
        }

        th:first-child {
            white-space: nowrap;
        }

        tbody {
            color: #6E7191;
        }

        .date,
        .time {
            white-space: nowrap;
        }

        .total {
            color: #1F1F39;
            font-weight: 600;
        }

        .footer {
            width: 100%;
            text-align: center;
            font-size: 10px;
            font-weight: 400;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #EFF0F6;
        }
    </style>
</head>
<body>
    @php
        $total_orders = 0;
        $total_price = 0;
        $total_tax = 0;
        $total_price_with_tax = 0;
    @endphp
    <div class="container">
        <div class="report">
            <p style="margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold">{{ App\Libraries\AppLibrary::textShortener($company['company_name'], 60) }}</p>
            <p>{{ App\Libraries\AppLibrary::textShortener($company['company_address'],60) }}</p>
            <p  style="color: #ff006b;margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold;">{{ trans('all.label.order_source_report', [], 'en') }}</p>
            <p style="margin: 0px 0px 16px 0px;font-size: 12px;">
                <strong>From:</strong> {{ $fromDate }}
                <strong style="margin-left: 20px;">To:</strong> {{ $toDate }}
            </p>
            <table>
                    <thead>
                        <tr>
                            <th>{{ trans('all.label.source') }}</th>
                            <th>{{ trans('all.label.order_source') }}</th>
                            <th>{{ trans('all.label.amount') }}</th>
                            <th>{{ trans('all.label.vat') }}</th>
                            <th>{{ trans('all.label.amount_vat') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $total_orders += $item->total_orders;
                                $total_price += $item->total;
                                $total_tax += $item->total_tax;
                                $total_price_with_tax += ($item->total_tax + $item->total);
                            @endphp
                            <tr>
                                <td>
                                    @if ($item->source == App\Enums\Source::POS)
                                        {{ trans('all.label.pos') }}
                                    @elseif ($item->source == App\Enums\Source::APP)
                                        {{ trans('all.label.app') }}
                                    @elseif ($item->source == App\Enums\Source::WEB)
                                        {{ trans('all.label.web') }}
                                    @else
                                        {{ '-' }}
                                    @endif
                                </td>
                                <td>{{ $item->total_orders }}</td>
                                <td>{{ App\Libraries\AppLibrary::flatAmountFormat($item->total) }}</td>
                                <td>{{ App\Libraries\AppLibrary::flatAmountFormat($item->total_tax) }}</td>
                                <td>{{ App\Libraries\AppLibrary::flatAmountFormat($item->total_tax + $item->total) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total">
                            <td>{{ trans('all.label.total', [], 'en') }}</td>
                            <td>{{ $total_orders }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($total_price) }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($total_tax) }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($total_price_with_tax) }}</td>
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
