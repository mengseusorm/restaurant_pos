<?php

namespace App\Enums;

interface ReservationStatus
{
    const PENDING = 1;
    const CONFIRMED = 2;
    const CHECKED_IN = 3;
    const CANCELLED = 4;
    const NO_SHOW = 5;
    const COMPLETED = 6;
}
