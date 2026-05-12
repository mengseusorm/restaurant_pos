<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TherapistProfile extends Model
{
    use HasFactory;

    protected $table = 'therapist_profiles';

    protected $fillable = [
        'branch_id',
        'user_id',
        'code',
        'verify_code',
        'commission_rate',
        'status',
    ];

    protected $casts = [
        'id'              => 'integer',
        'branch_id'       => 'integer',
        'user_id'         => 'integer',
        'code'            => 'string',
        'verify_code'     => 'string',
        'commission_rate' => 'decimal:2',
        'status'          => 'string',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());

        static::creating(function (TherapistProfile $model) {
            if (empty($model->verify_code)) {
                do {
                    $code = Str::random(16);
                } while (static::withoutGlobalScopes()->where('verify_code', $code)->exists());
                $model->verify_code = $code;
            }
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubSession::class, 'therapist_id', 'user_id');
    }

    
}
