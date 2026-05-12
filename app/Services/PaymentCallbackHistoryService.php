<?php

namespace App\Services;

use App\Models\PaymentCallbackHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentCallbackHistoryService
{
    /**
     * Log a payment callback request
     *
     * @param Request $request
     * @param string $paymentGateway
     * @param array $validatedData
     * @param bool $isValid
     * @param string|null $validationErrors
     * @return PaymentCallbackHistory
     */
    public function logCallback(
        Request $request,
        string $paymentGateway = 'huione',
        array $validatedData = [],
        bool $isValid = true,
        ?string $validationErrors = null
    ): PaymentCallbackHistory {
        try {
            $callbackHistory = PaymentCallbackHistory::create([
                'payment_gateway' => $paymentGateway,
                'out_trade_no' => $validatedData['outTradeNo'] ?? $request->input('outTradeNo'),
                'transaction_id' => $validatedData['transactionId'] ?? $request->input('transactionId'),
                'status' => $validatedData['status'] ?? $request->input('status'),
                'merchant_id' => $validatedData['merchantId'] ?? $request->input('merchantId'),
                'app_id' => $validatedData['appId'] ?? $request->input('appId'),
                'callback_url' => $request->fullUrl(),
                'request_headers' => $this->sanitizeHeaders($request->headers->all()),
                'request_data' => $request->all(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_valid' => $isValid,
                'validation_errors' => $validationErrors,
                'callback_received_at' => now(),
            ]);

            return $callbackHistory;
        } catch (Exception $e) {
            Log::error('Failed to log payment callback history', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }
    }

    /**
     * Update callback history with processing results
     *
     * @param PaymentCallbackHistory $callbackHistory
     * @param array $responseData
     * @param int $responseStatus
     * @param bool $isProcessed
     * @param string|null $processingErrors
     * @return PaymentCallbackHistory
     */
    public function updateProcessingResult(
        PaymentCallbackHistory $callbackHistory,
        array $responseData = [],
        int $responseStatus = 200,
        bool $isProcessed = true,
        ?string $processingErrors = null
    ): PaymentCallbackHistory {
        try {
            $callbackHistory->update([
                'response_data' => $responseData,
                'response_status' => $responseStatus,
                'is_processed' => $isProcessed,
                'processing_errors' => $processingErrors,
            ]);

            return $callbackHistory;
        } catch (Exception $e) {
            Log::error('Failed to update payment callback history', [
                'callback_id' => $callbackHistory->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get callback statistics for monitoring
     *
     * @param string $paymentGateway
     * @param int $hours
     * @return array
     */
    public function getCallbackStats(string $paymentGateway = 'huione', int $hours = 24): array
    {
        $since = now()->subHours($hours);

        $total = PaymentCallbackHistory::byGateway($paymentGateway)
            ->where('callback_received_at', '>=', $since)
            ->count();

        $successful = PaymentCallbackHistory::byGateway($paymentGateway)
            ->successful()
            ->where('callback_received_at', '>=', $since)
            ->count();

        $failed = PaymentCallbackHistory::byGateway($paymentGateway)
            ->failed()
            ->where('callback_received_at', '>=', $since)
            ->count();

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'period_hours' => $hours,
        ];
    }

    /**
     * Clean up old callback history records
     *
     * @param int $daysToKeep
     * @return int Number of deleted records
     */
    public function cleanupOldRecords(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return PaymentCallbackHistory::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Sanitize headers to remove sensitive information
     *
     * @param array $headers
     * @return array
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];
        
        $sanitized = [];
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
