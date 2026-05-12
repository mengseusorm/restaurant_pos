<?php

namespace App\Enums;

interface GroupSessionStatus
{
    const OPEN        = 'open';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED   = 'completed';
    const CANCELLED   = 'cancelled';
}
