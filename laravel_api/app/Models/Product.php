<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'sale_price',
        'sku',
        'stock_quantity',
        'status',
        'featured',
        'view_count',
    ];

    /**
     * Các trường datetime và cast
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'featured'   => 'boolean',
        // 'price' => 'decimal:2', // Nếu muốn ép kiểu trả về luôn là số thực
    ];

    /**
     * Tự động thêm attribute ảo vào JSON khi return response
     * Giúp frontend nhận được field 'stock_quantity' mà không cần gọi hàm
     */
    protected $appends = ['main_image_url'];

    // ==========================================
    // ACCESSORS (Thuộc tính ảo)
    // ==========================================

    // stock_quantity is now a real database column.

    /**
     * Kiểm tra xem sản phẩm còn hàng không
     * Gọi bằng: $product->is_in_stock
     */
    public function getIsInStockAttribute()
    {
        return $this->stock_quantity > 0 && $this->status === 'active';
    }

    /**
     * Lấy URL ảnh chính
     * Gọi bằng: $product->main_image_url
     */
    // 1. Attribute trả về đường dẫn TƯƠNG ĐỐI (Dùng để lưu vào DB)
    public function getMainImagePathAttribute()
    {
        // Ưu tiên lấy từ relation mainImage
        if ($this->relationLoaded('mainImage') && $this->mainImage) {
            return $this->mainImage->image_path; // Trả về: uploads/sp1.jpg
        }

        // Nếu không, tìm cái đầu tiên trong list
        $firstImage = $this->images->sortBy('sort_order')->first();

        if ($firstImage) {
            return $firstImage->image_path; // Trả về: uploads/sp1.jpg
        }

        // Mặc định
        return 'https://placehold.co/300x300?text=No+Image';
    }

    // 2. Attribute trả về đường dẫn TUYỆT ĐỐI (Dùng cho Frontend/Vue hiển thị)
    public function getMainImageUrlAttribute()
    {
        // Gọi lại hàm bên trên để lấy đường dẫn tương đối
        $relativePath = $this->main_image_path;

        if (filter_var($relativePath, FILTER_VALIDATE_URL)) {
            return $relativePath;
        }

        return '/' . ltrim($relativePath, '/');
    }



    // ==========================================
    // SCOPES (Phạm vi truy vấn)
    // ==========================================

    /**
     * Chỉ lấy các sản phẩm thuộc danh mục đang hoạt động (active)
     */
    public function scopeActiveCategory($query)
    {
        return $query->whereHas('category', function ($q) {
            // 1. Bản thân danh mục phải active
            $q->where('categories.status', 'active')
              ->where(function ($sub) {
                  // 2. Kiểm tra Cha (nếu có)
                  $sub->whereNull('parent_id')
                      ->orWhereHas('parent', function ($p) {
                          $p->where('status', 'active')
                            // 3. Kiểm tra Ông nội (nếu có)
                            ->where(function ($grand) {
                                $grand->whereNull('parent_id')
                                      ->orWhereHas('parent', function ($gp) {
                                          $gp->where('status', 'active');
                                      });
                            });
                      });
              });
        });
    }

    // ==========================================
    // RELATIONS (Quan hệ)
    // ==========================================

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * Relationship lấy ra ảnh đại diện (để dùng Eager Loading: with('mainImage'))
     */
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class, 'product_id')
            ->where('is_primary', 1)
            ->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function cartSessions()
    {
        return $this->hasMany(CartSession::class, 'product_id');
    }
}
