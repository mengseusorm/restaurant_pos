<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FloorPlanGroup extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    
    protected $table = "floor_plan_groups";
    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'branch_id', 
        'sort_order', 
        'status'
    ];
    
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'branch_id' => 'integer',
        'sort_order' => 'integer',
        'status' => 'integer'
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function getFloorPlanPhotoAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('floor_plan'))) {
            $media = $this->getMedia('floor_plan')->last();
            return $media->getUrl();
        }
        return '';
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('preview')->width(800)->keepOriginalImageFormat()->sharpen(10);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function diningTables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DiningTable::class);
    }

    public function activeDiningTables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DiningTable::class)->where('status', \App\Enums\Status::ACTIVE);
    }

    public function getTotalTablesAttribute(): int
    {
        return $this->diningTables()->count();
    }

    public function getOccupiedTablesAttribute(): int
    {
        return $this->diningTables()->whereNotNull('current_order_id')->count();
    }

    public function getAvailableTablesAttribute(): int
    {
        return $this->getTotalTablesAttribute() - $this->getOccupiedTablesAttribute();
    }
}
