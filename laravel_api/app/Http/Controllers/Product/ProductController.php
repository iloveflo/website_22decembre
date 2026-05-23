<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   public function getByCategory(Request $request, $slug)
    {
        $category = Category::with(['children', 'parent.parent'])
             ->where('slug', $slug)
             ->where('status', 'active')
             ->first();
 
         // Kiểm tra tính "Active" của toàn bộ phả hệ
         if (!$category || ($category->parent && $category->parent->status !== 'active') || 
             ($category->parent && $category->parent->parent && $category->parent->parent->status !== 'active')) {
             return response()->json(['message' => 'Danh mục không tồn tại hoặc đã bị ẩn'], 404);
         }

        // Lấy chính nó + các con (nếu có)
        $targetIds = $category->children->pluck('id')->toArray();
        $targetIds[] = $category->id;

        $query = Product::with(['images', 'variants'])
            ->whereIn('category_id', $targetIds)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc');

        // --- Lọc giá ---
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // --- Lọc theo size (qua bảng product_variants) ---
        if ($request->filled('sizes')) {
            $sizes = explode(',', $request->sizes);
            $query->whereHas('variants', function ($q) use ($sizes) {
                $q->where(function ($subQ) use ($sizes) {
                    $keys = ['Size', 'size', 'Kích thước'];
                    foreach ($sizes as $size) {
                        foreach ($keys as $key) {
                            $subQ->orWhereJsonContains("variant_attributes->{$key}", $size);
                        }
                    }
                });
            });
        }

        // --- Lọc theo màu ---
        if ($request->filled('colors')) {
            $colors = explode(',', $request->colors);
            $query->whereHas('variants', function ($q) use ($colors) {
                $q->where(function ($subQ) use ($colors) {
                    $keys = ['Màu', 'Màu sắc', 'Color', 'color'];
                    foreach ($colors as $color) {
                        foreach ($keys as $key) {
                            $subQ->orWhereJsonContains("variant_attributes->{$key}", $color);
                        }
                    }
                });
            });
        }

        // 4. Phân trang
        $perPage = $request->input('per_page', 12); // Mặc định 12
        if ($perPage > 12) $perPage = 12; // Nếu xin > 12 thì ép về 12
        $products = $query->paginate($perPage);

        // 5. Trả về response
        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ],
            'products' => $products
        ]);
    }



    public function getAll(Request $request) 
    {
        $query = Product::with(['images', 'variants'])
            ->activeCategory() // Chỉ lấy sản phẩm thuộc danh mục active
            ->where('status', 'active')
            ->orderBy('created_at', 'desc');

        // Lọc theo khoảng giá
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // Lọc theo size
        if ($request->filled('sizes')) {
            $sizes = explode(',', $request->sizes);
            $query->whereHas('variants', function ($q) use ($sizes) {
                $q->where(function ($subQ) use ($sizes) {
                    $keys = ['Size', 'size', 'Kích thước'];
                    foreach ($sizes as $size) {
                        foreach ($keys as $key) {
                            $subQ->orWhereJsonContains("variant_attributes->{$key}", $size);
                        }
                    }
                });
            });
        }

        // Lọc theo màu
        if ($request->filled('colors')) {
            $colors = explode(',', $request->colors);
            $query->whereHas('variants', function ($q) use ($colors) {
                $q->where(function ($subQ) use ($colors) {
                    $keys = ['Màu', 'Màu sắc', 'Color', 'color'];
                    foreach ($colors as $color) {
                        foreach ($keys as $key) {
                            $subQ->orWhereJsonContains("variant_attributes->{$key}", $color);
                        }
                    }
                });
            });
        }

        // Phân trang
        $products = $query->paginate(12);

        return response()->json($products);
    }
}
