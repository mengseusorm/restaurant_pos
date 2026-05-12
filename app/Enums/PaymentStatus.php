<?php

namespace App\Enums;

interface PaymentStatus
{
    const PAID   = 5;
    const UNPAID = 10;
    const PARTIAL = 15;
}
