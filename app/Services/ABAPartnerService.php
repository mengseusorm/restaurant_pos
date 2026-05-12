<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\GatewayOption;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ABAPartnerService
{
    protected string $partnerId;
    protected string $partnerKey;
    protected string $publicKey;
    protected string $privateKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->partnerId  = config('payway.partner_id', '');
        $this->partnerKey = config('payway.partner_key', '');
        $this->publicKey  = $this->loadKey(config('payway.partner_public_key', ''));
        $this->privateKey = $this->loadKey(config('payway.partner_private_key', ''));
        // $this->baseUrl    = config('payway.partner_base_url', 'https://merchant.payway.com.kh');
        // $this->baseUrl    = config('payway.partner_base_url', 'https://sandbox.payway.com.kh');
        $this->baseUrl    = 'https://sandbox.payway.com.kh';
    }

    /**
     * Load an RSA key: if the value is a relative file path, read the file;
     * otherwise treat it as an inline value with literal \\n escape sequences.
     */
    private function loadKey(string $value): string
    {
        if (empty($value)) {
            return '';
        }
        $path = base_path($value);
        if (!str_contains($value, '-----') && file_exists($path)) {
            return file_get_contents($path);
        }
        return str_replace('\n', "\n", $value);
    }

    /**
     * Get current request time in ABA format (UTC): YYYYMMDDHHmmss
     */
    private function getRequestTime(): string
    {
        return gmdate('YmdHis');
    }

    /**
     * RSA-encrypt $source using the ABA partner public key in 117-byte chunks,
     * then Base64-encode the concatenated ciphertext.
     */
    private function rsaEncrypt(string $source): string
    {
        $maxLength = 117;
        $output    = '';

        while ($source !== '') {
            $chunk  = substr($source, 0, $maxLength);
            $source = substr($source, $maxLength);
            $ok     = openssl_public_encrypt($chunk, $encrypted, $this->publicKey);
            if (!$ok) {
                throw new Exception('RSA public key encryption failed: ' . openssl_error_string());
            }
            $output .= $encrypted;
        }

        return base64_encode($output);
    }

    /**
     * RSA-decrypt an encrypted Base64 string using the ABA partner private key.
     * Used to decrypt the inquiry response `data` field.
     */
    public function rsaDecrypt(string $encryptedBase64): string
    {
        $encrypted = base64_decode($encryptedBase64);
        $chunkSize = 128; // 1024-bit key → 128 bytes per block
        $output    = '';

        while ($encrypted !== '') {
            $chunk     = substr($encrypted, 0, $chunkSize);
            $encrypted = substr($encrypted, $chunkSize);
            $ok        = openssl_private_decrypt($chunk, $decrypted, $this->privateKey);
            if (!$ok) {
                throw new Exception('RSA private key decryption failed: ' . openssl_error_string());
            }
            $output .= $decrypted;
        }

        return $output;
    }

    /**
     * Generate HMAC-SHA256 hash over (partner_id . request_data . request_time).
     */
    private function generateHash(string $partnerId, string $requestData, string $requestTime): string
    {
        $payload = $partnerId . $requestData . $requestTime;
        return hash_hmac('sha256', $payload, $this->partnerKey);
    }

    /**
     * Register a new merchant via ABA Partner self-activation API.
     *
     * @param  string  $currency     'KHR' or 'USD'
     * @param  string  $registerRef  Unique reference for this request
     * @param  string  $pushbackUrl  URL ABA will POST merchant credentials to after registration
     * @param  string  $redirectUrl  URL shown on ABA success screen for user to return to
     * @param  int     $merchantType 0 = instore, 1 = online (default)
     * @return array   ['url' => ..., 'token' => ..., 'status' => [...]]
     */
    public function registerMerchant(
        string $currency,
        string $registerRef,
        string $pushbackUrl,
        string $redirectUrl,
        int    $merchantType = 1
    ): array {
        $requestTime = $this->getRequestTime();

        $data = [
            'pushback_url'  => $pushbackUrl,
            'redirect_url'  => $redirectUrl,
            'type'          => 0,
            'register_ref'  => $registerRef,
            'merchant_type' => $merchantType,
            'currency'      => strtoupper($currency),
        ];

        $requestData = $this->rsaEncrypt(json_encode($data));
        $hash        = $this->generateHash($this->partnerId, $requestData, $requestTime);

        $payload = [
            'request_time' => $requestTime,
            'partner_id'   => $this->partnerId,
            'request_data' => $requestData,
            'reference_id' => $registerRef,
            'hash'         => $hash,
        ];

        $referer = config('app.url');

        Log::info('ABA Partner: registerMerchant request', [
            'referer'      => $referer,
            'register_ref' => $registerRef,
            'currency'     => $currency,
            'request_time' => $requestTime,
            'url'          => "{$this->baseUrl}/api/merchant-portal/online-self-activation/new-merchant",
        ]);

        Log::info('ABA Partner: registerMerchant request', $payload);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'referer'      => $referer,
        ])->post("{$this->baseUrl}/api/merchant-portal/online-self-activation/new-merchant", $payload);

        $result = $response->json();

        Log::info('ABA Partner: registerMerchant response', [
            'status' => $result['status'] ?? null,
            'has_url' => isset($result['url']),
        ]);
        Log::info('ABA Partner: registerMerchant response', $result);

        return $result;
    }

    /**
     * Inquiry merchant credential info from ABA Partner API.
     *
     * @param  string $registerRef  The register_ref used during registration
     * @return array  ['data' => <encrypted string>, 'status' => [...]]
     */
    public function inquiryMerchantInfo(string $registerRef): array
    {
        $requestTime = $this->getRequestTime();

        $data        = ['register_ref' => $registerRef];
        $requestData = $this->rsaEncrypt(json_encode($data));
        $hash        = $this->generateHash($this->partnerId, $requestData, $requestTime);

        $payload = [
            'request_time' => $requestTime,
            'partner_id'   => $this->partnerId,
            'request_data' => $requestData,
            'hash'         => $hash,
        ];

        $referer = config('app.url');

        Log::info('ABA Partner: inquiryMerchantInfo request', [
            'referer'      => $referer,
            'register_ref' => $registerRef,
            'request_time' => $requestTime,
            'url'          => "{$this->baseUrl}/api/merchant-portal/online-self-activation/get-mc-credential-info",
            'partner_id'   => $this->partnerId,
        ]);

        Log::info('ABA Partner: inquiryMerchantInfo request', $payload);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'referer'      => $referer,
        ])->post("{$this->baseUrl}/api/merchant-portal/online-self-activation/get-mc-credential-info", $payload);

        // https://sandbox.payway.com.kh/api/merchant-portal/online-self-activation/get-mc-credential-info

        $result = $response->json();

        Log::info('ABA Partner: inquiryMerchantInfo response', [
            'status'   => $result['status'] ?? null,
            'has_data' => isset($result['data']),
        ]);

        Log::info('ABA Partner: inquiryMerchantInfo response', $result);

        // Decrypt the returned merchant data if status is success
        if (isset($result['status']['code']) && $result['status']['code'] === '00' && !empty($result['data'])) {
            try {
                $decrypted        = $this->rsaDecrypt($result['data']);
                $result['merchant'] = json_decode($decrypted, true);
            } catch (Exception $e) {
                Log::warning('ABA Partner: could not decrypt merchant data', ['error' => $e->getMessage()]);
            }
        }

        return $result;
    }
}
