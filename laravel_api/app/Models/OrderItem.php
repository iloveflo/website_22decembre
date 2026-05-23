<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_image',
        'variant_info',
        'size',
        'color',
        'price',
        'quantity',
        'subtotal',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ: OrderItem thuộc Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Quan hệ: OrderItem thuộc Product (có thể null nếu sản phẩm bị xóa)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Quan hệ: OrderItem có thể có Review
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_id');
    }

    // 1. Tự động thêm các field vào JSON
    protected $appends = ['product_image_url', 'size', 'color'];

    public function getProductImageUrlAttribute()
    {
        if (!$this->product_image) {
            return 'https://placehold.co/300x300?text=No+Image';
        }

        if (filter_var($this->product_image, FILTER_VALIDATE_URL)) {
            return $this->product_image;
        }

        return '/' . ltrim($this->product_image, '/');
    }

    // 3. Truy xuất Size và Color từ variant_info nếu cột size/color trống
    public function getSizeAttribute()
    {
        $value = $this->attributes['size'] ?? null;
        if ($value) return $value;
        if (!$this->variant_info) return 'N/A';
        $attrs = is_string($this->variant_info) ? json_decode($this->variant_info, true) : $this->variant_info;
        return $attrs['Size'] ?? $attrs['size'] ?? $attrs['Kích thước'] ?? 'N/A';
    }

    public function getColorAttribute()
    {
        $value = $this->attributes['color'] ?? null;
        if ($value) return $value;
        if (!$this->variant_info) return 'N/A';
        $attrs = is_string($this->variant_info) ? json_decode($this->variant_info, true) : $this->variant_info;
        return $attrs['Color'] ?? $attrs['color'] ?? $attrs['Màu sắc'] ?? $attrs['Màu'] ?? 'N/A';
    }
}
