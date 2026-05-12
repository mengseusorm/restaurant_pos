<?php
namespace App\Enums;

interface PosPaymentMethod
{
    const CASH = 1;
    const CARD = 2;
    const MOBILE_BANKING = 3;
    const OTHER = 4;
    const ABA = 5;
    const ACLEDA = 6;
    const HUIONE = 7;
}
