<?php

namespace App\Enums;

interface BedStatus
{
    const AVAILABLE = 'available';
    const OCCUPIED  = 'occupied';
    const CLEANING  = 'cleaning';
}
