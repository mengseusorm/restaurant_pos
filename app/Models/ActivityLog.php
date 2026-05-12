<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\DefaultAccessModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use DefaultAccessModelTrait;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'branch_id',
        'event',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'collection',
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        // Add global scope for branch filtering
        static::addGlobalScope(new BranchScope());
        
        // Automatically set branch_id when creating activity logs
        static::creating(function ($activity) {
            if (Auth::check() && !$activity->branch_id) {
                $user = Auth::user();
                $activity->branch_id = $user->branch_id ?? 0;
            }
        });
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope activities by branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope activities by log name
     */
    public function scopeByLogName($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope activities by date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope activities by causer (user)
     */
    public function scopeByCauser($query, $causerId = null)
    {
        if ($causerId) {
            return $query->where('causer_id', $causerId)->where('causer_type', User::class);
        }
        return $query->whereNotNull('causer_id')->where('causer_type', User::class);
    }
}
