<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GatewayOption;
use App\Models\PaymentGateway;
use App\Services\ABAPartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ABAPartnerController extends Controller
{
    protected ABAPartnerService $abaPartnerService;

    public function __construct(ABAPartnerService $abaPartnerService)
    {
        $this->abaPartnerService = $abaPartnerService;
    }

    /**
     * Initiate merchant self-activation via ABA Partner API.
     * Returns the ABA onboarding URL to redirect the user to.
     *
     * POST /admin/aba-partner/register-merchant
     */
    public function registerMerchant(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency'     => ['required', 'in:KHR,USD'],
            'register_ref' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Guard: ensure ABA Partner credentials are configured
        if (empty(config('payway.partner_id')) || empty(config('payway.partner_key')) || empty(config('payway.partner_public_key'))) {
            return response()->json([
                'message' => 'ABA Partner credentials are not configured. Please set ABA_PARTNER_ID, ABA_PARTNER_KEY, and ABA_PARTNER_PUBLIC_KEY in your environment.',
            ], 503);
        }

        try {
            $registerRef = $request->input('register_ref') ?: 'REG-' . strtoupper(uniqid());
            $pushbackUrl = route('aba-partner.pushback');
            $redirectUrl = rtrim(config('app.url'), '/') . '/admin/settings/aba-payment-info';

            $result = $this->abaPartnerService->registerMerchant(
                $request->input('currency'),
                $registerRef,
                $pushbackUrl,
                $redirectUrl
            );

            if (!isset($result['status']['code'])) {
                return response()->json([
                    'message' => 'Invalid response from ABA PayWay.',
                ], 502);
            }

            if ($result['status']['code'] !== '00') {
                return response()->json([
                    'message' => $result['status']['message'] ?? 'ABA PayWay registration failed.',
                    'code'    => $result['status']['code'],
                ], 422);
            }

            // Store the register_ref so we can look it up on pushback
            $this->storeGatewayOption('register_ref', $registerRef);

            // Force https:// to prevent mixed-content errors on HTTPS-hosted sites
            $registrationUrl = preg_replace('/^http:\/\//i', 'https://', $result['url']);

            return response()->json([
                'url'          => $registrationUrl,
                'token'        => $result['token'] ?? null,
                'register_ref' => $registerRef,
                'status'       => $result['status'],
            ]);
        } catch (Exception $e) {
            Log::error('ABAPartnerController::registerMerchant - ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to connect to ABA PayWay: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Inquiry merchant credential info from ABA Partner API.
     *
     * POST /admin/aba-partner/inquiry-merchant
     */
    public function inquiryMerchant(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'register_ref' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $result = $this->abaPartnerService->inquiryMerchantInfo($request->input('register_ref'));

            if (!isset($result['status']['code'])) {
                return response()->json([
                    'message' => 'Invalid response from ABA PayWay.',
                ], 502);
            }

            if ($result['status']['code'] !== '00') {
                return response()->json([
                    'message' => $result['status']['message'] ?? 'Merchant inquiry failed.',
                    'code'    => $result['status']['code'],
                ], 422);
            }

            // If decrypted merchant data is available, auto-save the credentials
            if (!empty($result['merchant'])) {
                $this->saveMerchantCredentials($result['merchant']);
            }

            return response()->json([
                'merchant' => $result['merchant'] ?? null,
                'status'   => $result['status'],
            ]);
        } catch (Exception $e) {
            Log::error('ABAPartnerController::inquiryMerchant - ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to inquiry merchant info: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save ABA credentials (merchant_id, api_key, rsa_public_key) manually.
     *
     * POST /admin/aba-partner/save-credentials
     */
    public function saveCredentials(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'merchant_id'    => ['required', 'string'],
            'api_key'        => ['required', 'string'],
            'rsa_public_key' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $credentials = [
                'merchant_id'    => $request->input('merchant_id'),
                'api_key'        => $request->input('api_key'),
                'rsa_public_key' => $request->input('rsa_public_key', ''),
            ];

            $this->saveMerchantCredentials($credentials);

            return response()->json([
                'message' => 'Credentials saved successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('ABAPartnerController::saveCredentials - ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save credentials: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ABA PayWay pushback endpoint – called by ABA after merchant completes registration.
     * Receives merchant credential data and saves it to the gateway options.
     *
     * POST /api/aba-partner/pushback  (public, no auth)
     */
    public function pushbackCallback(Request $request): JsonResponse
    {
        // ABA sends Content-Type: text/plain — Laravel won't auto-parse it,
        // so read raw body and decode manually.
        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);

        // Fall back to normal parsed input if body was already decoded
        if (!is_array($payload)) {
            $payload = $request->all();
        }

        Log::info('ABA Partner Pushback received', ['payload' => $payload]);

        try {
            // ABA sends encrypted merchant credentials in the `return_params` field
            $returnParams = $payload['return_params'] ?? null;

            if (empty($returnParams)) {
                Log::warning('ABA pushback: missing return_params field', ['payload' => $payload]);
                return response()->json(['status' => 'ok']);
            }

            $decrypted = $this->abaPartnerService->rsaDecrypt($returnParams);
            $merchant  = json_decode($decrypted, true);

            if (!is_array($merchant)) {
                Log::error('ABA pushback: failed to decode decrypted return_params', ['decrypted' => $decrypted]);
                return response()->json(['status' => 'error', 'message' => 'Invalid decrypted data'], 422);
            }

            Log::info('ABA Partner Pushback decrypted', ['merchant' => array_keys($merchant)]);
            Log::info('ABA Partner Pushback decrypted details', ['merchant' => $merchant]);

            $this->saveMerchantCredentials($merchant);

            return response()->json(['status' => 'ok']);
        } catch (Exception $e) {
            Log::error('ABAPartnerController::pushbackCallback - ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Save / update merchant credentials into the abapayway gateway options table.
     *
     * Confirmed ABA pushback fields (from actual payload):
     *   mid            – merchant ID (may be empty in sandbox)
     *   merchant_key   – API key / merchant key
     *   public_key     – short key fingerprint/hash
     *   rsa_public_key – full PEM RSA public key certificate
     *   register_ref   – registration reference
     *   currency       – account currency (KHR / USD)
     */
    private function saveMerchantCredentials(array $credentials): void
    {
        $gateway = PaymentGateway::where('slug', 'abapayway')->first();
        if (!$gateway) {
            Log::warning('ABA Partner: abapayway gateway not found in database');
            return;
        }

        // Confirmed ABA pushback fields from actual payload:
        //   public_key     = short hash/fingerprint (e.g. "0b93808e...")
        //   rsa_public_key = full PEM certificate  (e.g. "-----BEGIN PUBLIC KEY-----...")

        /**
         * 'merchant_id' => $options['merchant_id'] ?? null,
         * 'api_key' => $options['api_key'] ?? null,
         * 'rsa_public_key' => $options['rsa_public_key'] ?? null,
         */
        $optionMap = [
            'merchant_id'            => 'merchant_id',
            'mid'            => 'mid',
            'partner_name'   => 'partner_name',
            'merchant_name'     => 'merchant_name',
            // 'merchant_key'      => 'merchant_key',
            'merchant_key'      => 'merchant_id',
            'api_key'       => 'api_key',
            'public_key'       => 'api_key',
            'rsa_public_key' => 'rsa_public_key',
            'register_ref'   => 'register_ref',
            'currency'       => 'aba_currency',
        ];

        //Response from ABA may contain some or all of the above fields. Only save the ones that are present and non-empty.
        /***
        
        {
            "merchant": {
                "partner_name": "KBT Solutions",
                "merchant_name": "Testing Account",
                "mid": "",
                "merchant_key": "Sandbox3201",
                "public_key": "0b93808eaf4c1ff496bf2664885e6f7f8f021a6d",
                "register_ref": "REG-69B78B004DCAE",
                "currency": "KHR",
                "rsa_public_key": ""
            }
            }



         */

        foreach ($optionMap as $incomingKey => $optionName) {
            if (!isset($credentials[$incomingKey]) || $credentials[$incomingKey] === '') {
                continue;
            }

            $value = $credentials[$incomingKey];

            // For RSA public key: strip PEM headers/footers and whitespace, store raw base64 only
            if ($incomingKey === 'rsa_public_key') {
                $value = preg_replace('/-----(?:BEGIN|END)[^-]+-----/', '', $value);
                $value = preg_replace('/\s+/', '', $value);
            }

            GatewayOption::updateOrCreate(
                [
                    'model_type' => PaymentGateway::class,
                    'model_id'   => $gateway->id,
                    'option'     => $optionName,
                ],
                ['value' => $value, 'type' => 5]
            );
        }

        Log::info('ABA Partner credentials saved', ['gateway_id' => $gateway->id, 'keys' => array_keys($credentials)]);
    }

    /**
     * Store a single gateway option value by option name (creates or updates).
     */
    private function storeGatewayOption(string $optionName, string $value): void
    {
        $gateway = PaymentGateway::where('slug', 'abapayway')->first();
        if (!$gateway) {
            return;
        }

        GatewayOption::updateOrCreate(
            [
                'model_type' => PaymentGateway::class,
                'model_id'   => $gateway->id,
                'option'     => $optionName,
            ],
            ['value' => $value, 'type' => 5]
        );
    }
}
