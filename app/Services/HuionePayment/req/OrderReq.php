<?php

namespace App\Services\HuionePayment\req;
class OrderReq
{
    /**
     * 金额，支持最多两位小数，去除无效0
     */
    public float $amount;

    /**
     * 币种，字符串
     */
    public string $currency;

    /**
     * 商品描述，最大长度 64
     */
    public string $description;

    /**
     * 随机数
     */
    public string $nonce;

    /**
     * 商户订单号，最大长度 64
     */
    public string $outTradeNo;

    /**
     * 订单过期时间（秒）
     */
    public int $timeExpire;

    /**
     * 时间戳（毫秒 UTC）
     */
    public int $timestamp;

    /**
     * 附加数据（最大长度 255，非必填）
     */
    public ?string $attach;

    public function __construct(
        float $amount,
        string $currency,
        string $description,
        string $nonce,
        string $outTradeNo,
        int $timeExpire,
        int $timestamp,
        ?string $attach = null
    ) {
        $this->amount = $this->normalizeAmount($amount);
        $this->currency = $currency;
        $this->description = mb_substr($description, 0, 64);
        $this->nonce = $nonce;
        $this->outTradeNo = mb_substr($outTradeNo, 0, 64);
        $this->timeExpire = $timeExpire;
        $this->timestamp = $timestamp;
        $this->attach = $attach ? mb_substr($attach, 0, 255) : null;
    }

    private function normalizeAmount(float $amount): float
    {
        // 保留最多两位小数，并去除无效的 0
        return (float) rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'nonce' => $this->nonce,
            'outTradeNo' => $this->outTradeNo,
            'timeExpire' => $this->timeExpire,
            'timestamp' => $this->timestamp,
            'attach' => $this->attach,
        ];
    }
}
