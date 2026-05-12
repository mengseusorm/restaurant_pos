<?php

namespace App\Enums;

interface RoomStatus
{
    const AVAILABLE = 'available';
    const OCCUPIED  = 'occupied';
    const CLEANING  = 'cleaning';
}
