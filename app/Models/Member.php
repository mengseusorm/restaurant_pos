<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;

    protected $table = "members";

    protected $fillable = [
        'name',
        'phone',
        'user_id',
        'branch_id',
        'card_number',
        'point_balance',
        'is_active',
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'phone' => 'string',
        'user_id' => 'integer',
        'branch_id' => 'integer',
        'card_number' => 'string',
        'point_balance' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(MemberPointTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithUser($query)
    {
        return $query->with('user');
    }

    public function scopeWithPointTransactions($query)
    {
        return $query->with('pointTransactions');
    }

    public function scopeForPhone($query, $phone)
    {
        return $query->where('phone', $phone);
    }

    public function scopeForPhoneOrCardNumber($query, $value)
    {
        return $query->where(function ($q) use ($value) {
            $q->where('phone', $value)
              ->orWhere('card_number', $value);
        });
    }

    public function scopeForCardNumber($query, $cardNumber)
    {
        return $query->where('card_number', $cardNumber);
    }

    public function scopeWithPointBalance($query, $balance)
    {
        return $query->where('point_balance', '>=', $balance);
    }
}
