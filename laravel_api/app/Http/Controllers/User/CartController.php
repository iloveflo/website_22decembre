<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartSession;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    // Hàm helper nội bộ (Private)
    private function coreAddToCart($userId, $sessionId, $productId, $quantity, $attributes)
    {
        // 1. Kiểm tra sản phẩm
        $product = Product::findOrFail($productId);
        if ($product->status !== 'active') {
            throw new \Exception("Sản phẩm '{$product->name}' đã ngừng kinh doanh.");
        }

        // 2. Kiểm tra biến thể (Variant) - Tìm variant khớp bộ thuộc tính
        $query = \App\Models\ProductVariant::where('product_id', $product->id);
        
        foreach ($attributes as $key => $value) {
            $query->where("variant_attributes->$key", $value);
        }

        $variant = $query->first();

        if (!$variant) {
            $attrString = collect($attributes)->map(fn($v, $k) => "$k: $v")->join(', ');
            throw new \Exception("Phiên bản ($attrString) của sản phẩm '{$product->name}' hiện tại không còn hoặc không tồn tại.");
        }

        // Tính toán attributesJson để dùng cho cart_sessions (vẫn dùng ksort để đồng bộ key)
        ksort($attributes);
        $attributesJson = json_encode($attributes);

        // 3. Kiểm tra giỏ hàng hiện tại để cộng dồn
        $query = CartSession::where('product_id', $product->id)
            ->where('variant_info', $attributesJson);

        if ($userId) $query->where('user_id', $userId);
        else $query->where('session_id', $sessionId);

        $cartItem = $query->first();

        // 4. Tính toán số lượng mới
        $newQuantity = $quantity;
        if ($cartItem) {
            $newQuantity += $cartItem->quantity;
        }

        // 5. Kiểm tra tồn kho
        if ($variant->quantity < $newQuantity) {
            throw new \Exception("Sản phẩm '{$product->name}' chỉ còn {$variant->quantity} sản phẩm cho phiên bản này.");
        }

        // 6. Lưu vào DB
        if ($cartItem) {
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            $cartItem = CartSession::create([
                'session_id'   => $sessionId,
                'user_id'      => $userId,
                'product_id'   => $productId,
                'quantity'     => $quantity,
                'variant_info' => $attributesJson,
            ]);
        }

        return $cartItem;
    }

    public function addToCart(Request $request)
    {
        // 1. Validate
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'attributes' => 'required|array', // { "Màu": "Đỏ", "Size": "XL" }
            'session_id' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Xác định User/Session
            $user = auth('sanctum')->user();
            $userId = $user ? $user->id : null;

            $sessionId = $request->input('session_id');
            if (empty($sessionId) && !$userId) $sessionId = (string) Str::uuid();

            // GỌI HÀM CORE
            $cartItem = $this->coreAddToCart(
                $userId,
                $sessionId,
                $validated['product_id'],
                $validated['quantity'],
                $validated['attributes']
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm vào giỏ hàng thành công',
                'data' => [
                    'cart_item' => $cartItem,
                    'session_id' => $sessionId
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function buyAgain(Request $request)
    {
        $request->validate([
            'order_id'   => 'required|exists:orders,id',
            'session_id' => 'nullable|string',
        ]);

        // Xác định User/Session hiện tại
        $user = auth('sanctum')->user();
        $userId = $user ? $user->id : null;
        $sessionId = $request->input('session_id');

        // Tìm đơn hàng cũ
        $order = Order::with('orderItems')->findOrFail($request->order_id);

        // Security: Check quyền sở hữu đơn hàng
        $isOwner = false;
        if ($user) {
            if ($order->user_id == $user->id) $isOwner = true;
        } else {
            if ($sessionId && $order->session_id == $sessionId) $isOwner = true;
        }

        if (!$isOwner) {
            return response()->json(['message' => 'Bạn không có quyền thực hiện hành động này'], 403);
        }

        // Biến để theo dõi kết quả
        $successCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($order->orderItems as $item) {
                try {
                    // Tái sử dụng logic thêm giỏ hàng cho từng món
                    // Parse variant_info từ snapshot đơn hàng cũ
                    $attributes = json_decode($item->variant_info, true) ?: [];

                    $this->coreAddToCart(
                        $userId,
                        $sessionId,
                        $item->product_id,
                        $item->quantity, 
                        $attributes
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            DB::commit();

            $message = "Đã thêm $successCount sản phẩm vào giỏ hàng.";
            if (count($errors) > 0) {
                $message .= " Có " . count($errors) . " sản phẩm không thể thêm.";
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hiển thị danh sách giỏ hàng
     */
    public function index(Request $request)
    {
        // 1. Validate
        $request->validate([
            'session_id' => 'nullable|string',
        ]);

        // 2. Xác định người dùng
        $user = auth('sanctum')->user();

        if (!$user && !$request->session_id) {
            return response()->json([
                'status' => 'success',
                'data' => [],
                'total' => 0
            ]);
        }

        // 3. Query DB
        $query = CartSession::with([
            'product' => function ($q) {
                $q->select('id', 'name', 'slug', 'price', 'sale_price', 'status', 'featured');
            },
            'product.variants', 
            'product.images' => function ($q) {
                $q->limit(1);
            }
        ]);

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('session_id', $request->session_id);
        }

        $query->whereHas('product');
        $cartItems = $query->orderBy('created_at', 'desc')->get();

        // 4. Tính toán tổng tiền
        $totalPrice = 0;
        $formattedItems = $cartItems->map(function ($item) use (&$totalPrice) {
            // Tìm variant tương ứng
            $itemAttrJson = $item->variant_info; // Đã là string JSON trong DB
            
            $itemAttrArray = json_decode($itemAttrJson, true) ?: [];
            $variant = $item->product->variants->first(function ($v) use ($itemAttrArray) {
                // So sánh mảng thay vì so sánh string JSON để tránh lỗi khoảng trắng
                $vAttr = $v->variant_attributes;
                if (count($vAttr) !== count($itemAttrArray)) return false;
                foreach ($itemAttrArray as $key => $val) {
                    if (!isset($vAttr[$key]) || $vAttr[$key] !== $val) return false;
                }
                return true;
            });

            $specificStock = $variant ? $variant->quantity : 0;
            $unitPrice = $item->product->sale_price > 0 ? $item->product->sale_price : $item->product->price;
            
            // Cộng thêm phụ phí của variant nếu có
            if ($variant && $variant->additional_price > 0) {
                $unitPrice += $variant->additional_price;
            }

            $lineTotal = $unitPrice * $item->quantity;
            $totalPrice += $lineTotal;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'image' => $item->product->main_image_url,
                'variant_info' => json_decode($itemAttrJson, true),
                'quantity' => $item->quantity,
                'stock_quantity' => $specificStock,
                'unit_price' => $unitPrice,
                'original_price' => $item->product->price,
                'line_total' => $lineTotal,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedItems,
            'summary' => [
                'total_items' => $cartItems->sum('quantity'),
                'total_price' => $totalPrice
            ]
        ], 200);
    }

    /**
     * Xóa 1 item khỏi giỏ hàng
     */
    public function remove(Request $request, $id)
    {
        $request->validate([
            'session_id' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user();
        $cartItem = CartSession::find($id);

        if (!$cartItem) {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong giỏ'], 404);
        }

        $isOwner = false;
        if ($user) {
            if ($cartItem->user_id == $user->id) $isOwner = true;
        } else {
            if ($request->session_id && $cartItem->session_id == $request->session_id) $isOwner = true;
        }

        if (!$isOwner) {
            return response()->json(['message' => 'Bạn không có quyền xóa sản phẩm này'], 403);
        }

        $cartItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
        ], 200);
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'         => 'required|exists:cart_sessions,id',
            'quantity'   => 'required|integer|min:1',
            'session_id' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user();
        $cartItem = CartSession::find($request->id);

        if (!$cartItem) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $isOwner = false;
        if ($user) {
            if ($cartItem->user_id == $user->id) $isOwner = true;
        } else {
            if ($request->session_id && $cartItem->session_id == $request->session_id) $isOwner = true;
        }

        if (!$isOwner) {
            return response()->json(['message' => 'Bạn không có quyền sửa sản phẩm này'], 403);
        }

        // Check tồn kho dùng JSON path
        $variantQuery = \App\Models\ProductVariant::where('product_id', $cartItem->product_id);
        $vAttr = json_decode($cartItem->variant_info, true) ?: [];
        foreach ($vAttr as $key => $val) {
            $variantQuery->where("variant_attributes->$key", $val);
        }
        $variant = $variantQuery->first();

        if ($variant && $variant->quantity < $request->quantity) {
            return response()->json([
                'message' => 'Kho chỉ còn ' . $variant->quantity . ' sản phẩm.',
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật giỏ hàng thành công'
        ], 200);
    }

    public function checkCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'subtotal'    => 'required|numeric|min:0'
        ]);

        try {
            $user = auth('sanctum')->user();
            $coupon = \App\Models\Coupon::where('code', $request->coupon_code)->first();

            if (!$coupon) {
                throw new \Exception("Mã giảm giá không tồn tại.");
            }

            $now = \Carbon\Carbon::now();
            if ($coupon->status !== 'active') {
                throw new \Exception("Mã giảm giá đang bị khóa.");
            }
            if ($coupon->start_date && $now->lt($coupon->start_date)) {
                throw new \Exception("Mã giảm giá chưa đến đợt áp dụng.");
            }
            if ($coupon->end_date && $now->gt($coupon->end_date)) {
                throw new \Exception("Mã giảm giá đã hết hạn.");
            }
            if ($coupon->usage_limit > 0 && $coupon->used_count >= $coupon->usage_limit) {
                throw new \Exception("Mã giảm giá đã hết lượt sử dụng.");
            }
            if ($coupon->min_order_value > 0 && $request->subtotal < $coupon->min_order_value) {
                throw new \Exception("Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.");
            }
            if ($user) {
                $hasUsed = \App\Models\CouponUsage::where('coupon_id', $coupon->id)
                    ->where('user_id', $user->id)
                    ->exists();
                if ($hasUsed) {
                    throw new \Exception("Bạn đã sử dụng mã giảm giá này rồi.");
                }
            }

            $discountAmount = 0;
            if ($coupon->discount_type === 'fixed') {
                $discountAmount = $coupon->discount_value;
            } elseif ($coupon->discount_type === 'percent') {
                $discountAmount = $request->subtotal * ($coupon->discount_value / 100);
                if ($coupon->max_discount > 0) {
                    $discountAmount = min($discountAmount, $coupon->max_discount);
                }
            }

            $discountAmount = min($discountAmount, $request->subtotal);

            return response()->json([
                'status' => 'success',
                'discount_amount' => $discountAmount,
                'message' => 'Áp dụng mã giảm giá thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    /**
     * Lấy danh sách mã khuyến mãi dành cho user đang đăng nhập:
     * - Mã được cấp riêng (user_coupons) chưa dùng
     * - Mã công khai đang active, chưa hết hạn, chưa hết lượt, user chưa dùng
     */
    public function myCoupons(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Chưa đăng nhập'], 401);
        }

        $now = \Carbon\Carbon::now();

        // 1. Tập hợp coupon_id mà user đã sử dụng
        $usedCouponIds = \App\Models\CouponUsage::where('user_id', $user->id)
            ->pluck('coupon_id')
            ->toArray();

        // 2. Mã được cấp riêng cho user (user_coupons) và chưa dùng
        $personalCouponIds = \DB::table('user_coupons')
            ->where('user_id', $user->id)
            ->where('is_used', 0)
            ->pluck('coupon_id')
            ->toArray();

        // 3. Query coupon hợp lệ
        $coupons = \App\Models\Coupon::where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            })
            ->whereNotIn('id', $usedCouponIds) // Loại bỏ mã user đã dùng
            ->where(function ($q) use ($personalCouponIds) {
                // Hiển thị: mã công khai (không có trong user_coupons bảng nào)
                // HOẶC mã được cấp riêng cho user này
                $q->whereNotIn('id', function ($sub) {
                    $sub->select('coupon_id')->from('user_coupons');
                })->orWhereIn('id', $personalCouponIds);
            })
            ->orderByRaw('FIELD(id, ' . (empty($personalCouponIds) ? '0' : implode(',', $personalCouponIds)) . ') DESC') // Mã riêng lên đầu
            ->get(['id', 'code', 'description', 'discount_type', 'discount_value', 'min_order_value', 'max_discount', 'end_date']);

        return response()->json([
            'status' => 'success',
            'data'   => $coupons,
            'personal_ids' => $personalCouponIds, // Để FE đánh dấu badge "Của tôi"
        ]);
    }
}
