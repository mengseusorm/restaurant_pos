<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CustomerBeverageStorage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'customer_beverage_storages';

    protected $fillable = [
        'storage_code',
        'customer_name',
        'customer_phone',
        'beverage_name',
        'quantity',
        'original_quantity',
        'unit',
        'store_date',
        'expiry_date',
        'status',
        'storage_location',
        'claimed_date',
        'disposed_date',
        'disposed_reason',
        'notes',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'storage_code' => 'string',
        'customer_name' => 'string',
        'customer_phone' => 'string',
        'beverage_name' => 'string',
        'quantity' => 'decimal:2',
        'original_quantity' => 'decimal:2',
        'unit' => 'string',
        'store_date' => 'date',
        'expiry_date' => 'date',
        'status' => 'integer',
        'storage_location' => 'string',
        'claimed_date' => 'datetime',
        'disposed_date' => 'datetime',
        'disposed_reason' => 'string',
        'notes' => 'string',
        'branch_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(100)->height(100);
        $this->addMediaConversion('cover')->width(600)->height(600);
        $this->addMediaConversion('preview')->width(300)->height(300);
    }

    public function getThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('customer_beverage_storage'))) {
            return $this->getMedia('customer_beverage_storage')[0]->getUrl('thumb');
        }
        return asset('images/default/beverage-placeholder.png');
    }

    public function getCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('customer_beverage_storage'))) {
            return $this->getMedia('customer_beverage_storage')[0]->getUrl('cover');
        }
        return asset('images/default/beverage-placeholder.png');
    }

    public function getPreviewAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('customer_beverage_storage'))) {
            return $this->getMedia('customer_beverage_storage')[0]->getUrl('preview');
        }
        return asset('images/default/beverage-placeholder.png');
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
