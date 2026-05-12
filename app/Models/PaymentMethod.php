<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PaymentMethod extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'payment_methods';

    protected $fillable = [
        'user_id', 
        'name',
        'value',
        'provider',
        'account_name',
        'account_number',
        'expiry_date',
        'billing_address',
        'is_default',
        'order_number',
        'status',
        'show_online_payment',  // Show this payment method when order from online
        'show_table_order_payment', // Show this payment method when order from table order
        'is_pos_static_qr_code_payment', // Enable static QR code payment for POS
        'is_pos_bank_integrate_payment',
        'short_description'
    ];

    protected $casts = [
        'id'                               => 'integer',
        'user_id'                          => 'integer',
        'name'                             => 'string',
        'value'                            => 'integer',
        'provider'                         => 'string',
        'account_name'                     => 'string',
        'account_number'                   => 'integer',
        'expiry_date'                      => 'string',
        'billing_address'                  => 'string',
        'is_default'                       => 'integer',
        'order_number'                     => 'integer',
        'status'                           => 'integer',
        'show_online_payment'              => 'integer',
        'show_table_order_payment'         => 'integer',
        'is_pos_static_qr_code_payment'    => 'integer',
        'is_pos_bank_integrate_payment'    => 'integer',
        'short_description'                => 'string',
    ];

    public function getPosStaticQrCodeThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('pos_static_qr_code'))) {
            $qrCode = $this->getMedia('pos_static_qr_code')->last();
            return $qrCode->getUrl('thumb');
        }
        return asset('images/payment-method/qr-thumb.png');
    }

    public function getPosStaticQrCodeCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('pos_static_qr_code'))) {
            $qrCode = $this->getMedia('pos_static_qr_code')->last();
            return $qrCode->getUrl('cover');
        }
        return asset('images/payment-method/qr-cover.png');
    }

    public function getPosStaticQrCodePreviewAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('pos_static_qr_code'))) {
            $qrCode = $this->getMedia('pos_static_qr_code')->last();
            return $qrCode->getUrl('preview');
        }
        return asset('images/payment-method/qr-preview.png');
    }

    public function getLogoThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('logo'))) {
            $logo = $this->getMedia('logo')->last();
            return $logo->getUrl('thumb');
        }
        return asset('images/payment-method/logo-thumb.png');
    }

    public function getLogoCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('logo'))) {
            $logo = $this->getMedia('logo')->last();
            return $logo->getUrl('cover');
        }
        return asset('images/payment-method/logo-cover.png');
    }

    public function getLogoPreviewAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('logo'))) {
            $logo = $this->getMedia('logo')->last();
            return $logo->getUrl('preview');
        }
        return asset('images/payment-method/logo-preview.png');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->crop('crop-center', 112, 120)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('cover')->crop('crop-center', 260, 180)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('preview')->width(400)->keepOriginalImageFormat()->sharpen(10);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo');
        $this->addMediaCollection('pos_static_qr_code');
    }

    public function order() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(Order::class);
    }

    public function orderDeleted() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(OrderDeleted::class);
    }

    public function supportedCurrencies() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'payment_method_currency');
    }
}
