<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderUserController extends Controller
{
    public function index(Request $request)
    {
        // Nhận diện User qua Token (Sanctum)
        $user = $request->user('sanctum');

        // Khởi tạo query
        $query = Order::with('orderItems');

        if ($user) {
            // TRƯỜNG HỢP 1: Đã đăng nhập
            // -> CHỈ lấy theo user_id
            $query->where('user_id', $user->id);
        } else {
            // TRƯỜNG HỢP 2: Khách vãng lai
            $sessionId = $request->input('session_id');

            if ($sessionId) {
                // -> Lấy theo session_id VÀ user_id phải bằng NULL
                // (Đảm bảo đây là đơn của khách, không phải đơn của thành viên)
                $query->where('session_id', $sessionId)
                      ->whereNull('user_id'); 
            } else {
                // Không có thông tin định danh -> Trả về rỗng
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'current_page' => 1,
                        'data' => [],
                        'total' => 0
                    ]
                ]);
            }
        }

        // 3. Xử lý lọc theo trạng thái (status)
        if ($request->has('status') && $request->status != 'all') {
            $query->where('order_status', $request->status);
        }

        // 4. Sắp xếp đơn mới nhất lên đầu
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        // 5. Trả về kết quả
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * Hàm lấy chi tiết một đơn hàng cụ thể
     */
    public function show(Request $request, $orderCode)
    {
        $user = $request->user('sanctum');

        $query = Order::with(['orderItems', 'couponUsages', 'payments']) // Thêm payments
            ->where('order_code', $orderCode);

        // Bảo mật: Chỉ cho phép xem nếu đúng chủ sở hữu
        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $sessionId = $request->input('session_id');
            if ($sessionId) {
                $query->where('session_id', $sessionId);
            } else {
                // Nếu không có session_id, có thể khách đang dùng link tra cứu trực tiếp
                // -> Lúc này sẽ do hàm lookup xử lý
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }
        }

        $order = $query->first();

        if ($order) {
            \App\Models\Payment::where('order_id', $order->id)
                ->where('status', 'pending')
                ->where('created_at', '<', now()->subMinutes(1))
                ->update(['status' => 'failed']);
            $order->load('payments');
        }

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    /**
     * Tra cứu đơn hàng dành cho khách vãng lai (không cần login/session)
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string',
            'phone' => 'required|string',
        ]);

        $order = Order::with(['orderItems', 'couponUsages', 'payments'])
            ->where('order_code', $request->order_code)
            ->where('phone', $request->phone)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Thông tin đơn hàng hoặc số điện thoại không chính xác'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel(Request $request, $orderCode)
    {
        $user = $request->user('sanctum');
        
        $query = Order::where('order_code', $orderCode);

        // Bảo mật: Kiểm tra quyền sở hữu
        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $sessionId = $request->input('session_id');
            if ($sessionId) {
                $query->where('session_id', $sessionId);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }
        }

        $order = $query->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Chỉ cho phép hủy khi đang ở trạng thái 'pending' (Chờ xác nhận)
        if ($order->order_status !== 'pending') {
            return response()->json([
                'status' => 'error', 
                'message' => 'Đơn hàng này không thể hủy do đã được xử lý hoặc đã giao.'
            ], 400);
        }

        // Thực hiện hủy
        $order->order_status = 'cancelled';
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Hủy đơn hàng thành công',
            'data' => $order
        ]);
    }
}