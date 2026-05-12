<?php

namespace App\Services\HuionePayment\enum;

// 路径枚举
class PathEnum
{
    // 代收下单
    public static string $createPrepayOrder = "/sdk/open/transactions/createPrepaymentOrder";
    // 代收订单支付状态查询
    public static string $queryOrder = "/sdk/open/transactions/pay/info";
    // 代收订单退款
    public static string $refund = "/sdk/open/transactions/refund";
    // 代收订单退款状态查询
    public static string $queryRefund = "/sdk/open/transactions/refund/info";
    // 获取代付账户对应币种余额
    public static string $queryBalance = "/sdk/open/payment/balance";
    // 通过手机号代付给汇旺用户/sdk/open/payment/phone
    public static string $phone = "/sdk/open/payment/phone";
    // 代付订单状态查询
    public static string $queryPayment = "/sdk/open/payment/orders";
}