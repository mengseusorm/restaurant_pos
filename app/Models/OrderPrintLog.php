<?php

namespace App\Models;

use App\Enums\PrintType;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPrintLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'order_serial_number',
        'print_type',
        'print_success',
        'error_message'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'branch_id' => 'integer',
        'order_serial_number' => 'string',
        'print_type' => 'integer',
        'print_success' => 'boolean',
        'error_message' => 'string'
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Print type constants
    const PRINT_TYPE_MENU = PrintType::MENU;
    const PRINT_TYPE_INVOICE = PrintType::INVOICE;
    const PRINT_TYPE_BILL = PrintType::BILL;
    const PRINT_TYPE_LABEL = PrintType::LABEL;
}
