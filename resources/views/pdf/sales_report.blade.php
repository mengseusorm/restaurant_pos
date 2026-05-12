<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('all.label.sales_report') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #1F1F39;
            padding: 20px;
        }

        .container {
            width: 100%;
            margin: auto;
        }

        .report {
            width: 100%;
            text-align: center;
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
            margin-bottom: 40px;
        }

        th,
        td {
            border: 1px solid #EFF0F6;
            padding: 8px 8px;
            text-align: left;
            font-size: 8;
            font-weight: 400;
        }

        th {
            background-color: #F8FBFB;
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
        }

        .footer {
            width: 100%;
            text-align: center;
            font-size: 12px;
            font-weight: 400;
            margin-top: 40px;
        }
    </style>
</head>

<body>
    @php
        $total = 0;
        $totalDiscount = 0;
        $totalDeliveryCharge = 0;
    @endphp

    <div class="container">
        <div class="report">
            <p style="margin: 0 0 8px 0; font-size: 16px; font-weight: bold;">
                {{ App\Libraries\AppLibrary::textShortener($company['company_name'], 60) }}
            </p>
            <p>{{ App\Libraries\AppLibrary::textShortener($company['company_address'], 60) }}</p>
            <p style="color: #ff006b; margin: 0 0 8px 0; font-size: 16px; font-weight: bold;">
                {{ trans('all.label.sales_report') }}
            </p>
            <p style="margin: 0 0 16px 0; font-size: 12px;">
                From: {{ $fromDate }} To: {{ $toDate }}
            </p>
            <table>
                <thead>
                    <tr>
                        <th>{{ trans('all.label.order_serial_no') }}</th>
                        <th>{{ trans('all.label.date') }}</th>
                        <th>{{ trans('all.label.user') }}</th>
                        <th>{{ trans('all.label.amount') }}</th>
                        <th>{{ trans('all.label.vat') }}</th>
                        <th>{{ trans('all.label.amount_vat') }}</th>
                        <th>{{ trans('all.label.discount') }}</th>
                        {{-- <th>{{ trans('all.label.delivery_charge') }}</th> --}}
                        <th>{{ trans('all.label.payment_type') }}</th>
                        <th>{{ trans('all.label.payment_status') }}</th>
                        <th>{{ trans('all.label.total') }}</th> 
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        @php
                            $total += $order->total;
                            $totalDiscount += $order->discount;
                            $totalDeliveryCharge += $order->delivery_charge;
                        @endphp
                        <tr>
                            <td>{{ $order->order_serial_no }}</td>
                            <td class="date">{{ App\Libraries\AppLibrary::datetime($order->created_at) }}</td>
                            <td>{{ $order->user['name'] }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($order->total) }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($order->total_tax) }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($order->total + $order->total_tax) }}
                            </td>
                            <td>{{ App\Libraries\AppLibrary::reportCurrencyAmountFormat($order->discount) }}</td>
                            {{-- <td>{{ App\Libraries\AppLibrary::reportCurrencyAmountFormat($order->delivery_charge) }} --}}
                            </td>
                            <td>
                                @if ($order->transaction)
                                    {{ strtoupper($order->transaction->payment_method) }}
                                @elseif ($order->order_type === \App\Enums\OrderType::POS)
                                    {{ trans('pos_payment_method.' . $order->pos_payment_method) ?: '' }}
                                @else
                                    {{ trans('payment_gateway.' . $order->payment_method) }}
                                @endif
                            </td>
                            <td>{{ trans('payment_status.' . $order->payment_status) }}</td>
                            <td>{{ App\Libraries\AppLibrary::reportCurrencyAmountFormat($order->total) }}</td> 
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        </div>
        <div class="footer">
            {{ $copyright }}
        </div>
    </div>
</body>

</html>
