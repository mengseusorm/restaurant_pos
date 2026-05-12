<?php

namespace App\Services\HuionePayment;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentOrder;
use App\Models\Transaction;
use App\Services\HuionePayment\enum\AppConfigEnum;
use App\Services\HuionePayment\enum\PathEnum;
use App\Services\HuionePayment\req\OrderInfoReq;
use App\Services\HuionePayment\req\OrderReq;
use App\Services\HuionePayment\req\RefundReq;
use App\Services\HuionePayment\util\NonceUtil;
use App\Services\HuionePayment\util\PostUtil;
use App\Services\HuionePayment\util\SignUtil;
use Exception;
use Illuminate\Support\Facades\Log;

class HuioneService
{
    protected ?object $paymentGateway;
    protected ?object $paymentGatewayOption;
    protected string $appId;
    protected string $privateKey;
    protected string $secretKey;
    protected bool $isLive;

    public function __construct()
    {
        $this->initializePaymentGateway();
    }

    /**
     * Initialize payment gateway configuration
     */
    private function initializePaymentGateway(): void
    {
        try {
            // $paymentGateway = PaymentGateway::with('gatewayOptions')
            //     ->where(['slug' => 'huione'])
            //     ->first();

            // if (!blank($paymentGateway)) {
            //     $this->paymentGateway = $paymentGateway;
            //     $this->paymentGatewayOption = $this->paymentGateway->gatewayOptions->pluck('value', 'option');

            //     // Check if required options exist, if not create them
            //     if ($this->paymentGatewayOption->isEmpty()) {
            //         // Create default gateway options
            //         $defaultOptions = [
            //             'huione_app_id' => AppConfigEnum::$appId,
            //             'huione_private_key' => AppConfigEnum::$privateKey,
            //             'huione_mode' => 'sandbox',
            //             'huione_status' => '1'
            //         ];

            //         foreach ($defaultOptions as $option => $value) {
            //             $this->paymentGateway->gatewayOptions()->create([
            //                 'option' => $option,
            //                 'value' => $value
            //             ]);
            //         }

            //         // Reload the payment gateway with options
            //         $reloadedGateway = PaymentGateway::with('gatewayOptions')
            //             ->where(['slug' => 'huione'])
            //             ->first();

            //         if ($reloadedGateway) {
            //             $this->paymentGateway = $reloadedGateway;
            //             $this->paymentGatewayOption = $this->paymentGateway->gatewayOptions->pluck('value', 'option');
            //         } else {
            //             throw new Exception('Failed to reload Huione payment gateway after creating options');
            //         }
            //     }
                
            //     // Use configuration from AppConfigEnum or database
            //     $this->appId = $this->paymentGatewayOption['huione_app_id'] ?? AppConfigEnum::$appId;
            //     $this->privateKey = $this->paymentGatewayOption['huione_private_key'] ?? AppConfigEnum::$privateKey;
            //     $this->secretKey = $this->paymentGatewayOption['huione_secret_key'] ?? AppConfigEnum::$secretKey ?? '';
            //     $this->isLive = ($this->paymentGatewayOption['huione_mode'] ?? 'sandbox') === 'live';

            //     // Log::info("Huione Payment Gateway Config: ", [
            //     //     'appId' => $this->appId,
            //     //     'privateKey' => $this->privateKey,
            //     //     'mode' => $this->paymentGatewayOption['huione_mode'] ?? 'sandbox'
            //     // ]);

            // } else {
            //     // Set properties to null when no payment gateway found
            //     $this->paymentGateway = null;
            //     $this->paymentGatewayOption = null;
            //     throw new Exception('Huione payment gateway not configured');
            // }
        } catch (Exception $exception) {
            log::error('Error initializing Huione payment gateway: ' . $exception->getMessage());

            // Set payment gateway properties to null when configuration fails
            $this->paymentGateway = null;
            $this->paymentGatewayOption = null;
            
            // Fallback to AppConfigEnum values
            $this->appId = AppConfigEnum::$appId;
            $this->privateKey = AppConfigEnum::$privateKey;
            $this->secretKey = AppConfigEnum::$secretKey ?? '';
            $this->isLive = AppConfigEnum::$isLive ?? false;
        }


    }

    /**
     * Create a payment order for Huione
     *
     * @param Order $order
     * @return array
     * @throws Exception
     */
    public function createPaymentOrder(Order $order): array
    {
        // Check if the order is already paid
        if ($order->payment_status === PaymentStatus::PAID) {
            Log::info("Order already paid: " . $order->id);
            throw new Exception('Order already paid');
        }

        // Check for existing valid payment order
        $existingPaymentOrder = $this->getValidPaymentOrder($order);
        if ($existingPaymentOrder) {
            Log::info("Payment order found and not expired: ", $existingPaymentOrder->toArray());
            return [
                'status' => true,
                'paymentOrder' => $existingPaymentOrder
            ];
        }

        Log::info("Order Details: ", $order->toArray());

        // Generate unique transaction number
        $outTradeNo = $this->generateTransactionNumber();

        // Configure currency and exchange rate
        $currency = 'USD';
        $exchangeRate = 4000;
        $totalPrice = $order->total / $exchangeRate;

        // Create order request
        $orderReq = new OrderReq(
            amount: $totalPrice,
            currency: $currency,
            description: '汇旺UAT测试支付',
            nonce: NonceUtil::getNonce(),
            outTradeNo: $outTradeNo,
            timeExpire: 300,
            timestamp: round(microtime(true) * 1000),
            attach: '汇旺支付测试'
        );

        Log::info("Order Request: ", $orderReq->toArray());

        $paramArray = json_decode(json_encode($orderReq), true);

        // Sign the request
        $sign = (new SignUtil)->sign($paramArray, $this->privateKey);
        $paramArray['sign'] = $sign;

        Log::info("签名: " . $sign);

        // JSON serialization
        $jsonString = json_encode($paramArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Log::info("请求参数: $jsonString");

        try {
            // Send POST request
            $responseBody = PostUtil::sendPostRequestTest(
                PathEnum::$createPrepayOrder,
                $jsonString,
                $this->appId
            );

            Log::info("响应: $responseBody");

            $data = json_decode($responseBody, true)['data'] ?? null;

            if (!$data) {
                Log::error("data字段不存在");
                throw new Exception('data字段不存在');
            }

            Log::info($data);

            if (!isset($data['qrCode'])) {
                Log::error("qurCode字段不存在");
                throw new Exception('qurCode字段不存在');
            }

            // Create or update payment order
            $paymentOrder = $this->createOrUpdatePaymentOrder(
                $order,
                $outTradeNo,
                $totalPrice,
                $currency,
                $jsonString,
                $data
            );

            Log::info("Payment Order Created: ", $paymentOrder->toArray());

            return [
                'status' => true,
                'paymentOrder' => $paymentOrder
            ];

        } catch (Exception $e) {
            Log::error("请求失败: " . $e->getMessage());
            throw new Exception('请求失败: ' . $e->getMessage());
        }
    }

    /**
     * Check payment status from Huione
     *
     * @param Order $order
     * @return array
     * @throws Exception
     */
    public function checkPaymentStatus(Order $order): array
    {
        Log::info("Checking payment status for order: " . $order->id);
        Log::info("Order Details: ", $order->toArray());

        // Check if order is already paid
        if ($order->payment_status === PaymentStatus::PAID) {
            Log::info("Order already paid: " . $order->id);
            return [
                'status' => true,
                'message' => 'Order already paid',
                'paymentStatus' => 'DONE_PAYMENT'
            ];
        }

        // Get payment order
        $paymentOrder = PaymentOrder::where(['order_id' => $order->id])->first();

        if (!$paymentOrder) {
            throw new Exception('Payment order not found');
        }

        if ($paymentOrder->status == PaymentStatus::PAID) {
            Log::info("Payment order already paid: ", $paymentOrder->toArray());
            return [
                'status' => true,
                'message' => 'Order already paid',
                'paymentStatus' => 'DONE_PAYMENT'
            ];
        }

        // Create order info request
        $orderInfoReq = new OrderInfoReq(
            nonce: NonceUtil::getNonce(),
            outTradeNoList: [$paymentOrder->transaction_no],
            timestamp: round(microtime(true) * 1000),
        );

        $paramArray = $orderInfoReq->toArray();
        $sign = (new SignUtil)->sign($paramArray, AppConfigEnum::$privateKey);
        $paramArray['sign'] = $sign;

        $jsonString = json_encode($paramArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Log::info("请求参数: $jsonString");

        try {
            // Send query request
            $responseBody = PostUtil::sendPostRequestTest(
                PathEnum::$queryOrder,
                $jsonString,
                AppConfigEnum::$appId
            );

            Log::info("响应: $responseBody");

            $data = json_decode($responseBody, true)['data'] ?? null;

            if (!$data) {
                Log::error("data字段不存在");
                throw new Exception('data字段不存在');
            }

            Log::info("data: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // Process payment status
            foreach ($data as $index => $item) {
                $str = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $str = preg_replace_callback(
                    '/("status"\s*:\s*)("[^"]+")/',
                    function ($matches) {
                        return "\033[31m" . $matches[1] . $matches[2] . "\033[0m";
                    },
                    $str
                );
                Log::info(($index + 1) . ": " . $str);
            }

            // Check for successful payment
            foreach ($data as $item) {
                if (isset($item['status']) && $item['status'] === 'DONE_PAYMENT') {
                    return $this->processSuccessfulPayment($order, $paymentOrder, $item);
                }
            }

            // Payment not yet completed
            return [
                'status' => false,
                'message' => 'NOT YET PAYMENT',
                'data' => $data
            ];

        } catch (Exception $e) {
            Log::error("请求失败: " . $e->getMessage());
            throw new Exception('请求失败: ' . $e->getMessage());
        }
    }



    /**
     * Process refund for a payment
     *
     * @param PaymentOrder $paymentOrder
     * @param float $refundAmount
     * @param string $reason
     * @param string|null $transactionId
     * @return array
     * @throws Exception
     */
    public function processRefund(PaymentOrder $paymentOrder, float $refundAmount, string $reason, ?string $transactionId = null): array
    {
        Log::info("===== Starting Refund Process =====");
        Log::info("Processing refund for payment order: " . $paymentOrder->id);
        Log::info("Refund amount: $refundAmount, Reason: $reason, Transaction ID: " . ($transactionId ?? 'null'));

        // Validate payment order status
        Log::info("Step 1: Validating payment order status");
        if ($paymentOrder->status !== PaymentStatus::PAID) {
            Log::error("Refund validation failed: Payment order status is not PAID. Current status: " . $paymentOrder->status);
            throw new Exception('Cannot refund unpaid order');
        }
        Log::info("Payment order status validation passed");

        // Validate refund amount
        Log::info("Step 2: Validating refund amount");
        Log::info("Payment order amount: " . $paymentOrder->amount . ", Requested refund: $refundAmount");
        if ($refundAmount <= 0 || $refundAmount > $paymentOrder->amount) {
            Log::error("Refund amount validation failed. Amount: $refundAmount, Max allowed: " . $paymentOrder->amount);
            throw new Exception('Invalid refund amount');
        }
        Log::info("Refund amount validation passed");

        // Use transaction ID from gateway response if not provided
        Log::info("Step 3: Determining transaction ID");
        if (!$transactionId && isset($paymentOrder->gateway_response['transactionId'])) {
            $transactionId = $paymentOrder->gateway_response['transactionId'];
            Log::info("Transaction ID retrieved from gateway response: $transactionId");
        }

        if (!$transactionId) {
            Log::error("Transaction ID is missing and cannot be retrieved from gateway response");
            throw new Exception('Transaction ID is required for refund');
        }
        Log::info("Transaction ID confirmed: $transactionId");

        // Create refund request
        Log::info("Step 4: Creating refund request object");
        $refundReq = new RefundReq(
            nonce: NonceUtil::getNonce(),
            timestamp: round(microtime(true) * 1000),
            outTradeNo: $paymentOrder->transaction_no,
            reason: $reason,
            refund: $refundAmount,
            transactionId: $transactionId
        );

        Log::info("Refund Request created successfully: ", $refundReq->toArray());

        $paramArray = $refundReq->toArray();
        Log::info("Refund request parameters: ", $paramArray);

        // Sign the request
        Log::info("Step 5: Signing the refund request");
        $sign = (new SignUtil)->sign($paramArray, $this->privateKey);
        $paramArray['sign'] = $sign;

        Log::info("Refund request signed successfully. Signature: " . $sign);

        // JSON serialization
        Log::info("Step 6: Serializing request to JSON");
        $jsonString = json_encode($paramArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Log::info("Refund request JSON: $jsonString");

        try {
            Log::info("Step 7: Sending refund request to Huione API");
            Log::info("API Endpoint: " . PathEnum::$refund);
            Log::info("App ID: " . $this->appId);

            // Send refund request
            $responseBody = PostUtil::sendPostRequestTest(
                PathEnum::$refund,
                $jsonString,
                $this->appId
            );

            Log::info("Step 8: Processing API response");
            Log::info("Raw API response: $responseBody");

            $response = json_decode($responseBody, true);
            Log::info("Decoded response: ", $response);

            $data = $response['data'] ?? null;

            if (!$data) {
                Log::error("Step 8 Failed: Data field missing from API response");
                Log::error("Response structure: ", $response);
                throw new Exception('Refund data字段不存在');
            }

            Log::info("Step 8 Success: Data field extracted from response");
            Log::info("Refund response data: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // Update payment order with refund information
            Log::info("Step 9: Updating payment order with refund information");
            $refundRequests = $paymentOrder->refund_requests ?? [];
            Log::info("Existing refund requests count: " . count($refundRequests));

            $newRefundRequest = [
                'refund_id' => $data['refundId'] ?? null,
                'transaction_id' => $transactionId,
                'amount' => $refundAmount,
                'currency' => 'USD',
                'reason' => $reason,
                'status' => $data['status'] ?? 'pending',
                'request' => $jsonString,
                'response' => $data,
                'created_at' => now()->toISOString()
            ];

            $refundRequests[] = $newRefundRequest;
            Log::info("New refund request added: ", $newRefundRequest);

            $previousRefundAmount = $paymentOrder->refund_amount ?? 0;
            $newRefundAmount = $previousRefundAmount + $refundAmount;

            Log::info("Updating payment order - Previous refund amount: $previousRefundAmount, New total: $newRefundAmount");

            $paymentOrder->update([
                'refund_requests' => $refundRequests,
                'refund_status' => $data['status'] ?? 'pending',
                'refund_amount' => $newRefundAmount
            ]);

            Log::info("Payment order updated successfully");

            // Create refund transaction record
            Log::info("Step 10: Creating refund transaction record");
            $refundTransaction = $this->createRefundTransaction($paymentOrder, $refundAmount, $data);
            Log::info("Refund transaction created with ID: " . $refundTransaction->id);

            if($refundTransaction){
                Log::info("Step 10 Success: Refund transaction created successfully");

                Log::info("Step 11: Preparing success response");
                $successResponse = [
                    'status' => true,
                    'message' => 'Refund processed successfully',
                    'refund_data' => $data,
                    'refund_amount' => $refundAmount,
                    'payment_order' => $paymentOrder->fresh()
                ];

                Log::info("===== Refund Process Completed Successfully =====");
                Log::info("Final response: ", $successResponse);

                return $successResponse;
            }else{
                Log::error("Step 11 Failed: Refund transaction creation failed");
                $errorResponse = [
                    'status' => false,
                    'message' => 'Refund transaction creation failed',
                    'payment_order' => $paymentOrder->fresh()
                ];

                Log::info("===== Refund Process Completed with Errors =====");
                Log::info("Final response: ", $errorResponse);

                return $errorResponse;
            }


        } catch (Exception $e) {
            Log::error("===== Refund Process Failed =====");
            Log::error("Exception occurred in Step 7-11: " . $e->getMessage());
            Log::error("Exception trace: " . $e->getTraceAsString());
            throw new Exception('Refund 请求失败: ' . $e->getMessage());
        }
    }

    /**
     * Check refund status
     *
     * @param PaymentOrder $paymentOrder
     * @param string $refundId
     * @return array
     * @throws Exception
     */
    public function checkRefundStatus(PaymentOrder $paymentOrder, string $refundId): array
    {
        Log::info("Checking refund status for payment order: " . $paymentOrder->id);

        // Create refund status request - using OrderInfoReq as base structure
        $refundStatusReq = new OrderInfoReq(
            nonce: NonceUtil::getNonce(),
            outTradeNoList: [$paymentOrder->transaction_no],
            timestamp: round(microtime(true) * 1000)
        );

        $paramArray = $refundStatusReq->toArray();
        // Add refund ID to the request
        $paramArray['refundId'] = $refundId;

        // Sign the request
        $sign = (new SignUtil)->sign($paramArray, $this->privateKey);
        $paramArray['sign'] = $sign;

        $jsonString = json_encode($paramArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Log::info("Refund Status 请求参数: $jsonString");

        try {
            // Send refund status query
            $responseBody = PostUtil::sendPostRequestTest(
                PathEnum::$queryRefund,
                $jsonString,
                $this->appId
            );

            Log::info("Refund Status 响应: $responseBody");

            $response = json_decode($responseBody, true);
            $data = $response['data'] ?? null;

            if (!$data) {
                Log::error("Refund status data字段不存在");
                throw new Exception('Refund status data字段不存在');
            }

            Log::info("Refund status data: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // Update refund status in payment order if status has changed
            $refundRequests = $paymentOrder->refund_requests ?? [];
            foreach ($refundRequests as $index => $refundRequest) {
                if ($refundRequest['refund_id'] === $refundId) {
                    $refundRequests[$index]['status'] = $data['status'] ?? $refundRequest['status'];
                    $refundRequests[$index]['status_response'] = $data;
                    $refundRequests[$index]['updated_at'] = now()->toISOString();
                    break;
                }
            }

            $paymentOrder->update([
                'refund_requests' => $refundRequests,
                'refund_status' => $data['status'] ?? $paymentOrder->refund_status
            ]);

            return [
                'status' => true,
                'refund_status' => $data['status'] ?? 'unknown',
                'refund_data' => $data,
                'payment_order' => $paymentOrder->fresh()
            ];

        } catch (Exception $e) {
            Log::error("Refund status 请求失败: " . $e->getMessage());
            throw new Exception('Refund status 请求失败: ' . $e->getMessage());
        }
    }

    /**
     * Create refund transaction record
     *
     * @param PaymentOrder $paymentOrder
     * @param float $refundAmount
     * @param array $refundData
     * @return Transaction
     */
    private function createRefundTransaction(PaymentOrder $paymentOrder, float $refundAmount, array $refundData): Transaction
    {
        Log::info("===== Creating Refund Transaction =====");
        Log::info("Payment Order ID: " . $paymentOrder->id);
        Log::info("Order ID: " . $paymentOrder->order_id);
        Log::info("Transaction No: " . $paymentOrder->transaction_no);
        Log::info("Refund Amount: $refundAmount");
        Log::info("Refund Data: ", $refundData);

        // find exist transaction by transaction_no from paymentOrder
        Log::info("Step 1: Looking for existing transaction");
        $existingTransaction = Transaction::where('order_id', $paymentOrder->order_id)
            ->where('transaction_no', $paymentOrder->transaction_no)
            ->first();

        if($existingTransaction) {
            Log::info("Step 1 Success: Found existing transaction");
            Log::info("Existing Transaction ID: " . $existingTransaction->id);
            Log::info("Existing Transaction Details: ", $existingTransaction->toArray());

            // If exists, use the existing transaction
            Log::info("Step 2: Generating refund transaction number");
            // $refundTransactionNo = 'REFUND_' . ($refundData['refundId'] ?? uniqid());
            $refundTransactionNo = 'REFUND_' . $existingTransaction->transaction_no;
            Log::info("Generated Refund Transaction No: $refundTransactionNo");

            Log::info("Step 3: Creating refund transaction record");
            try {
                // Get branch and currency information
                $order = \App\Models\Order::with('branch.currency')->find($paymentOrder->order_id);
                $baseCurrency = $order?->branch?->currency ?? null;
                $baseCurrencyCode = $baseCurrency?->code ?? 'USD';
                $baseCurrencyId = $baseCurrency?->id;
                $transactionCurrency = $existingTransaction->transaction_currency ?? $baseCurrencyCode;
                $transactionCurrencyId = $existingTransaction->transaction_currency_id ?? $baseCurrencyId;
                $refundAmount = $existingTransaction->amount;
                $refundTransactionAmount = $existingTransaction->transaction_amount ?? $refundAmount;
                
                // Calculate exchange rate
                $amountBaseCurrency = $refundAmount;
                $exchangeRate = null;
                $exchangeRateBase = null;
                $exchangeRateTarget = null;
                
                if ($transactionCurrency !== $baseCurrencyCode && $refundTransactionAmount > 0) {
                    $exchangeRate = $refundAmount / $refundTransactionAmount;
                    $exchangeRateBase = $transactionCurrency;
                    $exchangeRateTarget = $baseCurrencyCode;
                }
                
                $transaction = Transaction::create([
                    'order_id' => $paymentOrder->order_id,
                    'transaction_no' => $refundTransactionNo,
                    'amount' => $refundAmount,
                    'currency' => $baseCurrencyCode,
                    'currency_id' => $baseCurrencyId,
                    'amount_base_currency' => $amountBaseCurrency,
                    'base_currency' => $baseCurrencyCode,
                    'base_currency_id' => $baseCurrency?->id,
                    'transaction_amount' => $refundTransactionAmount,
                    'transaction_currency' => $transactionCurrency,
                    'transaction_currency_id' => $transactionCurrencyId,
                    'change_amount' => 0,
                    'change_currency' => null,
                    'change_currency_id' => null,
                    'exchange_rate' => $exchangeRate,
                    'exchange_rate_base' => $exchangeRateBase,
                    'exchange_rate_target' => $exchangeRateTarget,
                    'payment_method' => 'Huione',
                    'sign' => '-', // Negative sign for refund
                    'type' => 'refund',
                    'reference_transaction' => $paymentOrder->transaction_no,
                    'gateway_response' => json_encode($refundData)
                ]);

                Log::info("Step 3 Success: Refund transaction created successfully");
                Log::info("New Refund Transaction ID: " . $transaction->id);
                Log::info("Refund transaction created: ", $transaction->toArray());

                Log::info("===== Refund Transaction Creation Completed =====");
                return $transaction;

            } catch (Exception $e) {
                Log::error("Step 3 Failed: Error creating refund transaction");
                Log::error("Error Message: " . $e->getMessage());
                Log::error("Error Trace: " . $e->getTraceAsString());
                throw new Exception('Failed to create refund transaction: ' . $e->getMessage());
            }

        } else {
            Log::error("Step 1 Failed: Original transaction not found");
            Log::error("Search Criteria - Order ID: " . $paymentOrder->order_id);
            Log::error("Search Criteria - Transaction No: " . $paymentOrder->transaction_no);

            // Additional debugging - check if any transactions exist for this order
            $allOrderTransactions = Transaction::where('order_id', $paymentOrder->order_id)->get();
            Log::error("All transactions for Order ID " . $paymentOrder->order_id . ": ", $allOrderTransactions->toArray());

            Log::error("===== Refund Transaction Creation Failed =====");
            throw new Exception('Original transaction not found for refund');
        }
    }

    /**
     * Process successful payment
     *
     * @param Order $order
     * @param PaymentOrder $paymentOrder
     * @param array $paymentData
     * @return array
     */
    private function processSuccessfulPayment(Order $order, PaymentOrder $paymentOrder, array $paymentData): array
    {
        // Update payment order status
        $paymentOrder->update([
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
            'gateway_response' => $paymentData,
        ]);

        // Create transaction if not exists
        $transaction = Transaction::where(['order_id' => $order->id])->first();
        if (!$transaction) {
            // Get branch and currency information
            $branch = $order->branch;
            $baseCurrency = $branch->currency ?? null;
            $baseCurrencyCode = $baseCurrency?->code ?? 'USD';
            $baseCurrencyId = $baseCurrency?->id;
            $receivePaymentCurrency = $order->receive_payment_currency ?? $baseCurrencyCode;
            $receivePaymentCurrencyId = $order->receive_payment_currency_id ?? $baseCurrencyId;
            $orderTotal = $order->total;
            
            // Determine transaction amount (actual payment amount in payment currency)
            if ($order->pos_received_amount !== null) {
                // Use actual received amount for POS payments
                $transactionAmount = $order->pos_received_amount;
            } elseif ($receivePaymentCurrency !== $baseCurrencyCode) {
                // Convert order total to payment currency for gateway payments
                $exchangeRateLookup = \App\Models\ExchangeRate::where('base_currency', $baseCurrencyCode)
                    ->where('target_currency', $receivePaymentCurrency)
                    ->first();
                if ($exchangeRateLookup && $exchangeRateLookup->rate) {
                    $transactionAmount = $orderTotal * floatval($exchangeRateLookup->rate);
                } else {
                    $transactionAmount = $orderTotal;
                }
            } else {
                // Same currency - transaction amount equals order total
                $transactionAmount = $orderTotal;
            }
            
            // Calculate exchange rate
            $amountBaseCurrency = $orderTotal;
            $exchangeRate = null;
            $exchangeRateBase = null;
            $exchangeRateTarget = null;
            
            if ($receivePaymentCurrency !== $baseCurrencyCode && $transactionAmount > 0) {
                $exchangeRate = $orderTotal / $transactionAmount;
                $exchangeRateBase = $receivePaymentCurrency;
                $exchangeRateTarget = $baseCurrencyCode;
            }
            
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'transaction_no' => $paymentOrder->transaction_no,
                'amount' => $orderTotal,
                'currency' => $baseCurrencyCode,
                'currency_id' => $baseCurrencyId,
                'amount_base_currency' => $amountBaseCurrency,
                'base_currency' => $baseCurrencyCode,
                'base_currency_id' => $baseCurrency?->id,
                'transaction_amount' => $transactionAmount,
                'transaction_currency' => $receivePaymentCurrency,
                'transaction_currency_id' => $receivePaymentCurrencyId,
                'change_amount' => 0,
                'change_currency' => null,
                'change_currency_id' => null,
                'exchange_rate' => $exchangeRate,
                'exchange_rate_base' => $exchangeRateBase,
                'exchange_rate_target' => $exchangeRateTarget,
                'payment_method' => 'Huione',
                'sign' => '+',
                'type' => 'payment'
            ]);
        }

        // Update order payment status
        $order->payment_status = PaymentStatus::PAID;
        $order->save();

        Log::info("Order marked as paid: " . $order->id);

        return [
            'status' => true,
            'message' => 'Payment completed',
            'paymentStatus' => 'DONE_PAYMENT',
            'paymentOrder' => $paymentOrder,
            'transaction' => $transaction
        ];
    }

    /**
     * Get valid payment order that hasn't expired
     *
     * @param Order $order
     * @return PaymentOrder|null
     */
    private function getValidPaymentOrder(Order $order): ?PaymentOrder
    {
        $paymentOrder = PaymentOrder::where(['order_id' => $order->id])->first();

        if ($paymentOrder &&
            $paymentOrder->expires_at->diffInMinutes(now()) < 0 &&
            $paymentOrder->status !== 'DONE_PAYMENT') {
            return $paymentOrder;
        }

        return null;
    }

    /**
     * Create or update payment order
     *
     * @param Order $order
     * @param string $outTradeNo
     * @param float $totalPrice
     * @param string $currency
     * @param string $jsonString
     * @param array $data
     * @return PaymentOrder
     */
    private function createOrUpdatePaymentOrder(
        Order $order,
        string $outTradeNo,
        float $totalPrice,
        string $currency,
        string $jsonString,
        array $data
    ): PaymentOrder {
        $paymentOrder = PaymentOrder::where(['order_id' => $order->id])->first();

        if (!$paymentOrder) {
            $paymentOrder = PaymentOrder::create([
                'order_id' => $order->id,
                'transaction_no' => $outTradeNo,
                'amount' => $totalPrice,
                'currency' => $currency,
                'payment_gateway' => 'Huione',
                'status' => PaymentStatus::UNPAID,
                'last_placed_at' => now(),
                'expires_at' => now()->addMinutes(5),
                'payment_requests' => [
                    $outTradeNo => [
                        'request' => $jsonString,
                        'response' => $data,
                    ]
                ],
                'qr_code_url' => $data['qrCode'],
            ]);
        } else {
            $paymentOrder->update([
                'transaction_no' => $outTradeNo,
                'amount' => $totalPrice,
                'currency' => $currency,
                'payment_gateway' => 'Huione',
                'status' => PaymentStatus::UNPAID,
                'last_placed_at' => now(),
                'expires_at' => now()->addMinutes(5),
                'payment_requests' => [
                    ...$paymentOrder->payment_requests,
                    $outTradeNo => [
                        'request' => $jsonString,
                        'response' => $data,
                    ]
                ],
                'qr_code_url' => $data['qrCode'],
            ]);
        }

        $order->save();

        return $paymentOrder;
    }

    /**
     * Generate unique transaction number
     *
     * @return string
     */
    private function generateTransactionNumber(): string
    {
        return 'ZXCHM' . date('ymdHisv') . mt_rand(100, 999);
    }

    /**
     * Get payment gateway configuration
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        return [
            'appId' => $this->appId,
            'isLive' => $this->isLive,
            'gateway' => $this->paymentGateway,
            'options' => $this->paymentGatewayOption
        ];
    }

    /**
     * Validate payment gateway configuration
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->appId) && !empty($this->privateKey);
    }

    /**
     * Handle Huione payment callback
     *
     * @param array $callbackData
     * @return array
     * @throws Exception
     */
    public function handleCallback(array $callbackData): array
    {
        Log::info('Processing Huione payment callback', [
            'outTradeNo' => $callbackData['outTradeNo'],
            'status' => $callbackData['status'],
            'transactionId' => $callbackData['transactionId']
        ]);

        // Validate callback signature if needed
        if (!$this->validateCallbackSignature($callbackData)) {
            throw new Exception('Invalid callback signature');
        }

        // Find payment order by transaction number
        $paymentOrder = PaymentOrder::where('transaction_no', $callbackData['outTradeNo'])->first();

        if (!$paymentOrder) {
            Log::warning('Payment order not found for callback', [
                'outTradeNo' => $callbackData['outTradeNo']
            ]);
            throw new Exception('Payment order not found: ' . $callbackData['outTradeNo']);
        }

        // Get the related order
        $order = Order::find($paymentOrder->order_id);
        if (!$order) {
            Log::error('Order not found for payment order', [
                'paymentOrderId' => $paymentOrder->id,
                'orderId' => $paymentOrder->order_id
            ]);
            throw new Exception('Order not found');
        }

        // Update payment order based on callback status
        $status = $this->mapCallbackStatusToPaymentStatus($callbackData['status']);

        // Update payment order
        $paymentOrder->update([
            'status' => $status,
            'gateway_response' => array_merge(
                $paymentOrder->gateway_response ?? [],
                ['callback_' . time() => $callbackData]
            )
        ]);

        // Handle successful payment
        if ($status === PaymentStatus::PAID) {
            $paymentOrder->update([
                'paid_at' => now()
            ]);

            // Process successful payment (update order status, create transaction, etc.)
            return $this->processSuccessfulPayment($order, $paymentOrder, $callbackData);
        }

        // Handle failed payment
        if ($status === PaymentStatus::UNPAID) {
            Log::info('Payment failed or cancelled', [
                'outTradeNo' => $callbackData['outTradeNo'],
                'status' => $callbackData['status']
            ]);

            return [
                'status' => true,
                'message' => 'Payment callback processed - payment failed or cancelled',
                'payment_status' => $status,
                'order_id' => $order->id
            ];
        }

        return [
            'status' => true,
            'message' => 'Payment callback processed',
            'payment_status' => $status,
            'order_id' => $order->id
        ];
    }

    /**
     * Map Huione callback status to internal payment status
     *
     * @param string $callbackStatus
     * @return int
     */
    private function mapCallbackStatusToPaymentStatus(string $callbackStatus): int
    {
        return match ($callbackStatus) {
            'DONE_PAYMENT' => PaymentStatus::PAID,
            'CANCEL_PAYMENT', 'CANCELLED', 'FAILED_PAYMENT', 'FAILED', 'PENDING_PAYMENT', 'PENDING', 'FAIL_PAYMENT' => PaymentStatus::UNPAID,
            default => PaymentStatus::UNPAID
        };
    }

    /**
     * Validate callback signature
     *
     * @param array $callbackData
     * @return bool
     */
    private function validateCallbackSignature(array $callbackData): bool
    {
        // If no secret key is configured, skip signature validation
        if (empty($this->secretKey)) {
            Log::warning('Huione callback signature validation skipped - no secret key configured');
            return true;
        }

        // Extract signature from callback data
        $receivedSign = $callbackData['sign'] ?? '';
        if (empty($receivedSign)) {
            Log::error('No signature found in callback data');
            return false;
        }

        try {
            // Create expected signature
            $signData = [
                'appId' => $callbackData['appId'],
                'merchantId' => $callbackData['merchantId'],
                'outTradeNo' => $callbackData['outTradeNo'],
                'status' => $callbackData['status'],
                'transactionId' => $callbackData['transactionId'],
                'nonce' => $callbackData['nonce'],
                'timestamp' => $callbackData['timestamp']
            ];

            // Generate expected signature using SignUtil
            $signUtil = new SignUtil();
            $expectedSign = $signUtil->sign($signData, $this->privateKey);

            $isValid = hash_equals($expectedSign, $receivedSign);

            if (!$isValid) {
                Log::error('Callback signature validation failed', [
                    'expected' => $expectedSign,
                    'received' => $receivedSign,
                    'signData' => $signData
                ]);
            }

            return $isValid;

        } catch (Exception $e) {
            Log::error('Error validating callback signature: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check payment order status for multiple transactions within a time period
     *
     * @param int $timeToCheck Number of seconds before now to check (default: 120 seconds)
     * @param int|null $branchId Filter by specific branch ID (null for all branches)
     * @return array
     * @throws Exception
     */
    public function checkPaymentOrderStatus(int $timeToCheck = 120, ?int $branchId = null): array
    {
        Log::info("Checking payment order status for last {$timeToCheck} seconds" . ($branchId ? " for branch {$branchId}" : " for all branches"));

        // Calculate the time threshold
        $timeThreshold = now()->subSeconds($timeToCheck);

        // Get payment orders from the specified time period that are not yet paid
        $query = PaymentOrder::where('created_at', '>=', $timeThreshold)
            ->where('status', '!=', PaymentStatus::PAID)
            ->where('payment_gateway', 'huione');

        // Add branch filtering if specified
        if ($branchId !== null) {
            $query->whereHas('order', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $paymentOrders = $query->get();

        if ($paymentOrders->isEmpty()) {
            Log::info("No unpaid payment orders found in the last {$timeToCheck} seconds");
            return [
                'status' => true,
                'message' => 'No unpaid payment orders found',
                'checked_count' => 0,
                'results' => []
            ];
        }

        Log::info("Found " . $paymentOrders->count() . " unpaid payment orders to check");

        // Extract transaction numbers for batch checking
        $transactionNumbers = $paymentOrders->pluck('transaction_no')->toArray();

        // Create order info request for multiple transactions
        $orderInfoReq = new OrderInfoReq(
            nonce: NonceUtil::getNonce(),
            outTradeNoList: $transactionNumbers,
            timestamp: round(microtime(true) * 1000),
        );

        $paramArray = $orderInfoReq->toArray();
        $sign = (new SignUtil)->sign($paramArray, AppConfigEnum::$privateKey);
        $paramArray['sign'] = $sign;

        $jsonString = json_encode($paramArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Log::info("Batch payment status check request: $jsonString");

        try {
            // Send query request
            $responseBody = PostUtil::sendPostRequestTest(
                PathEnum::$queryOrder,
                $jsonString,
                AppConfigEnum::$appId
            );

            Log::info("Batch payment status response: $responseBody");

            $data = json_decode($responseBody, true)['data'] ?? null;

            if (!$data) {
                Log::error("data字段不存在 in batch payment status check");
                throw new Exception('data字段不存在');
            }

            $results = [];
            $updatedCount = 0;

            // Process each transaction in the response
            foreach ($data as $index => $item) {
                $transactionNo = $item['outTradeNo'] ?? null;
                $status = $item['status'] ?? null;

                Log::info("Processing transaction #{$index}: {$transactionNo} with status: {$status}");

                if ($transactionNo && $status) {
                    // Find the corresponding payment order
                    $paymentOrder = $paymentOrders->where('transaction_no', $transactionNo)->first();

                    if ($paymentOrder) {
                        $results[] = [
                            'transaction_no' => $transactionNo,
                            'payment_order_id' => $paymentOrder->id,
                            'order_id' => $paymentOrder->order_id,
                            'status' => $status,
                            'previous_status' => $paymentOrder->status
                        ];

                        // If payment is completed, update the payment order and order
                        if ($status === 'DONE_PAYMENT' && $paymentOrder->status != PaymentStatus::PAID) {
                            try {
                                // Find the associated order
                                $order = Order::find($paymentOrder->order_id);

                                if ($order) {
                                    // Process the successful payment
                                    $this->processSuccessfulPayment($order, $paymentOrder, $item);
                                    $updatedCount++;

                                    $results[count($results) - 1]['updated'] = true;
                                    $results[count($results) - 1]['message'] = 'Payment processed successfully';

                                    Log::info("Successfully processed payment for transaction: {$transactionNo}");
                                } else {
                                    Log::error("Order not found for payment order: {$paymentOrder->id}");
                                    $results[count($results) - 1]['updated'] = false;
                                    $results[count($results) - 1]['message'] = 'Associated order not found';
                                }
                            } catch (Exception $e) {
                                Log::error("Error processing payment for transaction {$transactionNo}: " . $e->getMessage());
                                $results[count($results) - 1]['updated'] = false;
                                $results[count($results) - 1]['message'] = 'Error processing payment: ' . $e->getMessage();
                            }
                        } else {
                            $results[count($results) - 1]['updated'] = false;
                            $results[count($results) - 1]['message'] = 'Payment not completed or already processed';
                        }
                    } else {
                        Log::warning("Payment order not found for transaction: {$transactionNo}");
                        $results[] = [
                            'transaction_no' => $transactionNo,
                            'status' => $status,
                            'updated' => false,
                            'message' => 'Payment order not found in local database'
                        ];
                    }
                }
            }

            Log::info("Batch payment status check completed. Checked: " . count($results) . ", Updated: {$updatedCount}");

            return [
                'status' => true,
                'message' => 'Batch payment status check completed',
                'time_period_seconds' => $timeToCheck,
                'checked_count' => count($results),
                'updated_count' => $updatedCount,
                'results' => $results
            ];

        } catch (Exception $e) {
            Log::error("Batch payment status check failed: " . $e->getMessage());
            throw new Exception('Batch payment status check failed: ' . $e->getMessage());
        }
    }

    /**
     * Scheduled payment status check with branch scope and locking mechanism
     * This method is designed to be run by schedulers across multiple branches safely
     *
     * @param int $timeToCheck Number of seconds before now to check (default: 120 seconds)
     * @param int|null $branchId Current branch ID (required for multi-branch setup)
     * @return array
     * @throws Exception
     */
    public function scheduledPaymentStatusCheck(int $timeToCheck = 120, ?int $branchId = null): array
    {
        $lockName = 'huione_payment_check_' . ($branchId ?? 'all');
        $lockTimeout = 50; // 50 seconds timeout - less than 60 second schedule interval

        Log::info("Starting scheduled payment status check with lock: {$lockName}");

        // Use Laravel's cache-based locking to prevent concurrent execution
        $lock = cache()->lock($lockName, $lockTimeout);

        if (!$lock->get()) {
            Log::info("Another instance is already running payment status check for lock: {$lockName}");
            return [
                'status' => false,
                'message' => 'Another payment status check is already running',
                'lock_name' => $lockName
            ];
        }

        try {
            Log::info("Acquired lock {$lockName}, proceeding with payment status check");

            // Add a small delay to prevent API hammering when multiple branches start simultaneously
            if ($branchId !== null) {
                $delay = ($branchId % 5) * 2; // Stagger requests by 0-8 seconds based on branch ID
                if ($delay > 0) {
                    Log::info("Delaying execution by {$delay} seconds for branch {$branchId}");
                    sleep($delay);
                }
            }

            // Call the main check method with branch filtering
            $result = $this->checkPaymentOrderStatus($timeToCheck, $branchId);

            Log::info("Scheduled payment status check completed successfully for lock: {$lockName}");

            return array_merge($result, [
                'scheduled_execution' => true,
                'branch_id' => $branchId,
                'lock_name' => $lockName
            ]);

        } catch (Exception $e) {
            Log::error("Scheduled payment status check failed for lock {$lockName}: " . $e->getMessage());
            throw $e;
        } finally {
            // Always release the lock
            $lock->release();
            Log::info("Released lock: {$lockName}");
        }
    }
}
