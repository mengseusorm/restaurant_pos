<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kitchenPrinter extends Model
{
    use HasFactory;

    protected $table = 'kitchen_printers';
    protected $fillable = [
        'name',
        'ip',
        'port',  
        'printer_type',
        'printer_method',
        'branch_id', 
        'printer_server',
        'label',
        'print_copies' 
    ];
    protected $casts = [
        'id'             => 'integer',
        'name'           => 'string',
        'ip'             => 'string',
        'port'           => 'string', 
        'printer_type'   => 'integer',
        'printer_method' => 'integer',
        'branch_id '     => 'integer', 
        'printer_server' => 'string',
        'label'          => 'string',
        'print_copies'   => 'integer' 
    ];

    public function item(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Item::class);
    }
    
    public function branch() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    } 

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

}
