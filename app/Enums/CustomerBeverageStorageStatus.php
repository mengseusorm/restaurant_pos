<?php

namespace App\Enums;

interface CustomerBeverageStorageStatus
{
    const STORED = 1;
    const CLAIMED = 2;
    const EXPIRED = 3;
    const DISPOSED = 4;
}
