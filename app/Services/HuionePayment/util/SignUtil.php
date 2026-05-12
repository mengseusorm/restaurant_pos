<?php

namespace App\Services\HuionePayment\util;

use Illuminate\Support\Facades\Log;

class SignUtil
{
    const SIGN = 'sign';

    public function sign($obj, $priKey)
    {
        $str = self::jsonToSignContent($obj);
        // echo "json转待验签参数:" . $str . PHP_EOL;
        Log::info("json转待验签参数: $str");

        $priKey = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($priKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        Log::info("私钥: $priKey");
        
        $pi_key = openssl_get_privatekey($priKey);
        if (!$pi_key){
            Log::error('SignUtil:sign 私钥不可用');
            die('私钥不可用');}
        $sign = openssl_sign($str, $encrypted, $pi_key, OPENSSL_ALGO_SHA256);
        if (!$sign) {
            return ('加密失败');
        }
        return base64_encode($encrypted);
    }

    public static function verify($obj, $sign, $pubKey)
    {
        $str = self::jsonToSignContent($obj);
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($pubKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $pu_key = openssl_get_publickey($pubKey);
        if (!$pu_key) die('公钥不可用');
        $verifyResult = openssl_verify($str, base64_decode($sign), $pu_key, OPENSSL_ALGO_SHA256);
        return $verifyResult ? 'true' : 'false';
    }


    public static function jsonToSignContent($param = array()): string
    {
        ksort($param);
        $result = '';
        foreach ($param as $key => $value) {
            if ($value === '' || $key === self::SIGN) {
                continue;
            }

            if ($result) {
                $result .= '&';
            }

            // 统一处理值
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $result .= "$key=$value";

        }
        return $result;
    }

}