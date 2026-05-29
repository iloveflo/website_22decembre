<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class StatisticsController extends Controller
{

    private function getDateRange(Request $request)
    {
        $period = $request->input('period', 'this_month');
        switch ($period) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'this_week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'this_quarter':
                return [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()];
            case 'this_year':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            case 'custom':
                $start = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
                $end = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
                return [$start, $end];
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }

    private function getPreviousDateRange(Request $request)
    {
        $period = $request->input('period', 'this_month');
        switch ($period) {
            case 'today':
                return [Carbon::yesterday(), Carbon::yesterday()->endOfDay()];
            case 'this_week':
                return [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()];
            case 'this_month':
                return [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];
            case 'this_quarter':
                return [Carbon::now()->subQuarter()->startOfQuarter(), Carbon::now()->subQuarter()->endOfQuarter()];
            case 'this_year':
                return [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()];
            case 'custom':
                $start = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
                $end = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
                $diffInDays = $start->diffInDays($end) + 1;
                return [(clone $start)->subDays($diffInDays), (clone $start)->subSecond()];
            default:
                return [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];
        }
    }

    public function getDashboardStats(Request $request)
    {
        $user = $request->user();
        $data = [];

        // 12 đơn hàng mới cần xử lý (pending orders)
        $data['pending_orders'] = Order::where('order_status', 'pending')->count();

        if ($user->role === 'admin') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            
            $revenue = Order::whereBetween('created_at', [$startDate, $endDate])
                            ->where('order_status', 'completed')
                            ->sum('total_amount');
                            
            $totalCustomers = User::where('role', 'user')->count();
            
            // Tính tăng trưởng tháng trước
            $prevStart = Carbon::now()->subMonth()->startOfMonth();
            $prevEnd = Carbon::now()->subMonth()->endOfMonth();
            
            $prevTotalCustomers = User::where('role', 'user')->where('created_at', '<=', $prevEnd)->count();
            $customerGrowth = $prevTotalCustomers > 0 ? (($totalCustomers - $prevTotalCustomers) / $prevTotalCustomers) * 100 : ($totalCustomers > 0 ? 100 : 0);
            
            $prevRevenue = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('order_status', 'completed')->sum('total_amount');
            $revenueGrowth = $prevRevenue > 0 ? (($revenue - $prevRevenue) / $prevRevenue) * 100 : ($revenue > 0 ? 100 : 0);
            
            // Format number to 'M' (Millions)
            $formattedRevenue = $revenue >= 1000000 ? round($revenue / 1000000, 1) . 'M' : number_format($revenue, 0, ',', '.');
            
            $data['adminStats'] = [
                'total_customers' => number_format($totalCustomers, 0, ',', '.'),
                'customer_growth' => round($customerGrowth, 1),
                'revenue' => $formattedRevenue,
                'revenue_growth' => round($revenueGrowth, 1)
            ];
            
        } else {
            // Staff stats
            $newOrders = Order::whereDate('created_at', Carbon::today())->count();
            $totalProducts = Product::count();
            
            $prevNewOrders = Order::whereDate('created_at', Carbon::yesterday())->count();
            $orderGrowth = $newOrders - $prevNewOrders;
            
            $data['staffStats'] = [
                'new_orders' => $newOrders,
                'order_growth' => $orderGrowth,
                'total_products' => $totalProducts
            ];
        }

        // Recent activities (Mix of new orders and new users, take top 3)
        $activities = [];
        
        $orders = Order::latest()->take(5)->get();
        foreach ($orders as $order) {
            $activities[] = [
                'type' => 'order',
                'desc' => "Đơn hàng mới #{$order->order_code}",
                'created_at' => $order->created_at
            ];
        }

        if ($user->role === 'admin') {
            $users = User::where('role', 'user')->latest()->take(5)->get();
            foreach ($users as $u) {
                $activities[] = [
                    'type' => 'user',
                    'desc' => "Khách hàng {$u->full_name} đăng ký tài khoản",
                    'created_at' => $u->created_at
                ];
            }
        }

        usort($activities, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        // Take top 4 recent and format time
        $recent = array_slice($activities, 0, 4);
        foreach ($recent as &$act) {
            // Dùng Carbon diffForHumans. Nhớ thiết lập setLocale('vi') nếu muốn tiếng Việt, 
            // hoặc đơn giản là format ngày giờ:
            if ($act['created_at']->isToday()) {
                $act['time'] = $act['created_at']->format('H:i');
            } elseif ($act['created_at']->isYesterday()) {
                $act['time'] = 'Hôm qua';
            } else {
                $act['time'] = $act['created_at']->format('d/m');
            }
            unset($act['created_at']);
        }

        $data['recentActivities'] = $recent;

        return response()->json($data);
    }

    public function getOverview(Request $request)
    {
        $period = $request->query('period', 'this_month');
        [$startDate, $endDate] = $this->getDateRange($request);
        [$prevStart, $prevEnd] = $this->getPreviousDateRange($request);
        
        $cacheKey = 'stats_overview_' . $period . '_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd');

        $data = Cache::remember($cacheKey, 30, function () use ($startDate, $endDate, $prevStart, $prevEnd) {
            // CURRENT PERIOD
            $baseQuery = Order::whereBetween('created_at', [$startDate, $endDate]);
            $revenue = (clone $baseQuery)->where('order_status', 'completed')->sum('total_amount');
            $orderCount = (clone $baseQuery)->count();
            
            // Đếm số lượng khách hàng thực tế đã mua hàng (Active Customers)
            // Tính cả khách vãng lai (qua email) và thành viên
            $activeCustomers = (clone $baseQuery)->distinct('email')->count('email');
            
            $avgOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

            // PREVIOUS PERIOD
            $prevBaseQuery = Order::whereBetween('created_at', [$prevStart, $prevEnd]);
            $prevRevenue = (clone $prevBaseQuery)->where('order_status', 'completed')->sum('total_amount');
            $prevOrderCount = (clone $prevBaseQuery)->count();
            $prevActiveCustomers = (clone $prevBaseQuery)->distinct('email')->count('email');
            $prevAvgOrderValue = $prevOrderCount > 0 ? $prevRevenue / $prevOrderCount : 0;

            // Calculate Growth
            $calculateGrowth = function ($current, $previous) {
                if ($previous == 0) {
                    return $current > 0 ? 100 : 0;
                }
                return (($current - $previous) / $previous) * 100;
            };

            return [
                'revenue' => (float) $revenue,
                'orderCount' => (int) $orderCount,
                'activeCustomers' => (int) $activeCustomers,
                'averageOrderValue' => (float) $avgOrderValue,
                'revenueGrowth' => $calculateGrowth($revenue, $prevRevenue),
                'orderCountGrowth' => $calculateGrowth($orderCount, $prevOrderCount),
                'activeCustomersGrowth' => $calculateGrowth($activeCustomers, $prevActiveCustomers),
                'avgOrderValueGrowth' => $calculateGrowth($avgOrderValue, $prevAvgOrderValue),
            ];
        });

        return response()->json($data);
    }

    public function getRevenueOverTime(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($data);
    }

    public function getSalesByCategory(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        // Join bảng để lấy tên category từ product_id trong order_items
        $data = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.order_status', 'completed')
            ->select(
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue') // Thêm doanh thu theo danh mục
            )
            ->groupBy('categories.name')
            ->get();

        return response()->json($data);
    }

    public function getOrderStatusDistribution(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);

        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->get();

        return response()->json($data);
    }

    public function getTopSellingProducts(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $limit = $request->input('limit', 5);

        $stats = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.order_status', 'completed')
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue') 
            )
            ->groupBy('order_items.product_id')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();

        $productIds = $stats->pluck('product_id')->toArray();
        $products = Product::with('mainImage')->whereIn('id', $productIds)->get()->keyBy('id');

        $result = $stats->map(function ($item) use ($products) {
            $product = $products->get($item->product_id);
            if (!$product) return null;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->main_image_url,
                'price' => $product->price,
                'total_sold' => (int) $item->total_sold,
                'total_revenue' => (float) $item->total_revenue,
            ];
        })->filter()->values();

        return response()->json($result);
    }

    public function getPaymentMethodsDistribution(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('count(*) as count'))
            ->groupBy('payment_method')
            ->get();
            
        return response()->json($data);
    }

    public function getTopCustomers(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        
        $data = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_status', 'completed')
            ->select('full_name', 'email', DB::raw('SUM(total_amount) as total_spent'), DB::raw('count(*) as orders_count'))
            ->groupBy('full_name', 'email')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();
            
        return response()->json($data);
    }

    public function getRecentActivities()
    {
        // 1. Đơn hàng gần đây
        $recentOrders = Order::with('user:id,full_name') // Chỉ lấy id và tên user để nhẹ payload
            ->select('id', 'user_id', 'total_amount', 'order_status', 'created_at', 'order_code')
            ->latest()
            ->take(5)
            ->get();

        // 2. Sản phẩm/Biến thể sắp hết hàng (Low Stock)
        // Logic: Lấy từng biến thể có quantity < 10
        $lowStockVariants = \DB::table('product_variants')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'product_variants.id',
                'products.name as product_name',
                'product_variants.variant_attributes',
                'product_variants.quantity as total_stock'
            )
            ->where('product_variants.quantity', '<', 10)
            ->orderBy('product_variants.quantity', 'asc')
            ->take(5)
            ->get();

        $lowStockProducts = $lowStockVariants->map(function ($item) {
            $attrs = json_decode($item->variant_attributes, true);
            $attrString = '';
            if (is_array($attrs) && !empty($attrs)) {
                $attrString = implode(' - ', array_values($attrs));
            }
            return [
                'id' => $item->id,
                'name' => $item->product_name . ($attrString ? ' (' . $attrString . ')' : ''),
                'total_stock' => $item->total_stock
            ];
        });

        return response()->json([
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    public function exportReport(Request $request)
    {
        // 1. Lấy dữ liệu
        [$startDate, $endDate] = $this->getDateRange($request);
        
        // Luôn quy ra từ ngày đến ngày để chuyên nghiệp hơn
        $periodText = $startDate->format('d/m/Y') . ' — ' . $endDate->format('d/m/Y');

        // Overview
        $baseQuery = Order::whereBetween('created_at', [$startDate, $endDate]);
        $revenue = (clone $baseQuery)->where('order_status', 'completed')->sum('total_amount');
        $orderCount = (clone $baseQuery)->count();
        $activeCustomers = (clone $baseQuery)->distinct('email')->count('email');
        $avgOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

        $overview = [
            'revenue' => $revenue,
            'orderCount' => $orderCount,
            'activeCustomers' => $activeCustomers,
            'averageOrderValue' => $avgOrderValue
        ];

        // Top Products
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.order_status', 'completed')
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('order_items.product_name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Top Customers
        $topCustomers = (clone $baseQuery)
            ->select('email', 'full_name', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_spent'))
            ->groupBy('email', 'full_name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        // Payment Methods
        $paymentMethods = (clone $baseQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Recent Orders
        $recentOrders = Order::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 2. Load View và render PDF
        $pdf = Pdf::loadView('statistics_pdf', [
            'period_text' => $periodText,
            'overview' => $overview,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'paymentMethods' => $paymentMethods,
            'recentOrders' => $recentOrders,
            'charts' => $request->input('charts', []),
            'exported_by' => auth()->user()->full_name ?? 'Quản trị viên',
            'exported_at' => now()->format('H:i:s d/m/Y')
        ]);

        // 3. Trả về file stream để download
        return $pdf->download('Bao_Cao_Kinh_Doanh_' . now()->format('dmY') . '.pdf');
    }
}
