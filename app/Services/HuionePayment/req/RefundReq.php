<?php

namespace App\Services\HuionePayment\req;

class RefundReq
{
    /**
     * 随机数
     */
    public string $nonce;

    /**
     * 时间戳（毫秒 UTC）
     */
    public int $timestamp;

    /**
     * 商户订单号，最大长度 64
     */
    public string $outTradeNo;

    /**
     * 退款原因，最大长度 255
     */
    public string $reason;

    /**
     * 退款金额，支持最多两位小数
     */
    public float $refund;

    /**
     * 交易ID
     */
    public string $transactionId;

    public function __construct(
        string $nonce,
        int $timestamp,
        string $outTradeNo,
        string $reason,
        float $refund,
        string $transactionId
    ) {
        $this->nonce = $nonce;
        $this->timestamp = $timestamp;
        $this->outTradeNo = mb_substr($outTradeNo, 0, 64);
        $this->reason = mb_substr($reason, 0, 255);
        $this->refund = $this->normalizeAmount($refund);
        $this->transactionId = $transactionId;
    }

    private function normalizeAmount(float $amount): float
    {
        // 保留最多两位小数，并去除无效的 0
        return (float) rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
    }

    public function toArray(): array
    {
        return [
            'nonce' => $this->nonce,
            'timestamp' => $this->timestamp,
            'outTradeNo' => $this->outTradeNo,
            'reason' => $this->reason,
            'refund' => $this->refund,
            'transactionId' => $this->transactionId,
        ];
    }
}
