<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

/**
 * Each branch has its own item stock and only 1 warehouse (itemStock)
 */
class ItemStock extends Model
{
    use HasFactory;
    protected $table = "item_stocks";
    protected $fillable = ['name', 'description','branch_id'];
    protected $casts = [
        'id'                => 'integer',
        'name'              => 'string',
        'branch_id'         => 'integer',
        'description'       => 'string',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }
}
