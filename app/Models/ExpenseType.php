<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseType extends Model
{
    use HasFactory;

    protected $table = "expense_types";

    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'id' => 'integer',
        'branch_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'is_active' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', Status::ACTIVE);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', Status::INACTIVE);
    }

    /**
     * Check if expense type is active
     */
    public function isActive(): bool
    {
        return $this->is_active === Status::ACTIVE;
    }

    /**
     * Check if expense type is inactive
     */
    public function isInactive(): bool
    {
        return $this->is_active === Status::INACTIVE;
    }
}
