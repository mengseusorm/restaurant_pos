<?php

namespace App\Services\HuionePayment\enum;

class AppConfigEnum
{

    // Data:
    /**
     * 应用ID：bd11934880319605850113
     * 
     * 服务端公钥（$publicKey）： MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWi4y32FoSw1NPnkCKwF7EA4DeCwWIxScC6q9TKz7E3Y0TKwimqeXxtH5Yxt0mKPwFQItRNDkKxrfRU4dbRaQIuKcAJr8ndllbYX6D79vXEActk4xGHddOCwC/+boT4Mdqq8X5XaYcYmLaecH1Rl8MwvImlnJcaDpLx4f4iQyccwIDAQAB
     * 商户公钥 （$privateKey）：MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCqn8jnkd/xrPLqnIM7gmGTL367q/0r2Rbl7Fy4Z9spA4JUM1J0oYHURUGgBKZ8OIBSjGXYTsCXHjpseGDMVfJ55tdCS2JpYghZl5sbN7EUsWF1bjF8CFKMyGxlEA3Xh3JgYHVhj/qIfmEYfFH0941f/i/yMJBrnBv3tM35ky2WcwIDAQAB
     */

    // 每个应用存在两套钥匙对，商户公私钥对，服务端公私钥对；
    // 商户下单，商户私钥加签（商户创建应用时保存私钥），下单， Hui one用商户公钥验签，
    // Hui one回调，用服务端私钥加签，回调，商户用服务端公钥（商户后台可见服务端公钥）验签;

    
    /** For Testing */
    public static $appId = "bd11934880319605850113";
    public static $merchantId = "1780857138277335042";  // not yet
    public static $privateKey = "MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAKqfyOeR3/Gs8uqcgzuCYZMvfrur/SvZFuXsXLhn2ykDglQzUnShgdRFQaAEpnw4gFKMZdhOwJceOmx4YMxV8nnm10JLYmliCFmXmxs3sRSxYXVuMXwIUozIbGUQDdeHcmBgdWGP+oh+YRh8UfT3jV/+L/IwkGucG/e0zfmTLZZzAgMBAAECgYBo75Bt6ydhyU40wEFtrgg4r3MwFNzFxOPyUGXN/AGrvb6/7jh+Bn6EgHuV4IZLy6wQGMziNbz4s9yWrYpK4WkYUeT19U7bLNa9sdx8zliWkFDGIkkpKV9YeCbMpMpB85+gHHxWOBlir3DTUfy5GiJT5MWRv5MLIVUP3VwspNXk4QJBAOIKGx9gGpGWiMkKrgHeAgJsZGbjvCWmPOHz6OHIGbCVYXzyCvpBlCRehQAESk6Ho+RCVtEz3ZE+7I8TIrxSVmsCQQDBPVhkxdOfyxZz4KdFAhsTAkmMoxPzB/bHzEfSg6NAyDcsmixlYMd64BiHnw812XpbcKVcw+AJagMKUSBU1vIZAkEAhiZ3SFyyB/uuPJrBAMywpp2LzOCVtkZ91Z/7c5xdWsadMBk6WKH4+Mi76HGjZA3uP7b7bXd5pQ0SOiuiLuAy9wJBAI6swIsHFU0yIY3FHTtyZpMOaUeRULVr9+VlCtJ/pxW5viMMYiMn5aDvvKzF3/EedZz1+uIXLV9GCqCfbYO2gGECQGHJKq+LK4z7uzLXQQm1UtBTMo3Wwcwp8YoEkBo7tfTx/P0FvrfaNM6tHMNOFCKQcC7vNeO0kK4RnCKteoo3t/Y=";

    public static $publicKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWi4y32FoSw1NPnkCKwF7EA4DeCwWIxScC6q9TKz7E3Y0TKwimqeXxtH5Yxt0mKPwFQItRNDkKxrfRU4dbRaQIuKcAJr8ndllbYX6D79vXEActk4xGHddOCwC/+boT4Mdqq8X5XaYcYmLaecH1Rl8MwvImlnJcaDpLx4f4iQyccwIDAQAB";

    
    /** For Production */
    // public static string $appId = "2611782313897625313282";
    // public static string $merchantId = "1780857138277335042";  // not yet
    // public static string $privateKey = "MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAKqfyOeR3/Gs8uqcgzuCYZMvfrur/SvZFuXsXLhn2ykDglQzUnShgdRFQaAEpnw4gFKMZdhOwJceOmx4YMxV8nnm10JLYmliCFmXmxs3sRSxYXVuMXwIUozIbGUQDdeHcmBgdWGP+oh+YRh8UfT3jV/+L/IwkGucG/e0zfmTLZZzAgMBAAECgYBo75Bt6ydhyU40wEFtrgg4r3MwFNzFxOPyUGXN/AGrvb6/7jh+Bn6EgHuV4IZLy6wQGMziNbz4s9yWrYpK4WkYUeT19U7bLNa9sdx8zliWkFDGIkkpKV9YeCbMpMpB85+gHHxWOBlir3DTUfy5GiJT5MWRv5MLIVUP3VwspNXk4QJBAOIKGx9gGpGWiMkKrgHeAgJsZGbjvCWmPOHz6OHIGbCVYXzyCvpBlCRehQAESk6Ho+RCVtEz3ZE+7I8TIrxSVmsCQQDBPVhkxdOfyxZz4KdFAhsTAkmMoxPzB/bHzEfSg6NAyDcsmixlYMd64BiHnw812XpbcKVcw+AJagMKUSBU1vIZAkEAhiZ3SFyyB/uuPJrBAMywpp2LzOCVtkZ91Z/7c5xdWsadMBk6WKH4+Mi76HGjZA3uP7b7bXd5pQ0SOiuiLuAy9wJBAI6swIsHFU0yIY3FHTtyZpMOaUeRULVr9+VlCtJ/pxW5viMMYiMn5aDvvKzF3/EedZz1+uIXLV9GCqCfbYO2gGECQGHJKq+LK4z7uzLXQQm1UtBTMo3Wwcwp8YoEkBo7tfTx/P0FvrfaNM6tHMNOFCKQcC7vNeO0kK4RnCKteoo3t/Y=";

    // public static string $publicKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCqn8jnkd/xrPLqnIM7gmGTL367q/0r2Rbl7Fy4Z9spA4JUM1J0oYHURUGgBKZ8OIBSjGXYTsCXHjpseGDMVfJ55tdCS2JpYghZl5sbN7EUsWF1bjF8CFKMyGxlEA3Xh3JgYHVhj/qIfmEYfFH0941f/i/yMJBrnBv3tM35ky2WcwIDAQAB";
}
