<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Product\ProductDetailsController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\OrderUserController;
use App\Http\Controllers\Admin\CouponController;
use Illuminate\Support\Facades\DB;


Route::get('/captcha', [AuthController::class, 'getCaptcha']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

// Các route dành cho Nhân viên (Staff)
Route::middleware(['auth:sanctum', 'staff'])->prefix('admin')->group(function () {
    Route::get('/dashboard-stats', [StatisticsController::class, 'getDashboardStats']);
    
    // quản lý đơn hàng
    Route::get('/orders/payment-methods', [OrderController::class, 'getUniquePaymentMethods']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order_code}', [OrderController::class, 'show']);
    Route::put('/{order_code}/status', [OrderController::class, 'updateStatus']);

    // quản lý danh mục (đã chuyển ra ngoài products để đồng bộ với giao diện mới)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/tree', [CategoryController::class, 'tree']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    Route::get('/products', [ProductAdminController::class, 'index']);
    Route::post('/products', [ProductAdminController::class, 'store']);
    Route::get('/products/{id}', [ProductAdminController::class, 'show']);
    Route::put('/products/{id}', [ProductAdminController::class, 'update']);
    Route::delete('/products/{id}', [ProductAdminController::class, 'destroy']);
    Route::get('/products/{product}/images', [ProductImageController::class, 'index']);
    Route::post('/products/{product}/images', [ProductImageController::class, 'store']);
    Route::delete('/products/images/{image}', [ProductImageController::class, 'destroy']);
    Route::patch('/products/images/{image}/primary', [ProductImageController::class, 'setPrimary']);
});

// Các route dành riêng cho Quản trị viên (Super Admin)
Route::middleware(['auth:sanctum', 'super_admin'])->prefix('admin')->group(function () {
    //quản lý người dùng
    Route::get('/users/deleted', [UserController::class, 'deletedUsers']);
    Route::patch('/users/{id}/restore', [UserController::class, 'restore']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/{id}/status', [UserController::class, 'changeStatus']);
    Route::get('/users/{id}/orders', [UserController::class, 'orders']);

    //thống kê báo cáo 
    Route::get('/statistics/overview', [StatisticsController::class, 'getOverview']);
    Route::get('/statistics/revenue-over-time', [StatisticsController::class, 'getRevenueOverTime']);
    Route::get('/statistics/sales-by-category', [StatisticsController::class, 'getSalesByCategory']);
    Route::get('/statistics/order-status-distribution', [StatisticsController::class, 'getOrderStatusDistribution']);
    Route::get('/statistics/payment-methods-distribution', [StatisticsController::class, 'getPaymentMethodsDistribution']);
    Route::get('/statistics/top-selling-products', [StatisticsController::class, 'getTopSellingProducts']);
    Route::get('/statistics/top-customers', [StatisticsController::class, 'getTopCustomers']);
    Route::get('/statistics/recent-activities', [StatisticsController::class, 'getRecentActivities']);
    Route::post('/statistics/export', [StatisticsController::class, 'exportReport']);

    //quản lý khuyến mại
    Route::apiResource('coupons', CouponController::class);
});

// hiển thị danh mục cho menu
Route::get('/categories/tree', [CategoryController::class, 'publicTree']);

//hiển thị sản phẩm
Route::get('/products/category/{slug}', [ProductController::class, 'getByCategory']);
Route::get('/products', [ProductController::class, 'getAll']);
Route::get('/products/{slug}', [ProductDetailsController::class, 'show']);//chi tiết sản phẩm

// tìm kiếm
Route::get('/search', [SearchController::class, 'getAll']);

// liên hệ
Route::post('/contact', [\App\Http\Controllers\User\ContactController::class, 'submit']);

// tài khoản khách hàng - CHỈ dành cho người đã đăng nhập (Thông tin nhạy cảm)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/update', [ProfileController::class, 'update']);
});

// Luồng người dùng (Hỗ trợ cả Thành viên & Khách vãng lai - Controller tự check owner)
Route::get('/orders', [OrderUserController::class, 'index']);
Route::get('/orders/lookup', [OrderUserController::class, 'lookup']); // Tra cứu cho khách vãng lai
Route::get('/orders/{code}', [OrderUserController::class, 'show']);
Route::post('/orders/{code}/cancel', [OrderUserController::class, 'cancel']);

// Route giỏ hàng & Thanh toán (Công khai - Controller tự xử lý User hoặc Guest qua session_id)
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'addToCart']);
    Route::put('/update', [CartController::class, 'update']);
    Route::delete('/remove/{id}', [CartController::class, 'remove']);
    Route::post('/buy-again', [CartController::class, 'buyAgain']);
    Route::post('/check-coupon', [CartController::class, 'checkCoupon']);
    Route::get('/my-coupons', [CartController::class, 'myCoupons']); // Mã của user đang login
});

Route::get('/checkout/info', [CheckoutController::class, 'getCheckoutInfo']);
Route::post('/checkout/process', [CheckoutController::class, 'processCheckout']);
Route::post('/checkout/change-to-cod', [CheckoutController::class, 'changePaymentToCod']);
Route::post('/checkout/retry-vnpay', [CheckoutController::class, 'retryVnpay']);

// Đánh giá (Controller nên check xem user đã mua hàng chưa)
Route::get('/orders/{order_code}/review-info', [OrderController::class, 'getReviewInfo']);
Route::post('/reviews', [OrderController::class, 'storeReviews']);

// Thanh toán (VNPAY callback)
Route::get('/payment/vnpay-callback', [CheckoutController::class, 'vnpayCallback']);
Route::get('/payment/vnpay-result', [CheckoutController::class, 'getVnpayResult']);



// Gọi avien
Route::get('/wake-up-db', function () {
    // Lệnh này bắt buộc Laravel phải kết nối vào Aiven
    // Chỉ cần SELECT 1 là đủ để Aiven tính là "có hoạt động"
    try {
        DB::select('SELECT 1');
        return response()->json(['status' => 'Database is awake!']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 500);
    }
});