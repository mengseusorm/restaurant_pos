<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Activity;
use App\Enums\PaymentStatus;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\HuioneCallbackRequest;
use App\Libraries\AppLibrary;
use App\Models\Currency;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\ThemeSetting;
use App\Services\PaymentManagerService;
use App\Services\HuionePayment\HuioneService;
use App\Services\PaymentCallbackHistoryService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;

class HuionePaymentController extends Controller
{
    protected HuioneService $huioneService;
    protected PaymentCallbackHistoryService $callbackHistoryService;

    public function __construct(HuioneService $huioneService, PaymentCallbackHistoryService $callbackHistoryService)
    {
        $this->huioneService = $huioneService;
        $this->callbackHistoryService = $callbackHistoryService;
    }

    /**
     * Place order with Huione payment gateway
     *
     * @param Order $order
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function placeOrder(Order $order, Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $result = $this->huioneService->createPaymentOrder($order);
            return response($result, 200);
        } catch (Exception $e) {
            Log::error("Place order failed: " . $e->getMessage());
            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Order already paid' ? 400 : 422);
        }
    }

    /**
     * Check payment status for an order
     *
     * @param Order $order
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function paymentStatus(Order $order, Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $result = $this->huioneService->checkPaymentStatus($order);
            return response($result, 200);
        } catch (Exception $e) {
            Log::error("Payment status check failed: " . $e->getMessage());
            
            // Return appropriate status code based on error type
            $statusCode = match (true) {
                str_contains($e->getMessage(), 'not found') => 404,
                str_contains($e->getMessage(), 'data字段不存在') => 422,
                default => 500
            };

            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }



    /**
     * Get Huione payment gateway configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function getConfiguration(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            if (!$this->huioneService->isConfigured()) {
                return response([
                    'status' => false,
                    'message' => 'Huione payment gateway is not properly configured'
                ], 422);
            }

            $config = $this->huioneService->getConfiguration();
            return response([
                'status' => true,
                'configuration' => $config
            ], 200);
        } catch (Exception $e) {
            Log::error("Get configuration failed: " . $e->getMessage());
            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund for an order
     *
     * @param Order $order
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function processRefund(Order $order, Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->validate([
                'refund_amount' => 'required|numeric|min:0.01',
                'reason' => 'required|string|max:255',
                'transaction_id' => 'nullable|string'
            ]);

            // Get payment order for this order
            $paymentOrder = PaymentOrder::where([
                'order_id' => $order->id,
                'status' => PaymentStatus::PAID
            ])->first();

            if (!$paymentOrder) {
                return response([
                    'status' => false,
                    'message' => 'No paid payment order found for this order'
                ], 404);
            }

            $result = $this->huioneService->processRefund(
                $paymentOrder,
                $request->refund_amount,
                $request->reason,
                $request->transaction_id
            );

            return response($result, 200);
        } catch (Exception $e) {
            Log::error("Process refund failed: " . $e->getMessage());
            
            $statusCode = match (true) {
                str_contains($e->getMessage(), 'Cannot refund unpaid order') => 400,
                str_contains($e->getMessage(), 'Invalid refund amount') => 400,
                str_contains($e->getMessage(), 'Transaction ID is required') => 422,
                str_contains($e->getMessage(), 'data字段不存在') => 422,
                default => 500
            };

            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Check refund status
     *
     * @param Order $order
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function checkRefundStatus(Order $order, Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->validate([
                'refund_id' => 'required|string'
            ]);

            // Get payment order for this order
            $paymentOrder = PaymentOrder::where(['order_id' => $order->id])->first();

            if (!$paymentOrder) {
                return response([
                    'status' => false,
                    'message' => 'Payment order not found for this order'
                ], 404);
            }

            $result = $this->huioneService->checkRefundStatus(
                $paymentOrder,
                $request->refund_id
            );

            return response($result, 200);
        } catch (Exception $e) {
            Log::error("Check refund status failed: " . $e->getMessage());
            
            $statusCode = match (true) {
                str_contains($e->getMessage(), 'data字段不存在') => 422,
                default => 500
            };

            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Check payment order status for multiple transactions within a time period
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function checkPaymentOrderStatus(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->validate([
                'time_to_check' => 'nullable|integer|min:1|max:3600', // Max 1 hour
                'branch_id' => 'nullable|integer|exists:branches,id'
            ]);

            $timeToCheck = $request->get('time_to_check', 60); // Default 60 seconds
            $branchId = $request->get('branch_id'); // Optional branch filtering

            $result = $this->huioneService->checkPaymentOrderStatus($timeToCheck, $branchId);

            return response($result, 200);
        } catch (Exception $e) {
            Log::error("Check payment order status failed: " . $e->getMessage());
            
            $statusCode = match (true) {
                str_contains($e->getMessage(), 'data字段不存在') => 422,
                str_contains($e->getMessage(), 'validation') => 422,
                default => 500
            };

            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Handle Huione payment callback
     *
     * @param HuioneCallbackRequest $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function callback(HuioneCallbackRequest $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $callbackHistory = null;
        $validationErrors = null;
        $isValid = true;

        try {
            // First, try to validate the request
            $validatedData = $request->validated();
        } catch (Exception $e) {
            $isValid = false;
            $validationErrors = $e->getMessage();
            $validatedData = [];
        }

        try {
            // Log the callback request immediately
            $callbackHistory = $this->callbackHistoryService->logCallback(
                $request,
                'huione',
                $validatedData,
                $isValid,
                $validationErrors
            );

            // If validation failed, return error early
            if (!$isValid) {
                $this->callbackHistoryService->updateProcessingResult(
                    $callbackHistory,
                    ['status' => false, 'message' => 'Validation failed'],
                    422,
                    false,
                    $validationErrors
                );

                return response([
                    'status' => false,
                    'message' => 'Validation failed'
                ], 422);
            }

            Log::info('Huione payment callback received', [
                'callback_history_id' => $callbackHistory->id,
                'outTradeNo' => $request->outTradeNo,
                'status' => $request->status,
                'transactionId' => $request->transactionId,
                'timestamp' => $request->timestamp
            ]);

            // Process callback via service
            $result = $this->huioneService->handleCallback($validatedData);

            // Update callback history with successful processing
            $this->callbackHistoryService->updateProcessingResult(
                $callbackHistory,
                $result,
                200,
                true
            );

            return response($result, 200);

        } catch (Exception $e) {
            $errorMessage = "Huione payment callback failed: " . $e->getMessage();
            
            Log::error($errorMessage, [
                'callback_history_id' => $callbackHistory?->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update callback history with processing error
            if ($callbackHistory) {
                $this->callbackHistoryService->updateProcessingResult(
                    $callbackHistory,
                    ['status' => false, 'message' => $e->getMessage()],
                    422,
                    false,
                    $e->getMessage()
                );
            }

            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
