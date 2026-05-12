<?php

namespace App\Services\HuionePayment\util;

require_once __DIR__ . '/../enum/HostEnum.php';

use App\Services\HuionePayment\enum\HostEnum;
use Illuminate\Support\Facades\Log;

class PostUtil {

    const APP_ID = 'app-id';

    public static function sendPostRequest($urlStr, $body, $appId): string {
        $url = HostEnum::$ProdUrl  . $urlStr;
        // echo "请求地址: $url" . PHP_EOL;

        Log::info("请求地址: $url");

        $headers = [
            self::APP_ID . ": $appId",
            "Content-Type: application/json"
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            // echo 'Curl error: ' . curl_error($ch);
            Log::error('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    public static function sendPostRequestTest($urlStr, $body, $appId): string {
        $url = HostEnum::$UatUrl  . $urlStr;
        // echo "请求地址: $url" . PHP_EOL;

        Log::info("请求地址: $url");

        $headers = [
            self::APP_ID . ": $appId",
            "Content-Type: application/json"
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            // echo 'Curl error: ' . curl_error($ch);
            Log::error('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }
}