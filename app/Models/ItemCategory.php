<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ItemCategory extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = "item_categories";
    protected $fillable = ['name', 'name_kh', 'name_cn', 'name_en', 'item_category_code', 'slug', 'description', 'status','branch_id','sort'];
    protected $casts = [
        'id'                 => 'integer',
        'name'               => 'string',
        'name_kh'            => 'string',
        'name_cn'            => 'string',
        'name_en'            => 'string',
        'item_category_code' => 'string',
        'slug'               => 'string',
        'description'        => 'string',
        'status'             => 'integer',
        'branch_id'          => 'integer',
        'sort'               => 'integer'
    ];

    public function getThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('item-category'))) {
            $category = $this->getMedia('item-category')->last();
            return $category->getUrl('thumb');
        }
        return asset('images/category/thumb.png');
    }

    public function getCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('item-category'))) {
            $category = $this->getMedia('item-category')->last();
            return $category->getUrl('cover');
        }
        return asset('images/category/cover.png');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->crop('crop-center', 75, 48)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('cover')->width(400)->keepOriginalImageFormat()->sharpen(10);
    }

    public function items() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Item::class)->where(['status' => Status::ACTIVE]);
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
