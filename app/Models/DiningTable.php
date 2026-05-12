<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DiningTable extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $table = "dining_tables";
    protected $fillable = [
        'name',
        'slug',
        'size',
        'status',
        'branch_id',
        'qr_code',
        'current_order_id',
        'floor_plan_group_id',
        'position_x',
        'position_y',
        'width',
        'height',
        'rotation',
        'shape',
        'color',
        'current_guests'
    ];
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'qr_code' => 'string',
        'size' => 'integer',
        'branch_id' => 'integer',
        'status' => 'integer',
        'current_order_id' => 'integer',
        'floor_plan_group_id' => 'integer',
        'position_x' => 'decimal:2',
        'position_y' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'rotation' => 'integer',
        'shape' => 'string',
        'color' => 'string',
        'current_guests' => 'integer'
    ];

    public function getTablePhotoAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('table'))) {
            $media = $this->getMedia('table')->last();
            return $media->getUrl();
        }
        return '';
    }

    public function getTableThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('table'))) {
            $media = $this->getMedia('table')->last();
            return $media->getUrl('thumb');
        }
        return '';
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('preview')->width(400)->keepOriginalImageFormat()->sharpen(10);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }


    public function floorPlanGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FloorPlanGroup::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getQrAttribute(): ?string
    {
        if (!empty($this->qr_code)) {
            return asset($this->qr_code);
        }
        return null;
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    public function getIsOccupiedAttribute(): bool
    {
        return !is_null($this->current_order_id);
    }

    public function getOccupancyRateAttribute(): float
    {
        if ($this->size == 0) return 0;
        return min(($this->current_guests / $this->size) * 100, 100);
    }

}
