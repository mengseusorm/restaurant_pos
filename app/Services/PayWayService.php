<?php

namespace App\Services;

use App\Enums\Activity;
use App\Models\PaymentGateway;
use App\Models\GatewayOption;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PayWayService
{
    protected $gateway;
    protected $merchantId;
    protected $apiKey;
    protected $baseUrl;
    protected $mode;
    protected $config;

    public function __construct()
    {
        // Get PayWay gateway from database
        // $this->gateway = PaymentGateway::where('slug', 'payway')
        //     ->where('status', 1)
        //     ->first();

        $this->gateway = PaymentGateway::where(['slug' => 'abapayway', 'status' => Activity::ENABLE])->first();  
        
        if (!$this->gateway) {
            throw new Exception('PayWay gateway not found or not active');
        }
        
        // Load configuration from gateway options
        $this->config = $this->getGatewayConfig();
        
        $this->merchantId = $this->config['merchant_id'] ?? null;
        $this->apiKey = $this->config['api_key'] ?? null;
        $this->mode = $this->config['mode'] ?? 'sandbox';
        
        // Set base URL based on mode
        $this->baseUrl = $this->mode === 'production'
            ? 'https://checkout.payway.com.kh'
            : 'https://checkout-sandbox.payway.com.kh';
        
        if (!$this->merchantId || !$this->apiKey) {
            throw new Exception('PayWay merchant_id and api_key are required');
        }
    }

    /**
     * Get PayWay configuration from gateway options
     *
     * @return array
     */
    protected function getGatewayConfig(): array
    {
        $options = $this->gateway->gatewayOptions->pluck('value', 'option')->toArray();
        
        return [
            'merchant_id' => $options['merchant_id'] ?? null,
            'api_key' => $options['api_key'] ?? null,
            'rsa_public_key' => $options['rsa_public_key'] ?? null,
            'mode' => $options['mode'] ?? 'sandbox',
            'callback_url' => $options['callback_url'] ?? route('payway.callback', [], true),
            'continue_success_url' => $options['continue_success_url'] ?? url('/payment/success'),
            'return_url' => $options['return_url'] ?? url('/payment/return'),
        ];
    }

    /**
     * Check if PayWay is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->apiKey);
    }

    /**
     * Generate hash for PayWay API using HMAC SHA-512
     * 
     * @param string $dataString
     * @return string
     */
    private function generateHash(string $dataString): string
    {
        return base64_encode(hash_hmac('sha512', $dataString, $this->apiKey, true));
    }

    /**
     * Generate current request time in PayWay format (UTC)
     * 
     * @return string YYYYMMDDHHmmss format
     */
    private function getReqTime(): string
    {
        return gmdate('YmdHis');
    }

    /**
     * Generate QR Code for KHQR payment
     *
     * @param array $data
     * @return array
     */
    public function generateQR(array $data): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'PayWay is not configured properly'
            ];
        }

        $reqTime = $this->getReqTime();
        $tranId = $data['tran_id'] ?? 'TRX' . time() . rand(1000, 9999);
        
        // Prepare items (base64 encoded JSON)
        $items = '';
        if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
            $items = base64_encode(json_encode($data['items']));
        }
        
        // Prepare callback URL (base64 encoded)
        $callbackUrl = base64_encode($this->config['callback_url']);

        // Prepare return_deeplink: ABA expects base64(JSON{"android_scheme":"...","ios_scheme":"..."})
        $returnDeeplinkRaw = $data['return_deeplink'] ?? '';
        $returnDeeplink = $returnDeeplinkRaw !== ''
            ? base64_encode(json_encode([
                'android_scheme' => $returnDeeplinkRaw,
                'ios_scheme'     => $returnDeeplinkRaw,
            ]))
            : '';

        // Build hash string according to PayWay KHQR documentation
        // Order is critical: req_time + merchant_id + tran_id + amount + items + 
        // firstname + lastname + email + phone + type + payment_option + callback_url + 
        // return_deeplink + currency + shipping + custom_fields + return_params + 
        // payout + lifetime + qr_image_template
        $hashString = $reqTime . 
                      $this->merchantId . 
                      $tranId . 
                      $data['amount'] . 
                      $items . 
                    //   ($data['first_name'] ?? '') . 
                    //   ($data['last_name'] ?? '') . 
                    //   ($data['email'] ?? '') . 
                    //   ($data['phone'] ?? '') . 
                      ($data['purchase_type'] ?? 'purchase') . 
                      ($data['payment_option'] ?? 'abapay_khqr') . 
                      $callbackUrl . 
                      $returnDeeplink . // return_deeplink (base64-encoded, or empty)
                      ($data['currency'] ?? 'USD') . 
                      '' . // shipping (empty)
                      '' . // custom_fields (empty)
                      '' . // return_params (empty)
                      '' . // payout (empty)
                      ($data['lifetime'] ?? 3) . 
                      ($data['qr_image_template'] ?? 'template3_color');
        
        $hash = $this->generateHash($hashString);
        
        $payload = [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'USD',
            'payment_option' => $data['payment_option'] ?? 'abapay_khqr',
            'items' => $items,
            // 'first_name' => $data['first_name'] ?? '',
            // 'last_name' => $data['last_name'] ?? '',
            // 'email' => $data['email'] ?? '',
            // 'phone' => $data['phone'] ?? '',
            'purchase_type' => $data['purchase_type'] ?? 'purchase',
            'callback_url' => $callbackUrl,
            'return_deeplink' => $returnDeeplink, // base64-encoded deeplink, or empty string
            'lifetime' => $data['lifetime'] ?? 30, // 30 minutes default
            'qr_image_template' => $data['qr_image_template'] ?? 'template3_color',
            'hash' => $hash
        ];


        try {
            Log::info('[PayWay KHQR] Generate QR Request', [
                'tran_id' => $tranId,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'USD',
                'mode' => $this->mode,
                'callback_url' => $this->config['callback_url'],
                'callback_url_encoded' => $callbackUrl,
                'return_deeplink_raw' => $returnDeeplinkRaw,
                'return_deeplink_encoded' => $returnDeeplink,
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/api/payment-gateway/v1/payments/generate-qr', $payload);

            $result = $response->json();
            
            Log::info('[PayWay KHQR] Generate QR Response', [
                'tran_id' => $tranId,
                'status_code' => $result['status']['code'] ?? 'unknown',
                'status_message' => $result['status']['message'] ?? 'unknown',
                'has_qr' => isset($result['qrImage'])
            ]);
            
            if ($response->successful() && isset($result['status']['code']) && $result['status']['code'] == '0') {
                return [
                    'success' => true,
                    'data' => [
                        'tran_id' => $tranId,
                        'qrString' => $result['qrString'] ?? '',
                        'qrImage' => $result['qrImage'] ?? '',
                        'abapay_deeplink' => $result['abapay_deeplink'] ?? '',
                        'app_store' => $result['app_store'] ?? '',
                        'play_store' => $result['play_store'] ?? '',
                        'amount' => $data['amount'],
                        'currency' => $data['currency'] ?? 'USD',
                        'lifetime' => (int)($data['lifetime'] ?? 3), // QR lifetime in minutes
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['status']['message'] ?? 'Failed to generate QR code',
                    'error_code' => $result['status']['code'] ?? 'unknown'
                ];
            }
        } catch (Exception $e) {
            Log::error('[PayWay KHQR] Generate QR Exception', [
                'tran_id' => $tranId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to connect to PayWay: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check transaction status
     *
     * @param string $tranId
     * @return array
     */
    public function checkTransaction(string $tranId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'PayWay is not configured properly'
            ];
        }

        $reqTime = $this->getReqTime();
        
        // Build hash string for check transaction
        // Order: req_time + merchant_id + tran_id
        $hashString = $reqTime . $this->merchantId . $tranId;
        $hash = $this->generateHash($hashString);
        
        $payload = [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'hash' => $hash
        ];

        try {
            Log::info('[PayWay KHQR] Check Transaction Request', [
                'tran_id' => $tranId,
                'mode' => $this->mode
            ]);

            // $response = Http::withHeaders([
            //     'Content-Type' => 'application/json',
            // ])->timeout(30)->post($this->baseUrl . '/api/payment-gateway/v1/payments/check-transaction', $payload);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/api/payment-gateway/v1/payments/check-transaction-2', $payload);

            $result = $response->json();
            
            Log::info('[PayWay KHQR] Check Transaction Response', [
                'tran_id' => $tranId,
                'status_code' => $result['status']['code'] ?? 'unknown',
                'payment_status' => $result['data']['payment_status'] ?? 'unknown',
                'payment_status_code' => $result['data']['payment_status_code'] ?? 'unknown'
            ]);
            
            if ($response->successful() && isset($result['status']['code']) && $result['status']['code'] == '00') {
                return [
                    'success' => true,
                    'data' => $result['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['status']['message'] ?? 'Failed to check transaction',
                    'error_code' => $result['status']['code'] ?? 'unknown'
                ];
            }
        } catch (Exception $e) {
            Log::error('[PayWay KHQR] Check Transaction Exception', [
                'tran_id' => $tranId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to connect to PayWay: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Close/Cancel transaction
     *
     * @param string $tranId
     * @return array
     */
    public function closeTransaction(string $tranId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'PayWay is not configured properly'
            ];
        }

        $reqTime = $this->getReqTime();
        
        // Build hash string for close transaction
        // Order: req_time + merchant_id + tran_id
        $hashString = $reqTime . $this->merchantId . $tranId;
        $hash = $this->generateHash($hashString);
        
        $payload = [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'hash' => $hash
        ];

        try {
            Log::info('[PayWay KHQR] Close Transaction Request', [
                'tran_id' => $tranId,
                'mode' => $this->mode
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/api/payment-gateway/v1/payments/close-transaction', $payload);

            $result = $response->json();
            
            Log::info('[PayWay KHQR] Close Transaction Response', [
                'tran_id' => $tranId,
                'status_code' => $result['status']['code'] ?? 'unknown',
                'status_message' => $result['status']['message'] ?? 'unknown'
            ]);
            
            if ($response->successful() && isset($result['status']['code']) && $result['status']['code'] == '0') {
                return [
                    'success' => true,
                    'message' => 'Transaction closed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['status']['message'] ?? 'Failed to close transaction',
                    'error_code' => $result['status']['code'] ?? 'unknown'
                ];
            }
        } catch (Exception $e) {
            Log::error('[PayWay KHQR] Close Transaction Exception', [
                'tran_id' => $tranId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to connect to PayWay: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process refund for a transaction
     * 
     * @param string $tranId Transaction ID to refund
     * @param float $refundAmount Amount to refund
     * @return array Response from PayWay API
     */
    public function refundTransaction(string $tranId, float $refundAmount): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'PayWay is not configured properly'
            ];
        }

        try {
            $reqTime = $this->getReqTime();
            
            // Get RSA public key from config (this should be stored in gateway options)
            $rsaPublicKey = $this->config['rsa_public_key'] ?? null;

            /**
            *    MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDJE4seQhT+GVtJXL59HneeRWZL
            *    zp5ORmX9qnPNVAqsGnpw3v5MwYEO7ehXKNI6zX3PYjDazX8iTM9gFH3HbpPXjq+f
            *    1rfAxuTD0F32INGuW4oUB/bw0OuU/KHL3QkWwFCbkoGaNOSulZBSQXVbwmcVeivw
            *    qnUge/KmAMkphvfeQwIDAQAB
             */

               
            // $rsaPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDJE4seQhT+GVtJXL59HneeRWZLzp5ORmX9qnPNVAqsGnpw3v5MwYEO7ehXKNI6zX3PYjDazX8iTM9gFH3HbpPXjq+f1rfAxuTD0F32INGuW4oUB/bw0OuU/KHL3QkWwFCbkoGaNOSulZBSQXVbwmcVeivwqnUge/KmAMkphvfeQwIDAQAB';
            
            if (!$rsaPublicKey) {
                return [
                    'success' => false,
                    'message' => 'RSA public key is not configured. Please add rsa_public_key to PayWay gateway options.'
                ];
            }

            // Format RSA public key if needed
            if (strpos($rsaPublicKey, '-----BEGIN PUBLIC KEY-----') === false) {
                $rsaPublicKey = "-----BEGIN PUBLIC KEY-----\n" . 
                               chunk_split($rsaPublicKey, 64, "\n") . 
                               "-----END PUBLIC KEY-----";
            }

            // Prepare data to be encrypted
            $dataObject = json_encode([
                'mc_id' => $this->merchantId,
                'tran_id' => $tranId,
                'refund_amount' => number_format($refundAmount, 2, '.', '')
            ]);

            // Encrypt data in chunks using RSA public key
            $maxLength = 117; // Maximum chunk size for RSA encryption
            $encryptedOutput = '';
            
            while ($dataObject !== '') {
                $chunk = substr($dataObject, 0, $maxLength);
                $dataObject = substr($dataObject, $maxLength);
                
                $encryptedChunk = '';
                if (openssl_public_encrypt($chunk, $encryptedChunk, $rsaPublicKey)) {
                    $encryptedOutput .= $encryptedChunk;
                } else {
                    throw new Exception('Encryption failed for a data chunk');
                }
            }
            
            // Base64 encode the encrypted output
            $merchantAuth = base64_encode($encryptedOutput);
            
            // Generate hash: request_time + merchant_id + merchant_auth
            $hashData = $reqTime . $this->merchantId . $merchantAuth;
            $hash = $this->generateHash($hashData);

            $payload = [
                'request_time' => $reqTime,
                'merchant_id' => $this->merchantId,
                'merchant_auth' => $merchantAuth,
                'hash' => $hash
            ];

            Log::info('[PayWay Refund] Request', [
                'transaction_id' => $tranId,
                'refund_amount' => $refundAmount,
                'merchant_id' => $this->merchantId,
                'mode' => $this->mode
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/api/merchant-portal/merchant-access/online-transaction/refund', $payload);

            $responseData = $response->json();
            
            Log::info('[PayWay Refund] Response', [
                'transaction_id' => $tranId,
                'status_code' => $responseData['status']['code'] ?? 'unknown',
                'status_message' => $responseData['status']['message'] ?? 'unknown',
                'grand_total' => $responseData['grand_total'] ?? null,
                'total_refunded' => $responseData['total_refunded'] ?? null
            ]);

            if ($response->successful() && isset($responseData['status']['code']) && $responseData['status']['code'] === '00') {
                return [
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'data' => $responseData
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['status']['message'] ?? 'Refund failed',
                'error_code' => $responseData['status']['code'] ?? 'UNKNOWN',
                'data' => $responseData
            ];

        } catch (Exception $e) {
            Log::error('[PayWay Refund] Exception', [
                'transaction_id' => $tranId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ];
        }
    }
}