<?php

namespace App\Services\HuionePayment\req;
class OrderInfoReq
{
    /**
     * 随机数
     */
    public string $nonce;

    /**
     * 商户订单号，最大长度 64
     */
    public array $outTradeNoList;

    /**
     * 时间戳（毫秒 UTC）
     */
    public int $timestamp;

    public function __construct(
        string $nonce,
        array $outTradeNoList,
        int $timestamp,
    ) {
        $this->nonce = $nonce;
        $this->outTradeNoList = $outTradeNoList;
        $this->timestamp = $timestamp;
    }



    public function toArray(): array
    {
        return [
            'nonce' => $this->nonce,
            'outTradeNoList' => $this->outTradeNoList,
            'timestamp' => $this->timestamp,
        ];
    }
}
