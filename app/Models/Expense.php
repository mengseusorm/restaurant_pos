<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Expense extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = "expenses";
    
    protected $fillable = [
        'branch_id',
        'expense_code',
        'expense_date',
        'expense_type_id',
        'amount',
        'payment_method_id',
        'description',
        'paid_to',
        'reference_no',
        'recorded_by',
        'approved_by',
        'status',
        'is_deleted'
    ];

    protected $casts = [
        'id'                 => 'integer',
        'branch_id'          => 'integer',
        'expense_code'       => 'string',
        'expense_date'       => 'date',
        'expense_type_id'    => 'integer',
        'amount'             => 'decimal:2',
        'payment_method_id'  => 'integer',
        'description'        => 'string',
        'paid_to'            => 'string',
        'reference_no'       => 'string',
        'recorded_by'        => 'integer',
        'approved_by'        => 'integer',
        'status'             => 'string',
        'is_deleted'         => 'boolean'
    ];

    protected $dates = ['deleted_at'];

    public function getReceiptImageAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('expense'))) {
            $expense = $this->getMedia('expense')->last();
            return $expense->getUrl('thumb');
        }
        return asset('images/default/receipt.png');
    }

    public function getReceiptPreviewAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('expense'))) {
            $expense = $this->getMedia('expense')->last();
            return $expense->getUrl('preview');
        }
        return asset('images/default/receipt.png');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->crop('crop-center', 112, 120)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('preview')->width(400)->keepOriginalImageFormat()->sharpen(10);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function expenseType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function paymentMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExpensePaymentMethod::class);
    }

    public function recordedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }
}
