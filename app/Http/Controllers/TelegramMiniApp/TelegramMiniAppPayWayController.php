<?php

namespace App\Http\Controllers\TelegramMiniApp;

use App\Http\Controllers\Controller;
use App\Enums\PaymentStatus;
use App\Models\FrontendOrder;
use App\Services\OrderService;
use App\Services\PayWayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class TelegramMiniAppPayWayController extends Controller
{
    protected $payWayService;
    protected $orderService;

    public function __construct(PayWayService $payWayService, OrderService $orderService)
    {
        $this->payWayService = $payWayService;
        $this->orderService  = $orderService;
    }

    /**
     * Generate QR code for payment (no auth required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:USD,KHR',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'branch_id' => 'required|exists:branches,id',
            'order_items' => 'nullable|array',
            'telegram_user_id' => 'nullable|string',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => [
                    'code' => '1',
                    'message' => $validator->errors()->first()
                ]
            ], 422);
        }

        try {
            // Check if PayWay is configured
            if (!$this->payWayService->isConfigured()) {
                return response()->json([
                    'status' => [
                        'code' => '99',
                        'message' => 'PayWay is not configured properly. Please contact administrator.'
                    ]
                ], 500);
            }

            // Build return_deeplink so ABA redirects back into the Telegram Mini App
            // after payment, pre-opening the order details screen.
            $returnDeeplink = '';
            if ($request->order_id) {
                $miniAppUrl = rtrim(env('TELEGRAM_MINI_APP_URL', ''), '/');
                if ($miniAppUrl) {
                    $returnDeeplink = $miniAppUrl . '?startapp=order_' . $request->order_id;
                }
            }

            // Prepare data for PayWay
            $data = [
                'amount' => number_format((float)$request->amount, 2, '.', ''),
                'currency' => strtoupper($request->currency),
                'payment_option' => 'abapay_khqr',
                // 'first_name' => $request->input('customer_info.first_name', ''),
                // 'last_name' => $request->input('customer_info.last_name', ''),
                // 'email' => $request->input('customer_info.email', ''),
                // 'phone' => $request->input('customer_info.phone', ''),
                'items' => $request->order_items ?? [],
                'callback_url' => route('payway.callback', [], true), // Absolute URL
                'return_deeplink' => $returnDeeplink,
                'lifetime' => 3, // 3 minutes
                'qr_image_template' => 'template3_color',
            ];

            $result = $this->payWayService->generateQR($data);

            if ($result['success']) {
                // Store transaction in database
                DB::table('payway_transactions')->insert([
                    'tran_id' => $result['data']['tran_id'],
                    'branch_id' => $request->branch_id,
                    'payment_method_id' => $request->payment_method_id,
                    'order_id' => $request->order_id ?? null,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'qr_string' => $result['data']['qrString'],
                    'qr_image' => $result['data']['qrImage'],
                    'abapay_deeplink' => $result['data']['abapay_deeplink'],
                    'payment_status_code' => 2, // PENDING
                    'payment_status' => 'PENDING',
                    'response_data' => json_encode($result['data']),
                    'telegram_user_id' => $request->telegram_user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'data' => $result['data'],
                    'status' => ['code' => '0', 'message' => 'Success']
                ]);
            }

            return response()->json([
                'status' => [
                    'code' => $result['code'] ?? '1',
                    'message' => $result['error'] ?? 'Failed to generate QR'
                ]
            ], 400);

        } catch (Exception $e) {
            Log::error('TelegramMiniApp PayWay Generate QR Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => ['code' => '99', 'message' => 'Internal server error']
            ], 500);
        }
    }

    /**
     * Check transaction status (no auth required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tran_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => [
                    'code' => '1',
                    'message' => $validator->errors()->first()
                ]
            ], 422);
        }

        try {
            $result = $this->payWayService->checkTransaction($request->tran_id);

            if ($result['success']) {
                // Update transaction in database
                $updateData = [
                    'payment_status_code' => $result['data']['payment_status_code'] ?? null,
                    'payment_status' => $result['data']['payment_status'] ?? null,
                    'payment_amount' => $result['data']['payment_amount'] ?? null,
                    'payment_currency' => $result['data']['payment_currency'] ?? null,
                    'apv' => $result['data']['apv'] ?? null,
                    'transaction_date' => $result['data']['transaction_date'] ?? null,
                    'response_data' => json_encode($result['data']),
                    'updated_at' => now(),
                ];

                DB::table('payway_transactions')
                    ->where('tran_id', $request->tran_id)
                    ->update($updateData);

                // If payment is approved, mark the order as PAID and create transaction record
                if (isset($result['data']['payment_status_code']) && $result['data']['payment_status_code'] == 0) {
                    $paywayTxn = DB::table('payway_transactions')
                        ->where('tran_id', $request->tran_id)
                        ->first();

                    if ($paywayTxn && $paywayTxn->order_id) {
                        try {
                            $order = FrontendOrder::find($paywayTxn->order_id);
                            if ($order) {
                                $this->orderService->markFrontendOrderAsPaid($order, $request->tran_id);

                                Log::info('TelegramMiniApp PayWay: order marked as PAID and transaction created', [
                                    'tran_id'         => $request->tran_id,
                                    'order_id'        => $order->id,
                                    'order_serial_no' => $order->order_serial_no,
                                ]);
                            } else {
                                Log::warning('TelegramMiniApp PayWay: order not found after payment approved', [
                                    'order_id' => $paywayTxn->order_id,
                                    'tran_id'  => $request->tran_id,
                                ]);
                            }
                        } catch (Exception $e) {
                            Log::error('TelegramMiniApp PayWay: failed to mark order as paid', [
                                'tran_id'  => $request->tran_id,
                                'order_id' => $paywayTxn->order_id,
                                'error'    => $e->getMessage(),
                            ]);
                        }
                    } else {
                        Log::info('TelegramMiniApp PayWay: payment approved but no order linked', [
                            'tran_id' => $request->tran_id,
                        ]);
                    }
                }

                return response()->json([
                    'data' => $result['data'],
                    'status' => ['code' => '00', 'message' => 'Success']
                ]);
            }

            return response()->json([
                'status' => [
                    'code' => $result['code'] ?? '1',
                    'message' => $result['error'] ?? 'Failed to check transaction'
                ]
            ], 400);

        } catch (Exception $e) {
            Log::error('TelegramMiniApp PayWay Check Transaction Error', [
                'error' => $e->getMessage(),
                'tran_id' => $request->tran_id ?? 'unknown'
            ]);
            return response()->json([
                'status' => ['code' => '99', 'message' => 'Internal server error']
            ], 500);
        }
    }

    /**
     * Close/Cancel transaction (no auth required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function closeTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tran_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => [
                    'code' => '1',
                    'message' => $validator->errors()->first()
                ]
            ], 422);
        }

        try {
            // Check if transaction exists in database
            $transaction = DB::table('payway_transactions')
                ->where('tran_id', $request->tran_id)
                ->first();

            if (!$transaction) {
                Log::warning('TelegramMiniApp PayWay Close Transaction - Transaction not found', [
                    'tran_id' => $request->tran_id
                ]);
                
                // Transaction doesn't exist, but that's okay for cancellation
                return response()->json([
                    'status' => ['code' => '00', 'message' => 'Transaction not found or already closed']
                ]);
            }

            // If already cancelled, return success
            if ($transaction->payment_status_code == 7) {
                return response()->json([
                    'status' => ['code' => '00', 'message' => 'Transaction already cancelled']
                ]);
            }

            $result = $this->payWayService->closeTransaction($request->tran_id);

            if ($result['success']) {
                // Update transaction in database
                DB::table('payway_transactions')
                    ->where('tran_id', $request->tran_id)
                    ->update([
                        'payment_status_code' => 7, // CANCELLED
                        'payment_status' => 'CANCELLED',
                        'updated_at' => now(),
                    ]);

                return response()->json([
                    'data' => $result['data'] ?? [],
                    'status' => ['code' => '00', 'message' => 'Transaction cancelled successfully']
                ]);
            }

            // If PayWay API fails, still mark as cancelled in our database
            // The transaction might be expired or in a state that cannot be closed via API
            Log::warning('TelegramMiniApp PayWay Close Transaction - API failed, marking as cancelled anyway', [
                'tran_id' => $request->tran_id,
                'error' => $result['message'] ?? 'Unknown error'
            ]);

            DB::table('payway_transactions')
                ->where('tran_id', $request->tran_id)
                ->update([
                    'payment_status_code' => 7, // CANCELLED
                    'payment_status' => 'CANCELLED',
                    'updated_at' => now(),
                ]);

            // Return success even if PayWay API failed
            // From user's perspective, the cancellation is successful
            return response()->json([
                'status' => ['code' => '00', 'message' => 'Transaction cancelled (API close skipped)']
            ]);

        } catch (Exception $e) {
            Log::error('TelegramMiniApp PayWay Close Transaction Error', [
                'error' => $e->getMessage(),
                'tran_id' => $request->tran_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            // Even on exception, try to mark as cancelled
            try {
                DB::table('payway_transactions')
                    ->where('tran_id', $request->tran_id)
                    ->update([
                        'payment_status_code' => 7,
                        'payment_status' => 'CANCELLED',
                        'updated_at' => now(),
                    ]);
                
                return response()->json([
                    'status' => ['code' => '00', 'message' => 'Transaction marked as cancelled']
                ]);
            } catch (Exception $dbError) {
                // If even database update fails, log and return error
                Log::error('TelegramMiniApp PayWay Close Transaction - Database update also failed', [
                    'db_error' => $dbError->getMessage()
                ]);
                
                return response()->json([
                    'status' => ['code' => '99', 'message' => 'Failed to cancel transaction']
                ], 500);
            }
        }
    }

    /**
     * Link PayWay transaction to order (no auth required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkTransactionToOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tran_id' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => [
                    'code' => '1',
                    'message' => $validator->errors()->first()
                ]
            ], 422);
        }

        try {
            DB::table('payway_transactions')
                ->where('tran_id', $request->tran_id)
                ->update([
                    'order_id' => $request->order_id,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'status' => ['code' => '00', 'message' => 'Transaction linked to order successfully']
            ]);
        } catch (Exception $e) {
            Log::error('TelegramMiniApp PayWay Link Transaction Error', [
                'error' => $e->getMessage(),
                'tran_id' => $request->tran_id ?? 'unknown',
                'order_id' => $request->order_id ?? 'unknown'
            ]);
            return response()->json([
                'status' => ['code' => '99', 'message' => 'Internal server error']
            ], 500);
        }
    }
}
