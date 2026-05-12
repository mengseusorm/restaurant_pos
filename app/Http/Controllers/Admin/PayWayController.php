<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentCallbackHistory;
use App\Models\PaywayCallbackHistory;
use App\Services\PayWayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Auth;

class PayWayController extends Controller
{
    protected $payWayService;

    public function __construct(PayWayService $payWayService)
    {
        $this->payWayService = $payWayService;
    }

    /**
     * Generate QR code for payment
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
                'lifetime' => 3, // 5 minutes
                'qr_image_template' => 'template3_color',
            ];
            
            Log::info('PayWay Generate QR Data', $data);

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
            Log::error('PayWay Generate QR Controller Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => ['code' => '99', 'message' => 'Internal server error']
            ], 500);
        }
    }

    /**
     * Check transaction status
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

                // If payment is approved, update the order if order_id exists
                if (isset($result['data']['payment_status_code']) && $result['data']['payment_status_code'] == 0) {
                    $transaction = DB::table('payway_transactions')
                        ->where('tran_id', $request->tran_id)
                        ->first();
                    
                    if ($transaction && $transaction->order_id) {
                        // Payment is approved, you can update order status here if needed
                        Log::info('PayWay Payment Approved', [
                            'tran_id' => $request->tran_id,
                            'order_id' => $transaction->order_id,
                            'amount' => $transaction->amount
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
            Log::error('PayWay Check Transaction Controller Error', [
                'error' => $e->getMessage(),
                'tran_id' => $request->tran_id ?? 'unknown'
            ]);
            return response()->json([
                'status' => ['code' => '99', 'message' => 'Internal server error']
            ], 500);
        }
    }

    /**
     * Close/Cancel transaction
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
            $tranId = $request->tran_id;
            
            // Check if transaction exists in our database
            $transaction = DB::table('payway_transactions')
                ->where('tran_id', $tranId)
                ->first();

            // If transaction doesn't exist, return success (nothing to cancel)
            if (!$transaction) {
                Log::warning('PayWay closeTransaction: Transaction not found in database', [
                    'tran_id' => $tranId
                ]);
                return response()->json([
                    'data' => ['message' => 'Transaction not found'],
                    'status' => ['code' => '00', 'message' => 'Transaction cancelled successfully']
                ]);
            }

            // If transaction is already cancelled, return success
            if ($transaction->payment_status_code == 7) {
                Log::info('PayWay closeTransaction: Transaction already cancelled', [
                    'tran_id' => $tranId
                ]);
                return response()->json([
                    'data' => ['message' => 'Transaction already cancelled'],
                    'status' => ['code' => '00', 'message' => 'Transaction cancelled successfully']
                ]);
            }

            // Try to close transaction with PayWay API
            $result = $this->payWayService->closeTransaction($tranId);

            if ($result['success']) {
                // PayWay API successfully closed the transaction
                DB::table('payway_transactions')
                    ->where('tran_id', $tranId)
                    ->update([
                        'payment_status_code' => 7, // CANCELLED
                        'payment_status' => 'CANCELLED',
                        'updated_at' => now(),
                    ]);

                Log::info('PayWay closeTransaction: Successfully cancelled via API', [
                    'tran_id' => $tranId
                ]);

                return response()->json([
                    'data' => $result['data'],
                    'status' => ['code' => '00', 'message' => 'Transaction cancelled successfully']
                ]);
            }

            // PayWay API failed, but we'll still mark as cancelled locally
            // This handles cases where transaction is expired, already processed, etc.
            Log::warning('PayWay closeTransaction: API failed, marking as cancelled locally (non-critical)', [
                'tran_id' => $tranId,
                'payway_error' => $result['error'] ?? 'Unknown error',
                'payway_code' => $result['code'] ?? 'Unknown code'
            ]);

            // Update local database to mark as cancelled regardless of PayWay API response
            // This ensures user can proceed even if PayWay API is down or rejects the request
            try {
                DB::table('payway_transactions')
                    ->where('tran_id', $tranId)
                    ->update([
                        'payment_status_code' => 7, // CANCELLED
                        'payment_status' => 'CANCELLED',
                        'updated_at' => now(),
                    ]);

                // Return success to frontend - cancellation completed locally
                return response()->json([
                    'data' => ['message' => 'Transaction cancelled locally'],
                    'status' => ['code' => '00', 'message' => 'Transaction cancelled successfully']
                ]);
            } catch (Exception $dbError) {
                // Only fail if we can't update our own database (catastrophic)
                Log::error('PayWay closeTransaction: Failed to update database', [
                    'tran_id' => $tranId,
                    'error' => $dbError->getMessage()
                ]);
                return response()->json([
                    'status' => ['code' => '99', 'message' => 'Database error']
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('PayWay closeTransaction: Unexpected error', [
                'error' => $e->getMessage(),
                'tran_id' => $request->tran_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Even on error, try to mark as cancelled
            try {
                DB::table('payway_transactions')
                    ->where('tran_id', $request->tran_id)
                    ->update([
                        'payment_status_code' => 7,
                        'payment_status' => 'CANCELLED',
                        'updated_at' => now(),
                    ]);
                
                return response()->json([
                    'data' => ['message' => 'Transaction cancelled with errors'],
                    'status' => ['code' => '00', 'message' => 'Transaction cancelled successfully']
                ]);
            } catch (Exception $finalError) {
                return response()->json([
                    'status' => ['code' => '99', 'message' => 'Internal server error']
                ], 500);
            }
        }
    }

    /**
     * PayWay callback handler
     * Receives pushback notifications from PayWay after a QR payment is made.
     *
     * Payload fields (ABA QR API):
     *   tran_id          string  Payment transaction ID generated by PayWay (max 20)
     *   apv              int     Transaction approval code (6 digits)
     *   status           string  Status code ("00" = success)
     *   merchant_ref_no  string  Merchant payment reference number
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $payload = $request->all();

        Log::info('PayWay Callback Received', ['data' => $payload]);

        // ── 1. Persist raw callback data to payway_callback_histories ──────────
        $paywayCallback = PaywayCallbackHistory::create([
            'tran_id'          => $request->input('tran_id'),
            'apv'              => $request->input('apv'),
            'status'           => $request->input('status'),
            'merchant_ref_no'  => $request->input('merchant_ref_no'),
            'raw_payload'      => $payload,
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
            'is_processed'     => false,
        ]);

        // ── 2. Also persist to generic payment_callback_histories ─────────────
        $callbackHistory = PaymentCallbackHistory::create([
            'payment_gateway'      => 'payway',
            'out_trade_no'         => $request->input('tran_id'),
            'transaction_id'       => $request->input('tran_id'),
            'status'               => $request->input('status'),
            'merchant_id'          => config('payway.merchant_id'),
            'app_id'               => config('payway.app_id'),
            'callback_url'         => $request->fullUrl(),
            'request_headers'      => $request->headers->all(),
            'request_data'         => $payload,
            'ip_address'           => $request->ip(),
            'user_agent'           => $request->userAgent(),
            'is_valid'             => false,
            'is_processed'         => false,
            'callback_received_at' => now(),
        ]);

        try {
            $tranId = $request->input('tran_id');

            // ── 3. Validate required field ─────────────────────────────────────
            if (!$tranId) {
                $note = 'Missing tran_id in callback payload';
                Log::warning('PayWay Callback Validation Failed', ['note' => $note, 'payload' => $payload]);

                $paywayCallback->update([
                    'is_processed'     => false,
                    'processing_notes' => $note,
                ]);
                $callbackHistory->update([
                    'is_valid'          => false,
                    'is_processed'      => false,
                    'validation_errors' => [$note],
                    'response_status'   => 422,
                ]);

                return response()->json(['status' => 'received']);
            }

            // ── 4. Verify payment with PayWay Check Transaction API ───────────
            $result = $this->payWayService->checkTransaction($tranId);

            if ($result['success']) {
                // Update payway_transactions with confirmed status from PayWay
                DB::table('payway_transactions')
                    ->where('tran_id', $tranId)
                    ->update([
                        'payment_status_code' => $result['data']['payment_status_code'] ?? null,
                        'payment_status'      => $result['data']['payment_status'] ?? null,
                        'payment_amount'      => $result['data']['payment_amount'] ?? null,
                        'payment_currency'    => $result['data']['payment_currency'] ?? null,
                        'apv'                 => $result['data']['apv'] ?? null,
                        'transaction_date'    => $result['data']['transaction_date'] ?? null,
                        'response_data'       => json_encode($result['data']),
                        'updated_at'          => now(),
                    ]);

                $paywayCallback->update([
                    'is_processed'     => true,
                    'processed_at'     => now(),
                    'processing_notes' => 'Transaction verified and payway_transactions updated.',
                ]);
                $callbackHistory->update([
                    'is_valid'       => true,
                    'is_processed'   => true,
                    'response_data'  => $result['data'],
                    'response_status' => 200,
                ]);
            } else {
                $errorNote = $result['error'] ?? 'Failed to verify transaction via Check Transaction API';
                Log::warning('PayWay Callback: checkTransaction failed', [
                    'tran_id' => $tranId,
                    'error'   => $errorNote,
                ]);

                $paywayCallback->update([
                    'is_processed'     => false,
                    'processing_notes' => $errorNote,
                ]);
                $callbackHistory->update([
                    'is_valid'          => true,
                    'is_processed'      => false,
                    'processing_errors' => [$errorNote],
                    'response_status'   => 400,
                ]);
            }

            return response()->json(['status' => 'received']);

        } catch (Exception $e) {
            Log::error('PayWay Callback Error', [
                'error' => $e->getMessage(),
                'data'  => $payload,
            ]);

            $paywayCallback->update([
                'is_processed'     => false,
                'processing_notes' => $e->getMessage(),
            ]);
            $callbackHistory->update([
                'is_valid'          => false,
                'is_processed'      => false,
                'processing_errors' => [$e->getMessage()],
                'response_status'   => 500,
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Link PayWay transaction to order
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
            Log::error('PayWay Link Transaction Error', [
                'error' => $e->getMessage(),
                'tran_id' => $request->tran_id ?? 'unknown',
                'order_id' => $request->order_id ?? 'unknown'
            ]);
            return response()->json([
                'status' => ['code' => '99', 'message' => 'Internal server error']
            ], 500);
        }
    }

    /**
     * Process refund for a transaction
     * Accepts either transaction_id (from transactions table) or tran_id (from payway_transactions table)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refundTransaction(Request $request)
    {
        // Validate that at least one of transaction_id or tran_id is provided
        $validator = Validator::make($request->all(), [
            'refund_amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => [
                    'code' => '1',
                    'message' => $validator->errors()->first()
                ]
            ], 422);
        }

        // Check that either transaction_id or tran_id is provided
        if (!$request->has('transaction_id') && !$request->has('tran_id')) {
            return response()->json([
                'status' => [
                    'code' => '1',
                    'message' => 'Either transaction_id or tran_id is required'
                ]
            ], 422);
        }

        try {
            $transaction = null;
            $paywayTransaction = null;
            $tranIdForRefund = null;

            // If tran_id is provided, find the PaywayTransaction
            if ($request->has('tran_id')) {
                $paywayTransaction = \App\Models\PaywayTransaction::where('tran_id', $request->tran_id)->first();
                
                if (!$paywayTransaction) {
                    return response()->json([
                        'status' => [
                            'code' => '1',
                            'message' => 'PayWay transaction not found'
                        ]
                    ], 404);
                }

                $tranIdForRefund = $paywayTransaction->tran_id;
                $refundAmount = $request->refund_amount ?? $paywayTransaction->amount;

                // Validate refund amount doesn't exceed PayWay transaction amount
                if ($refundAmount > $paywayTransaction->amount) {
                    return response()->json([
                        'status' => [
                            'code' => '1',
                            'message' => 'Refund amount cannot exceed transaction amount'
                        ]
                    ], 422);
                }

                // Check if there's an associated order
                if ($paywayTransaction->order_id) {
                    // Find the related transaction in transactions table
                    $transaction = DB::table('transactions')
                        ->where('order_id', $paywayTransaction->order_id)
                        ->where('transaction_no', $paywayTransaction->tran_id)
                        ->first();
                }

            } else {
                // Use transaction_id from transactions table
                $transaction = DB::table('transactions')
                    ->where('id', $request->transaction_id)
                    ->first();

                if (!$transaction) {
                    return response()->json([
                        'status' => [
                            'code' => '1',
                            'message' => 'Transaction not found'
                        ]
                    ], 404);
                }

                $tranIdForRefund = $transaction->transaction_no;
                $refundAmount = $request->refund_amount;

                // Validate refund amount doesn't exceed transaction amount
                if ($refundAmount > $transaction->amount) {
                    return response()->json([
                        'status' => [
                            'code' => '1',
                            'message' => 'Refund amount cannot exceed transaction amount'
                        ]
                    ], 422);
                }

                // Get payment method to check provider
                $paymentMethod = DB::table('payment_methods')
                    ->where('id', $transaction->pos_payment_method)
                    ->first();

                if (!$paymentMethod || strtolower($paymentMethod->provider) !== 'payway') {
                    return response()->json([
                        'status' => [
                            'code' => '1',
                            'message' => 'This payment method does not support refunds'
                        ]
                    ], 422);
                }
            }

            // Process refund through PayWay
            $result = $this->payWayService->refundTransaction(
                $tranIdForRefund,
                $refundAmount
            );

            if ($result['success']) {
                // Create refund transaction record if there's an associated order
                if ($transaction && $transaction->order_id) {
                    DB::table('transactions')->insert([
                        'order_id' => $transaction->order_id,
                        'user_id' => Auth::id(),
                        'transaction_no' => $result['data']['transaction_id'] ?? $transaction->transaction_no . '-REFUND',
                        'amount' => $refundAmount,
                        'payment_method' => $transaction->payment_method,
                        'pos_payment_method' => $transaction->pos_payment_method,
                        'type' => 'refund',
                        'sign' => '-',
                        'reference_transaction' => $transaction->id,
                        'gateway_response' => json_encode($result['data']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update order payment status to unpaid after refund
                    DB::table('orders')
                        ->where('id', $transaction->order_id)
                        ->update([
                            'payment_status' => PaymentStatus::UNPAID, // UNPAID
                            'updated_at' => now()
                        ]);
                } elseif ($paywayTransaction && $paywayTransaction->order_id) {
                    // If we only have PaywayTransaction with order_id
                    DB::table('orders')
                        ->where('id', $paywayTransaction->order_id)
                        ->update([
                            'payment_status' => PaymentStatus::UNPAID,
                            'updated_at' => now()
                        ]);
                }

                // Update PaywayTransaction status if it exists
                if ($paywayTransaction) {
                    $paywayTransaction->update([
                        'payment_status' => 'REFUNDED',
                        'payment_status_code' => -1, // or whatever code you use for refunded
                    ]);
                }

                return response()->json([
                    'status' => [
                        'code' => '00',
                        'message' => 'Refund processed successfully'
                    ],
                    'data' => $result['data']
                ], 200);
            }

            return response()->json([
                'status' => [
                    'code' => $result['error_code'] ?? '99',
                    'message' => $result['message']
                ],
                'data' => $result['data'] ?? null
            ], 400);

        } catch (Exception $e) {
            Log::error('Refund Controller Error', [
                'error' => $e->getMessage(),
                'transaction_id' => $request->transaction_id ?? 'N/A',
                'tran_id' => $request->tran_id ?? 'N/A'
            ]);
            
            return response()->json([
                'status' => [
                    'code' => '99',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]
            ], 500);
        }
    }}