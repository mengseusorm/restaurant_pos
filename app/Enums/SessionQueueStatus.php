<?php

namespace App\Enums;

interface SessionQueueStatus
{
    const WAITING   = 'waiting';
    const CALLED    = 'called';
    const SEATED    = 'seated';
    const CANCELLED = 'cancelled';
}
