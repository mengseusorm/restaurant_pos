<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPointTransaction extends Model
{
    use HasFactory;

    protected $table = "member_point_transactions";

    protected $fillable = [
        'member_id',
        'branch_id',
        'type',
        'points',
        'reference_type',
        'reference_id',
        'note',
    ];

    protected $casts = [
        'id' => 'integer',
        'member_id' => 'integer',
        'branch_id' => 'integer',
        'type' => 'string',
        'points' => 'integer',
        'reference_type' => 'string',
        'reference_id' => 'integer',
        'note' => 'string',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function scopeEarned($query)
    {
        return $query->where('type', 'earn');
    }

    public function scopeRedeemed($query)
    {
        return $query->where('type', 'redeem');
    }

    public function scopeRevertedEarn($query)
    {
        return $query->where('type', 'revert_earn');
    }

    public function scopeRevertedRedeem($query)
    {
        return $query->where('type', 'revert_redeem');
    }

    public function scopeForMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeForReference($query, $referenceType, $referenceId)
    {
        return $query->where('reference_type', $referenceType)
                     ->where('reference_id', $referenceId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->take($limit);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeWithMember($query)
    {
        return $query->with('member');
    }

    public function scopeWithReference($query)
    {
        return $query->with('reference');
    }
}
