<?php

namespace App\Services\HuionePayment\util;


class NonceUtil
{
    public static function getNonce(): string
    {
        return uniqid();
    }
}