<?php


namespace App\Services;

use App\Enums\PaymentStatus;
use App\Http\Requests\PaginateRequest;
use App\Libraries\AppLibrary;
use App\Models\Order;
use App\Models\OrderItemDeleted;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailySaleSummaryReportService
{
    protected array $exceptFilter = [
        'excepts'
    ];


    public function list(PaginateRequest $request)
    {
        try {
            $requests = $request->all();

            if (isset($requests['from_date']) && isset($requests['to_date'])) {
                $startDate = AppLibrary::filterDateTime($requests['from_date'])->toDateTimeString();
                $endDate = AppLibrary::filterDateTime($requests['to_date'])->toDateTimeString();
            } else {

                $branch = \App\Models\Branch::find(auth()->user()->branch_id ?? 1);

                $startDate = \Carbon\Carbon::yesterday()->startOfDay();
                if ($branch && $branch->open_time) {
                    $time = explode(':', $branch->open_time);
                    $startDate->setTime((int)$time[0], (int)$time[1], 0);
                }

                $endDate = \Carbon\Carbon::today()->startOfDay();
                if ($branch && $branch->close_time) {
                    $time = explode(':', $branch->close_time);
                    $endDate->setTime((int)$time[0], (int)$time[1], 59);
                } else {
                    $endDate->endOfDay();
                }

                $startDate = $startDate->format('Y-m-d H:i:s');
                $endDate = $endDate->format('Y-m-d H:i:s');
            }


            $dateCondition = function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            };


            $totalInvoices = Order::where('payment_status', PaymentStatus::PAID)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();


            $saleItemsByPrinter = DB::table('order_items as oi')
                ->join('items as i', 'oi.item_id', '=', 'i.id')
                ->join('orders as o', 'oi.order_id', '=', 'o.id')
                ->leftJoin('kitchen_printers as kp', 'i.kitchen_printer_id', '=', 'kp.id')
                ->where('o.payment_status', PaymentStatus::PAID)
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('o.created_at', [$startDate, $endDate]);
                })
            ->groupBy('kp.id', 'kp.name')
            ->select([
                DB::raw('COALESCE(kp.name, "No Printer") as printer_name'),
                DB::raw('COUNT(DISTINCT i.id) as total_items'),
                DB::raw('SUM(oi.quantity) as total_quantity'),
                DB::raw('SUM(oi.total_price) as gross_total'),
                DB::raw('SUM(oi.discount) as item_discount'),
                DB::raw('SUM(oi.tax_amount) as tax_amount'),
                DB::raw('SUM(oi.total_price + oi.tax_amount) as total_price'),
                DB::raw('MAX(o.created_at) as created_at')
            ])
            ->get();

            $saleItemsByPrinterFormatted = [];
            $totalSaleItemsQuantity = 0;
            $totalSaleItemsPrice = 0;

            foreach ($saleItemsByPrinter as $printer) {
                $printerName = $printer->printer_name;
                $itemCount = $printer->total_items;
                $quantity = $printer->total_quantity;
                $price = $printer->total_price;

                $saleItemsByPrinterFormatted[] = [
                    'printer_name' => $printerName,
                    'total_items' => $itemCount,
                    'total_quantity' => $quantity,
                    'total_price' => $price,
                    'created_at' => $printer->created_at
                ];

                $totalSaleItemsQuantity += $quantity;
                $totalSaleItemsPrice += $price;
            }


            // $saleItemsByPrinterFormatted[] = [
            //     'printer_name' => 'Total',
            //     'total_items' => count($saleItemsByPrinter),
            //     'total_quantity' => $totalSaleItemsQuantity,
            //     'total_price' => $totalSaleItemsPrice
            // ];


            // Calculate total revenue from both Order totals and OrderItem totals
            $orderRevenue = Order::where('payment_status', PaymentStatus::PAID)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })->sum(DB::raw('subtotal + total_tax'));

            $orderItemRevenue = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('o.payment_status', PaymentStatus::PAID)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('o.created_at', [$startDate, $endDate]);
            })
            ->sum(DB::raw('oi.total_price - oi.discount + oi.tax_amount'));

            $totalRevenue = max($orderRevenue, $orderItemRevenue); // Use the higher value for accuracy


            // Calculate total discount from both Order and OrderItem levels
            $orderDiscount = Order::where('payment_status', PaymentStatus::PAID)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })->sum('discount');

            $orderItemDiscount = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('o.payment_status', PaymentStatus::PAID)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('o.created_at', [$startDate, $endDate]);
            })
            ->sum('oi.discount');

            $totalDiscount = $orderDiscount + $orderItemDiscount;  

            $paymentMethods = DB::table('payment_methods')->get();
            $paymentMethodTotals = [];
            $totalPaymentMethods = 0;

            foreach ($paymentMethods as $method) {
                $amount = Order::where('pos_payment_method', $method->id)
                    ->where('payment_status', PaymentStatus::PAID)
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->sum('total');
                $paymentMethodTotals[] = [
                    'method_name' => $method->name,
                    'amount' => $amount
                ];
                $totalPaymentMethods += $amount;
            }


            if (!empty($paymentMethodTotals)) {
                $paymentMethodTotals[] = [
                    'method_name' => 'Total',
                    'amount' => $totalPaymentMethods
                ];
            }


            $voidInvoiceCount = DB::table('order_deleteds')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();


            $deletedOrdersCount = DB::table('order_deleteds')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();


            $deletedOrderItemsCount = OrderItemDeleted::when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })->sum('quantity');

 

            $result = (object)[
                'total_invoices'        => $totalInvoices,
                'sale_items_by_printer' => $saleItemsByPrinterFormatted,
                'total_revenue'         => $totalRevenue,
                'total_discount'        => $totalDiscount,
                'void_invoice'          => $voidInvoiceCount,
                'deleted_orders'        => $deletedOrdersCount,
                'deleted_order_items'   => $deletedOrderItemsCount,
                'payment_methods'       => $paymentMethodTotals,
                'user'                  => Auth::user()->name,
            ];

            return [$result];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

}
