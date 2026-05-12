<?php

namespace App\Enums;

interface Source
{
    const WEB = 5;
    const APP = 10;
    const POS = 15;
    const TABLE = 20;
    const ONLINE_ORDER = 25;
    const RETAIL_POS = 30;
    const TELEGRAM_MINI_APP = 35;
}
