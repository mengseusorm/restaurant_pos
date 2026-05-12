<?php

namespace App\Enums;

interface PaymentOrderEnum
{
    const PENDING  = 1;
    const PAID     = 2;
    const FAILED   = 3;
    const EXPIRED  = 4;
    const ACTIVE   = 5;
    const INACTIVE = 10;
}
