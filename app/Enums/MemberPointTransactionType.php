<?php

namespace App\Enums;

interface MemberPointTransactionType
{
    public const EARN = 'earn';
    public const REDEEM = 'redeem';
    public const REVERT_EARN = 'revert_earn';
    public const REVERT_REDEEM = 'revert_redeem';
}
