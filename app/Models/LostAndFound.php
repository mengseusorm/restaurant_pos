<?php

namespace App\Models;

use App\Enums\LostAndFoundStatus;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LostAndFound extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'lost_and_founds';

    protected $fillable = [
        'item_name',
        'found_date',
        'found_by',
        'found_location',
        'customer_name',
        'customer_phone',
        'customer_email',
        'status',
        'claimed_by',
        'claimed_date',
        'notes',
        'branch_id',
        'created_by',
        'storage_location',
        'disposal_date'
    ];

    protected $casts = [
        'id' => 'integer',
        'item_code' => 'string',
        'item_name' => 'string',
        'found_date' => 'date',
        'found_by' => 'string',
        'found_location' => 'string',
        'customer_name' => 'string',
        'customer_phone' => 'string',
        'customer_email' => 'string',
        'status' => 'integer',
        'claimed_by' => 'string',
        'claimed_date' => 'datetime',
        'notes' => 'string',
        'branch_id' => 'integer',
        'created_by' => 'integer',
        'storage_location' => 'string',
        'disposal_date' => 'date'
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateItemCode(): string
    {
        $prefix = 'LF';
        $date = date('Ymd');
        $lastItem = self::whereDate('created_at', today())->latest()->first();
        $sequence = $lastItem ? (intval(substr($lastItem->item_code, -3)) + 1) : 1;
        return $prefix . '-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function getThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('lost_and_found'))) {
            $item = $this->getMedia('lost_and_found')->last();
            return $item->getUrl('thumb');
        }
        return asset('images/default/lost-and-found-thumb.png');
    }

    public function getCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('lost_and_found'))) {
            $item = $this->getMedia('lost_and_found')->last();
            return $item->getUrl('cover');
        }
        return asset('images/default/lost-and-found-cover.png');
    }

    public function getPreviewAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('lost_and_found'))) {
            $item = $this->getMedia('lost_and_found')->last();
            return $item->getUrl('preview');
        }
        return asset('images/default/lost-and-found-preview.png');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->crop('crop-center', 112, 120)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('cover')->crop('crop-center', 260, 180)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('preview')->width(400)->keepOriginalImageFormat()->sharpen(10);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }
}
