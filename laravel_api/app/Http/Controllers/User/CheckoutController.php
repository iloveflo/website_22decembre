<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt; // Thêm thư viện Crypt
use Illuminate\Contracts\Encryption\DecryptException; // Để bắt lỗi giải mã
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\CartSession;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    /**
     * Lấy thông tin hiển thị lên form
     * Cần GIẢI MÃ (Decrypt) dữ liệu từ DB để Frontend hiển thị được
     */
    public function getCheckoutInfo(Request $request)
    {
        $user = $request->user('sanctum');

        if ($user) {
            // Xử lý giải mã an toàn (tránh lỗi nếu dữ liệu cũ chưa mã hóa hoặc bị lỗi)
            $phone = '';
            $address = '';

            try {
                // Kiểm tra nếu có dữ liệu thì mới giải mã
                $phone = $user->phone ? Crypt::decryptString($user->phone) : '';
            } catch (DecryptException $e) {
                // Nếu giải mã lỗi (do data cũ là plain text), ta lấy nguyên gốc
                $phone = $user->phone;
            }

            try {
                $address = $user->address ? Crypt::decryptString($user->address) : '';
            } catch (DecryptException $e) {
                $address = $user->address;
            }

            return response()->json([
                'is_logged_in' => true,
                'customer_info' => [
                    'id'        => $user->id,
                    'full_name' => $user->full_name,
                    'email'     => $user->email,
                    'phone'     => $phone,   // Đã giải mã
                    'address'   => $address, // Đã giải mã
                ]
            ]);
        }

        // Khách vãng lai
        return response()->json([
            'is_logged_in' => false,
            'customer_info' => [
                'full_name' => '',
                'email'     => '',
                'phone'     => '',
                'address'   => '',
            ]
        ]);
    }

    /**
     * Xử lý đặt hàng
     */
    public function processCheckout(Request $request)
    {
        // 1. Validate (Giữ nguyên)
        $validatedData = $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:500',
            'payment_method' => 'required|in:cod,vnpay',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size'     => 'nullable|string',
            'items.*.color'    => 'nullable|string',
            'items.*.attributes' => 'nullable|array', // Thêm cái này
            'note'           => 'nullable|string',
            'session_id'     => 'required|string',
            'coupon_code'    => 'nullable|string|exists:coupons,code'
        ]);

        // Chống tạo đơn trùng khi người dùng bấm VNPay nhiều lần trong thời gian ngắn
        if ($validatedData['payment_method'] === 'vnpay') {
            $existingPendingOrder = Order::where('session_id', $validatedData['session_id'])
                ->where('payment_method', 'vnpay')
                ->where('payment_status', 'pending')
                ->where('created_at', '>=', Carbon::now()->subMinutes(15))
                ->latest('id')
                ->first();

            if ($existingPendingOrder) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn VNPay đang chờ thanh toán, vui lòng tiếp tục thanh toán.',
                    'payment_method' => 'vnpay',
                    'order_code' => $existingPendingOrder->order_code,
                    'payment_url' => $this->buildVnpayUrl($request, $existingPendingOrder, (float) $existingPendingOrder->total_amount),
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $user = $request->user('sanctum');

            // 2. Update User (Giữ nguyên) & Kiểm tra thanh toán khách vãng lai
            if ($user) {
                $user->full_name = $validatedData['full_name'];
                $user->phone = $validatedData['phone'] ? Crypt::encryptString($validatedData['phone']) : null;
                $user->address = $validatedData['address'] ? Crypt::encryptString($validatedData['address']) : null;
                $user->save();
            } else {
                // Khách vãng lai: Bắt buộc phải là VNPay
                if ($validatedData['payment_method'] === 'cod') {
                    throw new \Exception("Khách vãng lai bắt buộc phải thanh toán qua VNPay để hoàn tất đơn hàng.");
                }
            }

            // 3. Xử lý Logic Lọc Dần & Trừ Kho
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($validatedData['items'] as $item) {
                $product = Product::find($item['id']);

                // --- BƯỚC 1: LẤY ATTRIBUTES & CHUẨN HÓA ---
                $attributes = $item['attributes'] ?? [];
                ksort($attributes);
                $attributesJson = json_encode($attributes);

                // --- BƯỚC 2: TÌM BIẾN THỂ VỚI LOCK ---
                $variantQuery = ProductVariant::where('product_id', $product->id);
                foreach ($attributes as $key => $val) {
                    $variantQuery->where("variant_attributes->$key", $val);
                }
                $variant = $variantQuery->lockForUpdate()->first();

                // --- BƯỚC 3: KIỂM TRA & TRỪ KHO ---
                if (!$variant) {
                    $attrString = collect($attributes)->map(fn($v, $k) => "$k: $v")->join(', ');
                    throw new \Exception("Sản phẩm {$product->name} ($attrString) không tồn tại trong kho.");
                }

                if ($variant->quantity < $item['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ đáp ứng số lượng {$item['quantity']}.");
                }

                $variant->decrement('quantity', $item['quantity']);

                // --- BƯỚC 4: TÍNH TIỀN & SNAPSHOT ---
                $basePrice = $product->sale_price > 0 ? $product->sale_price : $product->price;
                $finalPrice = $basePrice + ($variant->additional_price ?? 0);

                $lineTotal = $finalPrice * $item['quantity'];
                $subtotal += $lineTotal;

                $orderItemsData[] = [
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_image' => $product->main_image_path,
                    'variant_info'  => $attributesJson,
                    'price'         => $finalPrice,
                    'quantity'      => $item['quantity'],
                    'subtotal'      => $lineTotal,
                ];
            }


            // --- BẮT ĐẦU LOGIC KHUYẾN MẠI ---
            $discountAmount = 0;
            $couponIdApplied = null; // Để lưu lại ID coupon đã dùng

            if (!empty($request->coupon_code)) {
                // Lock coupon để tránh race condition (nhiều người dùng mã cuối cùng cùng lúc)
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->lockForUpdate() 
                    ->first();

                if (!$coupon) {
                    throw new \Exception("Mã giảm giá không tồn tại.");
                }

                // A. Check trạng thái và thời gian
                $now = Carbon::now();
                if ($coupon->status !== 'active') {
                    throw new \Exception("Mã giảm giá đang bị khóa.");
                }
                if ($coupon->start_date && $now->lt($coupon->start_date)) {
                    throw new \Exception("Mã giảm giá chưa đến đợt áp dụng.");
                }
                if ($coupon->end_date && $now->gt($coupon->end_date)) {
                    throw new \Exception("Mã giảm giá đã hết hạn.");
                }

                // B. Check giới hạn số lượng toàn hệ thống
                if ($coupon->usage_limit > 0 && $coupon->used_count >= $coupon->usage_limit) {
                    throw new \Exception("Mã giảm giá đã hết lượt sử dụng.");
                }

                // C. Check giá trị đơn hàng tối thiểu
                if ($coupon->min_order_value > 0 && $subtotal < $coupon->min_order_value) {
                    throw new \Exception("Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.");
                }

                // D. Check lịch sử sử dụng của User (Nếu đã login)
                if ($user) {
                    $hasUsed = CouponUsage::where('coupon_id', $coupon->id)
                        ->where('user_id', $user->id)
                        ->exists();
                    if ($hasUsed) {
                        throw new \Exception("Bạn đã sử dụng mã giảm giá này rồi.");
                    }
                }

                // E. Tính toán số tiền giảm
                if ($coupon->discount_type === 'fixed') {
                    $discountAmount = $coupon->discount_value;
                } elseif ($coupon->discount_type === 'percent') {
                    $discountAmount = $subtotal * ($coupon->discount_value / 100);
                    // Áp dụng mức giảm tối đa (nếu có)
                    if ($coupon->max_discount > 0) {
                        $discountAmount = min($discountAmount, $coupon->max_discount);
                    }
                }

                // Đảm bảo không giảm quá giá trị đơn hàng
                $discountAmount = min($discountAmount, $subtotal);
                
                // Đánh dấu để cập nhật sau khi tạo Order
                $couponIdApplied = $coupon->id;
                
                // Tăng biến đếm sử dụng
                $coupon->increment('used_count');
            }


            // 4. Tạo Order & Order Items
            $shippingFee = 0;
            // Tính tổng cuối cùng: Subtotal + Ship - Discount
            $totalAmount = $subtotal + $shippingFee - $discountAmount;
            // Đảm bảo không âm
            $totalAmount = max($totalAmount, 0);

            $orderCode = 'ORD' . time() . strtoupper(Str::random(4));

            $order = new Order();
            $order->user_id         = $user ? $user->id : null;
            $order->order_code      = $orderCode;
            $order->full_name       = $validatedData['full_name'];
            $order->email           = $validatedData['email'];
            $order->phone           = $validatedData['phone'];
            $order->address         = $validatedData['address'];
            $order->subtotal        = $subtotal;
            $order->shipping_fee    = $shippingFee;
            $order->discount_amount = $discountAmount;
            $order->total_amount    = $totalAmount;
            $order->payment_method  = $validatedData['payment_method'];
            $order->note            = $request->note;
            $order->order_status    = 'pending';
            $order->payment_status  = 'pending';
            $order->session_id      = $request->session_id;
            $order->save();

            foreach ($orderItemsData as $itemData) {
                $orderItem = new OrderItem($itemData);
                $orderItem->order_id = $order->id;
                $orderItem->save();
            }


            // --- LƯU LỊCH SỬ DÙNG COUPON  ---
            if ($couponIdApplied) {
                CouponUsage::create([
                    'coupon_id' => $couponIdApplied,
                    'user_id'   => $user ? $user->id : null,
                    'order_id'  => $order->id,
                ]);
            }
            // 5. Xóa giỏ hàng & Commit
            // - User đã login: Xóa ngay (đơn luôn được giữ lại dù chưa thanh toán)
            // - Khách vãng lai + COD: Không có (đã bị chặn)
            // - Khách vãng lai + VNPay: Đợi callback thành công mới xóa
            if ($user) {
                // User đã login: xóa giỏ theo user_id
                CartSession::where('user_id', $user->id)->delete();
            }
            DB::commit();

            // 6. Gửi Email (Chỉ gửi ngay cho Thành viên đã login)
            // Vì Thành viên chắc chắn được giữ đơn (Pending), còn Khách vãng lai (VNPay) phải đợi thành công.
            if ($user) {
                $this->sendOrderConfirmationMail($order);
            }

            // 7. Response 
            if ($validatedData['payment_method'] === 'cod') {
                return response()->json([
                    'success' => true,
                    'type' => 'cod',
                    'message' => 'Đặt hàng thành công!',
                    'order_code' => $orderCode
                ]);
            }

            // --- XỬ LÝ THANH TOÁN VNPAY ---
            if ($validatedData['payment_method'] === 'vnpay') {
                return response()->json([
                    'success' => true,
                    'message' => 'Tạo link thanh toán thành công',
                    'payment_method' => 'vnpay',
                    'order_code' => $orderCode,
                    'payment_url' => $this->buildVnpayUrl($request, $order, (float) $totalAmount),
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }


    public function vnpayCallback(Request $request)
    {
        $inputData = $request->all();
        $orderCode = $inputData['vnp_TxnRef'] ?? null;
        
        // 1. Kiểm tra chữ ký
        $verify = $this->verifyVnpaySignature($inputData);
        $isValidSignature = $verify['is_valid'];
        
        // 2. Các biến trạng thái
        $responseCode = (string) ($inputData['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string) ($inputData['vnp_TransactionStatus'] ?? '');
        $isSuccess = $responseCode === '00' && ($transactionStatus === '' || $transactionStatus === '00');

        // 3. Tìm đơn hàng qua bảng Payments (Vì vnp_TxnRef giờ là transaction_id duy nhất)
        $paymentRecord = Payment::where('transaction_id', $orderCode)->first();
        $order = $paymentRecord ? $paymentRecord->order : null;

        // [SỬA LỖI QUAN TRỌNG] Lấy URL frontend từ biến môi trường
        // Tránh lỗi trình duyệt Nginx đánh rơi port (vd: redirect về http://localhost thay vì localhost:8000)
        $baseUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/');

        $realOrderCode = $order ? $order->order_code : $orderCode;
        $finalStatus = ($isValidSignature && $isSuccess) ? 'success' : 'failed';
        
        // Nếu là khách vãng lai thất bại, ta gửi thêm flag để frontend báo quay lại giỏ hàng
        if (!$isSuccess && $order && !$order->user_id) {
            $finalStatus = 'failed_guest';
        }

        $redirectUrl = $baseUrl . '/payment/result?order_code=' . urlencode((string) $realOrderCode)
            . '&status=' . $finalStatus
            . '&signature_valid=' . ($isValidSignature ? '1' : '0')
            . '&response_code=' . urlencode($responseCode);

        if ($isValidSignature && $order) {
            // [GUARD] Nếu đơn đã chuyển sang COD rồi, bỏ qua mọi cập nhật từ VNPay callback
            // Tránh VNPay callback ghi đè payment_status về 'failed' sau khi user đã chuyển COD
            if ($order->payment_method === 'cod') {
                Log::info("VNPAY Callback skipped for Order {$order->order_code}: already switched to COD.");
                return redirect()->away($redirectUrl);
            }

            // Cập nhật record payment hiện tại
            $paymentRecord->status = $isSuccess ? 'success' : 'failed';
            $paymentRecord->payload = json_encode($inputData);
            $paymentRecord->save();
            // Log để debug nếu cần
            Log::info("VNPAY Callback for Order: $orderCode. Status: " . ($isSuccess ? 'Success' : 'Failed'));
            
            // Cập nhật trạng thái nếu thành công và đơn chưa được thanh toán
            if ($isSuccess) {
                $this->markOrderAsPaid($order, $inputData['vnp_TransactionNo'] ?? null);
                
                // [THÊM MỚI] Xóa giỏ hàng sau khi thanh toán thành công
                // Kiểm tra xóa theo user_id nếu có, không thì xóa theo session_id
                if ($order->user_id) {
                    CartSession::where('user_id', $order->user_id)->delete();
                } elseif ($order->session_id) {
                    CartSession::where('session_id', $order->session_id)->delete();
                }

                // [BỔ SUNG] Gửi mail cho khách vãng lai nếu thanh toán thành công
                // (Vì khách vãng lai VNPay chưa được gửi mail ở bước processCheckout)
                if (!$order->user_id) {
                    $this->sendOrderConfirmationMail($order);
                }
            } elseif ($order->payment_status === 'pending') {
                // Nếu thanh toán thất bại
                if (!$order->user_id) {
                    // [BẮT BUỘC CHO KHÁCH VÃNG LAI] 
                    // Nếu khách vãng lai thanh toán thất bại -> Xóa đơn, hoàn lại kho
                    $this->reverseStock($order);
                    $order->delete();
                } else {
                    // Với User đã login, ta giữ đơn lại ở trạng thái 'failed' để họ có thể thử lại từ lịch sử đơn hàng
                    $order->payment_status = 'failed';
                    $order->save();
                }
            }
        }

        return redirect()->away($redirectUrl);
    }



    /**
     * Hàm dùng chung để xác thực chữ ký VNPAY
     */
    private function verifyVnpaySignature(array $inputData): array
    {
        $vnp_HashSecret = trim((string) config('vnpay.hash_secret'));
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        
        // Loại bỏ các tham số không dùng để ký
        $data = [];
        foreach ($inputData as $key => $value) {
            if (substr($key, 0, 4) === "vnp_" && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType') {
                $data[$key] = $value;
            }
        }

        ksort($data);
        $hashData = "";
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $isValid = hash_equals(strtolower($secureHash), strtolower((string)$vnp_SecureHash));

        if (!$isValid) {
            Log::warning("VNPAY Signature Mismatch!", [
                'received' => $vnp_SecureHash,
                'calculated' => $secureHash,
                'hashData' => $hashData
            ]);
        }

        return [
            'is_valid' => $isValid,
            'calculated' => $secureHash
        ];
    }

    public function getVnpayResult(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string',
        ]);

        $order = Order::with(['orderItems', 'payments'])
            ->where('order_code', $request->order_code)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'failed',
                'data' => ['order' => null]
            ], 404);
        }

        // [TỰ ĐỘNG CẬP NHẬT] Đánh dấu các giao dịch VNPay đang chờ quá 1 phút sang thất bại
        Payment::where('order_id', $order->id)
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(1))
            ->update(['status' => 'failed']);

        // Load lại payments sau khi dọn dẹp
        $order->load('payments');

        // Nếu đơn vẫn đang pending, hãy chủ động hỏi VNPAY xem đã trả tiền chưa (QueryDR)
        if ($order->payment_status === 'pending') {
            $vnpRes = $this->queryVnpayTransaction($order->order_code);
            
            if ($vnpRes && isset($vnpRes['vnp_ResponseCode']) && $vnpRes['vnp_ResponseCode'] === '00') {
                $vnpStatus = $vnpRes['vnp_TransactionStatus'] ?? '';
                
                if ($vnpStatus === '00') {
                    $this->markOrderAsPaid($order, $vnpRes['vnp_TransactionNo'] ?? null);
                    Payment::updateOrCreate(
                        ['order_id' => $order->id, 'transaction_id' => 'TXN_' . $order->order_code . '_QUERY'],
                        [
                            'bank_code' => $vnpRes['vnp_BankCode'] ?? null,
                            'amount' => $order->total_amount,
                            'payment_method' => 'vnpay',
                            'status' => 'success',
                            'payload' => json_encode($vnpRes),
                        ]
                    );
                } elseif ($vnpStatus === '02') {
                    $order->payment_status = 'failed';
                    $order->save();
                }
            }
        }

        return response()->json([
            'status' => $order->payment_status === 'paid' ? 'success' : 'failed',
            'data' => [
                'order' => $order->fresh(['orderItems', 'payments']),
            ]
        ]);
    }

    /**
     * Truy vấn trạng thái giao dịch từ VNPAY (QueryDR)
     * Đây là giải pháp thay thế hoàn hảo cho IPN nếu bạn không muốn cấu hình URL IPN phức tạp.
     */
    private function queryVnpayTransaction(string $orderCode)
    {
        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = trim((string) config('vnpay.hash_secret'));
        
        // Endpoint QueryDR (Thường khác với URL thanh toán)
        $vnp_ApiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction"; 
        // Trong môi trường thật sẽ là: https://vnpayment.vn/merchant_webapi/api/transaction

        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) return null;

        $vnp_RequestId = (string) (time() . rand(100, 999)); 
        $vnp_Command = "querydr";
        $vnp_TxnRef = $orderCode;
        $vnp_OrderInfo = "Query status for order $orderCode";
        $vnp_TransactionDate = date('YmdHis', strtotime($order->created_at));
        $vnp_CreateDate = date('YmdHis');
        $vnp_IpAddr = request()->ip();

        // Tạo chuỗi hash theo đúng định dạng QueryDR 2.1.0
        $hashData = $vnp_RequestId . '|' . '2.1.0' . '|' . $vnp_Command . '|' . $vnp_TmnCode . '|' . $vnp_TxnRef . '|' . $vnp_TransactionDate . '|' . $vnp_CreateDate . '|' . $vnp_IpAddr . '|' . $vnp_OrderInfo;
        $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        $data = [
            "vnp_RequestId" => $vnp_RequestId,
            "vnp_Version" => "2.1.0",
            "vnp_Command" => $vnp_Command,
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_TransactionDate" => $vnp_TransactionDate,
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_SecureHash" => $vnp_SecureHash
        ];

        try {
            $response = Http::post($vnp_ApiUrl, $data);
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("VNPAY QueryDR Error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Endpoint cho phép người dùng đổi sang COD nếu thanh toán Online thất bại
     */
    public function changePaymentToCod(Request $request)
    {
        $request->validate([
            'order_code' => 'required|exists:orders,order_code'
        ]);

        DB::beginTransaction();
        try {
            $order = Order::where('order_code', $request->order_code)->lockForUpdate()->first();

            if (!$order) throw new \Exception("Đơn hàng không tồn tại.");
            if ($order->payment_status === 'paid') throw new \Exception("Đơn hàng đã được thanh toán.");

            // [IDEMPOTENCY GUARD] Nếu đã chuyển sang COD rồi thì không làm gì thêm
            // Tránh tạo trùng record khi beforeunload gọi nhiều lần (F5, đóng tab...)
            if ($order->payment_method === 'cod' && $order->payment_status === 'pending') {
                DB::rollBack();
                return response()->json(['success' => true, 'message' => 'Đơn hàng đã ở trạng thái COD.']);
            }

            // Cập nhật phương thức VÀ reset trạng thái thanh toán về pending
            $order->payment_method  = 'cod';
            $order->payment_status  = 'pending'; // Reset từ 'failed' về 'pending'
            $order->save();

            // Xóa các record payment 'failed' cũ (VNPay bị hủy/thất bại)
            // Vì đây là những lần thử không thành công, không nên hiện trong lịch sử
            Payment::where('order_id', $order->id)
                ->where('status', 'failed')
                ->delete();

            // Ghi nhận vào bảng Payments
            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => 'COD_' . $order->order_code . '_' . time(),
                'amount' => $order->total_amount,
                'payment_method' => 'cod',
                'status' => 'pending',
                'payload' => json_encode(['action' => 'switched_from_vnpay'])
            ]);

            DB::commit();

            // KHÔNG gửi mail ở đây vì User đã nhận được mail lúc bấm "Đặt hàng" rồi
            // Trừ khi bạn muốn gửi thêm mail "Đã đổi sang COD" riêng. 
            // Nhưng theo yêu cầu "Duy nhất 1 email" thì ta bỏ qua.

            return response()->json(['success' => true, 'message' => 'Đã chuyển sang thanh toán COD thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint lấy link thanh toán mới cho đơn hàng cũ (Retry VNPay)
     */
    public function retryVnpay(Request $request)
    {
        $request->validate([
            'order_code' => 'required|exists:orders,order_code'
        ]);

        $order = Order::where('order_code', $request->order_code)->first();
        if ($order->payment_status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Đơn hàng đã thanh toán rồi.'], 400);
        }

        // [BỔ SUNG] Cập nhật lại phương thức thanh toán là vnpay nếu trước đó người dùng đã đổi sang COD
        if ($order->payment_method !== 'vnpay') {
            $order->payment_method = 'vnpay';
            $order->save();
        }

        $paymentUrl = $this->buildVnpayUrl($request, $order, (float)$order->total_amount);

        return response()->json([
            'success' => true,
            'payment_url' => $paymentUrl
        ]);
    }

    private function buildVnpayUrl(Request $request, Order $order, float $totalAmount): string
    {
        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = trim((string) config('vnpay.hash_secret'));
        $vnp_Url = config('vnpay.url');
        $vnp_Returnurl = config('vnpay.return_url');

        // [CẢI TIẾN] Đánh dấu tất cả các giao dịch VNPay đang chờ trước đó của đơn hàng này là Thất bại
        // Vì người dùng đang thực hiện một lượt thanh toán mới (hoặc bấm lại nút thanh toán)
        Payment::where('order_id', $order->id)
            ->where('payment_method', 'vnpay')
            ->where('status', 'pending')
            ->update(['status' => 'failed']);

        // [GIẢI QUYẾT TRÙNG MÃ] - Tạo mã giao dịch duy nhất cho mỗi lần bấm
        $txnRef = 'TXN_' . $order->order_code . '_' . time();

        // Lưu vào bảng payments để tra cứu sau này
        Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $txnRef,
            'amount' => $totalAmount,
            'payment_method' => 'vnpay',
            'status' => 'pending'
        ]);

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => (int) round($totalAmount * 100),
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $request->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan don hang " . $order->order_code,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $txnRef, // Gửi TXN duy nhất
        ];

        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        return $vnp_Url . "?" . $hashData . '&vnp_SecureHash=' . $secureHash;
    }

    private function markOrderAsPaid(Order $order, $transactionId): void
    {
        // Chống gửi mail trùng lặp hoặc cập nhật lại khi đã paid
        if ($order->payment_status === 'paid') {
            return;
        }

        $order->payment_status = 'paid';
        $order->transaction_id = $transactionId;
        $order->save();
        
        // KHÔNG gọi gửi mail ở đây để đảm bảo quy tắc "Duy nhất 1 email"
        // Mail đã được gửi lúc đặt hàng (User) hoặc lúc callback success (Guest)
    }

    private function reverseStock(Order $order): void
    {
        $items = $order->orderItems;
        foreach ($items as $item) {
            $attributes = json_decode((string)$item->variant_info, true);
            $variantQuery = ProductVariant::where('product_id', $item->product_id);
            if ($attributes) {
                foreach ($attributes as $key => $val) {
                    $variantQuery->where("variant_attributes->$key", $val);
                }
            }
            $variant = $variantQuery->first();
            if ($variant) {
                $variant->increment('quantity', $item->quantity);
            }
        }
    }

    private function sendOrderConfirmationMail(Order $order): void
    {
        try {
            if (!$order->user_id && $order->session_id) {
                // Khách vãng lai → dẫn đến trang tra cứu công khai
                $trackingLink = rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/user/orders?session_id=' . $order->session_id;
            } else {
                // Thành viên đã đăng nhập → dẫn đến trang đơn hàng của tài khoản
                $trackingLink = rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/user/orders';
            }

            $items = $order->relationLoaded('orderItems') ? $order->orderItems : $order->orderItems()->get();
            \Illuminate\Support\Facades\Mail::send('order_confirm', [
                'order'        => $order,
                'items'        => $items,
                'trackingLink' => $trackingLink
            ], function ($message) use ($order) {
                $message->to($order->email, $order->full_name)
                        ->subject('Xác nhận đơn hàng #' . $order->order_code);
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gửi mail thất bại: " . $e->getMessage());
        }
    }
}
