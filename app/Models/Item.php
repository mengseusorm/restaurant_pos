<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enums\Status;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Item extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = "items";
    protected $fillable = [
        'name',
        'name_kh',
        'name_cn',
        'name_en',
        'item_code',
        'item_category_id',
        'slug',
        'tax_id',
        'item_type',

        'tax_name',
        'tax_rate',
        'tax_type',
        'price',
        'tax_amount',
        'price_with_tax',

        'is_featured',
        'description',
        'caution',
        'status',
        'order',
        'kitchen_printer_id',
        'label_printer_id',
        'branch_id',
        'barcode', 
        'manage_stock',
        'is_print_menu',
        'is_print_label',
        'can_input_custom_name',
        'can_input_custom_unit_price',
        'item_kind',
        'duration',
    ];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id'                  => 'integer',
        'name'                => 'string',
        'name_kh'             => 'string',
        'name_cn'             => 'string',
        'name_en'             => 'string',
        'item_code'           => 'string',
        'item_category_id'    => 'integer',
        'slug'                => 'string',
        'tax_id'              => 'integer',
        'item_type'           => 'integer',

        'tax_name'            => 'string',
        'tax_rate'            => 'string',
        'tax_type'            => 'integer',
        'price'               => 'decimal:8',
        'tax_amount'          => 'decimal:8',
        'price_with_tax'      => 'decimal:8',

        'is_featured'         => 'integer',
        'description'         => 'string',
        'caution'             => 'string',
        'status'              => 'integer',
        'order'               => 'integer',
        'kitchen_printer_id'  => 'integer',
        'label_printer_id'    => 'integer',
        'branch_id'           => 'integer',
        "barcode"             => 'string', 
        'manage_stock'        => 'integer',
        'is_print_menu'       => 'integer',
        'is_print_label'      => 'integer',
        'can_input_custom_name' => 'integer',
        'can_input_custom_unit_price' => 'integer',
        'item_kind' => 'integer',
        'duration'  => 'integer',
    ];

    public function getThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('item'))) {
            $item = $this->getMedia('item')->last();
            return $item->getUrl('thumb');
        }
        return asset('images/item/thumb.png');
    }

    public function getCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('item'))) {
            $item = $this->getMedia('item')->last();
            return $item->getUrl('cover');
        }
        return asset('images/item/cover.png');
    }

    public function getPreviewAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('item'))) {
            $item = $this->getMedia('item')->last();
            return $item->getUrl('preview');
        }
        return asset('images/item/cover.png');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->crop('crop-center', 112, 120)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('cover')->crop('crop-center', 260, 180)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('preview')->width(400)->keepOriginalImageFormat()->sharpen(10);
    }

    public function variations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemVariation::class)->with('itemAttribute')->where(['status' => Status::ACTIVE]);
    }

    public function extras(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemExtra::class)->where(['status' => Status::ACTIVE]);
    }

    public function addons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemAddon::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id', 'id');
    }

    public function tax(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class, 'item_id', 'id');
    }

    public function offer(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offer_items');
    }

    public function kitchenPrinter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(kitchenPrinter::class);
    }

    public function labelPrinter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(kitchenPrinter::class, 'label_printer_id');
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(itemStock::class);
    }

    public function stockRecord(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockRecord::class);
    }

    public function branch() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function pointGifts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PointGift::class);
    }
    
    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function orderItemDeleted(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItemDeleted::class, 'item_id', 'id');
    } 
     
}
