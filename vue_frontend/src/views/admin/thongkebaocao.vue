<template>
  <div class="stats-dashboard">
    <div class="dashboard-header">
      <div class="header-content">
        <h1>Thống kê & Báo cáo</h1>
        <p>Tổng quan tình hình kinh doanh của cửa hàng</p>
      </div>
      <div class="header-actions">
        <div class="date-filter">
          <label>Thời gian:</label>
          <div class="filter-controls">
            <select v-model="period" @change="fetchData">
              <option value="today">Hôm nay</option>
              <option value="this_week">Tuần này</option>
              <option value="this_month">Tháng này</option>
              <option value="this_quarter">Quý này</option>
              <option value="this_year">Năm này</option>
              <option value="custom">Tùy chọn...</option>
            </select>
            <div v-if="period === 'custom'" class="custom-range">
              <input type="date" v-model="customStartDate" @change="fetchData">
              <span>đến</span>
              <input type="date" v-model="customEndDate" @change="fetchData">
            </div>
          </div>
        </div>
        <button class="btn-export" @click="exportReport" :disabled="loading">
          <i class="fas fa-download"></i> Xuất Báo Cáo
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <div v-else class="dashboard-content">
      <div class="kpi-grid">
        <div class="kpi-card revenue" :class="{ 'has-error': errors.overview }">
          <div class="kpi-icon"><i class="fas fa-coins"></i></div>
          <div class="kpi-info">
            <h3>Doanh Thu <i v-if="errors.overview" class="fas fa-exclamation-triangle text-warning" title="Lỗi tải dữ liệu"></i></h3>
            <p class="kpi-value">{{ formatCurrency(stats.overview.revenue) }}</p>
            <div class="kpi-growth" :class="stats.overview.revenueGrowth >= 0 ? 'up' : 'down'">
                <i :class="stats.overview.revenueGrowth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                <span>{{ formatGrowth(stats.overview.revenueGrowth) }}</span>
                <small>so với kì trước</small>
            </div>
          </div>
        </div>
        <div class="kpi-card orders" :class="{ 'has-error': errors.overview }">
          <div class="kpi-icon"><i class="fas fa-shopping-bag"></i></div>
          <div class="kpi-info">
            <h3>Đơn Hàng <i v-if="errors.overview" class="fas fa-exclamation-triangle text-warning"></i></h3>
            <p class="kpi-value">{{ stats.overview.orderCount }}</p>
            <div class="kpi-growth" :class="stats.overview.orderCountGrowth >= 0 ? 'up' : 'down'">
                <i :class="stats.overview.orderCountGrowth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                <span>{{ formatGrowth(stats.overview.orderCountGrowth) }}</span>
                <small>so với kì trước</small>
            </div>
          </div>
        </div>
        <div class="kpi-card customers" :class="{ 'has-error': errors.overview }">
          <div class="kpi-icon"><i class="fas fa-users"></i></div>
          <div class="kpi-info">
            <h3>Khách Mua Hàng <i v-if="errors.overview" class="fas fa-exclamation-triangle text-warning"></i></h3>
            <p class="kpi-value">{{ stats.overview.activeCustomers }}</p>
            <div class="kpi-growth" :class="stats.overview.activeCustomersGrowth >= 0 ? 'up' : 'down'">
                <i :class="stats.overview.activeCustomersGrowth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                <span>{{ formatGrowth(stats.overview.activeCustomersGrowth) }}</span>
                <small>so với kì trước</small>
            </div>
          </div>
        </div>
        <div class="kpi-card avg-order" :class="{ 'has-error': errors.overview }">
          <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
          <div class="kpi-info">
            <h3>TB Đơn Hàng <i v-if="errors.overview" class="fas fa-exclamation-triangle text-warning"></i></h3>
            <p class="kpi-value">{{ formatCurrency(stats.overview.averageOrderValue) }}</p>
            <div class="kpi-growth" :class="stats.overview.avgOrderValueGrowth >= 0 ? 'up' : 'down'">
                <i :class="stats.overview.avgOrderValueGrowth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                <span>{{ formatGrowth(stats.overview.avgOrderValueGrowth) }}</span>
                <small>so với kì trước</small>
            </div>
          </div>
        </div>
      </div>

      <div class="charts-grid-top">
        <div class="chart-card main-chart">
          <h3>Biểu Đồ Doanh Thu <i v-if="errors.revenue" class="fas fa-exclamation-triangle text-warning" title="Lỗi tải biểu đồ"></i></h3>
          <div class="chart-wrapper">
            <Line v-if="chartData.revenue" ref="revenueChartRef" :data="chartData.revenue" :options="chartOptions.revenue" />
            <div v-else class="error-placeholder">
              <i class="fas fa-chart-line"></i>
              <p>Không có dữ liệu biểu đồ</p>
            </div>
          </div>
        </div>
        <div class="chart-card side-chart">
          <h3>Trạng Thái Đơn Hàng <i v-if="errors.status" class="fas fa-exclamation-triangle text-warning"></i></h3>
          <div class="chart-wrapper">
            <Doughnut v-if="chartData.status" ref="statusChartRef" :data="chartData.status" :options="chartOptions.status" />
            <div v-else class="error-placeholder">
              <i class="fas fa-chart-pie"></i>
              <p>Không có dữ liệu trạng thái</p>
            </div>
          </div>
        </div>
      </div>

      <div class="charts-grid-top second-row">
        <div class="chart-card side-chart">
          <h3>Phương Thức Thanh Toán <i v-if="errors.paymentMethod" class="fas fa-exclamation-triangle text-warning"></i></h3>
          <div class="chart-wrapper">
            <Doughnut v-if="chartData.paymentMethod" ref="paymentChartRef" :data="chartData.paymentMethod" :options="chartOptions.paymentMethod" />
            <div v-else class="error-placeholder">
              <i class="fas fa-credit-card"></i>
              <p>Không có dữ liệu thanh toán</p>
            </div>
          </div>
        </div>
        <div class="chart-card main-chart">
          <h3>Doanh Thu Theo Danh Mục <i v-if="errors.category" class="fas fa-exclamation-triangle text-warning"></i></h3>
           <div class="chart-wrapper">
            <Bar v-if="chartData.category" ref="categoryChartRef" :data="chartData.category" :options="chartOptions.category" />
            <div v-else class="error-placeholder">
              <i class="fas fa-chart-bar"></i>
              <p>Không có dữ liệu danh mục</p>
            </div>
          </div>
        </div>
      </div>

      <div class="data-tables-grid">
        <div class="table-card">
          <div class="card-header">
            <h3>Top 5 Khách Hàng Thân Thiết <i v-if="errors.topCustomers" class="fas fa-exclamation-triangle text-warning"></i></h3>
          </div>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Khách hàng</th>
                  <th>Số đơn</th>
                  <th>Tổng chi tiêu</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in stats.topCustomers" :key="customer.email">
                  <td>
                    <div class="customer-info">
                        <strong>{{ customer.full_name }}</strong>
                        <small class="text-muted d-block">{{ customer.email }}</small>
                    </div>
                  </td>
                  <td>{{ customer.orders_count }}</td>
                  <td><strong>{{ formatCurrency(customer.total_spent) }}</strong></td>
                </tr>
                <tr v-if="stats.topCustomers.length === 0">
                   <td colspan="3" class="text-center">Chưa có dữ liệu</td>
                </tr>
              </tbody>
            </table>
        </div>
      </div>

        <div class="table-card">
          <div class="card-header">
            <h3>Cảnh Báo & Hoạt Động <i v-if="errors.recentActivities" class="fas fa-exclamation-triangle text-warning"></i></h3>
          </div>
          <div class="activity-list">
             <div v-if="stats.lowStock.length > 0" class="stock-alert warning">
                <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="alert-content">
                  <h4>Sắp hết hàng</h4>
                  <p>Có {{ stats.lowStock.length }} phân loại hàng sắp hết trong kho</p>
                  <ul class="mini-stock-list">
                    <li v-for="item in stats.lowStock" :key="'stock-'+item.id">
                      <span class="item-name">{{ item.name }}</span>
                      <span class="item-stock">Còn {{ item.total_stock }}</span>
                    </li>
                  </ul>
                </div>
             </div>
             <div v-else class="stock-alert success">
                <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                <div class="alert-content">
                  <h4>Kho hàng ổn định</h4>
                  <p>Tất cả sản phẩm đều có đủ lượng tồn kho</p>
                </div>
             </div>
             
             <div class="recent-orders-timeline">
                <div class="timeline-header">
                  <h4>Đơn hàng mới nhất</h4>
                </div>
                <div class="timeline-items">
                  <div v-for="order in stats.recentOrders" :key="'order-'+order.id" class="timeline-item">
                    <div class="item-marker"></div>
                    <div class="item-content">
                      <div class="item-main">
                        <span class="order-code">#{{ order.order_code || order.id }}</span>
                        <span class="order-customer">{{ order.user ? order.user.full_name : 'Khách vãng lai' }}</span>
                      </div>
                      <div class="item-details">
                        <span class="order-amount">{{ formatCurrency(order.total_amount) }}</span>
                        <span :class="['status-badge', order.order_status]">{{ translateStatus(order.order_status) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="stats.recentOrders.length === 0" class="text-center p-4">
                  <p class="text-muted">Chưa có đơn hàng mới</p>
                </div>
             </div>
          </div>
        </div>
      </div>

      <div class="full-width-table margin-top">
         <div class="table-card">
          <div class="card-header">
            <h3>Sản Phẩm Bán Chạy <i v-if="errors.topProducts" class="fas fa-exclamation-triangle text-warning"></i></h3>
          </div>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Sản phẩm</th>
                  <th>Giá hiện tại</th>
                  <th>Đã bán</th>
                  <th>Tổng doanh thu</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in stats.topProducts" :key="product.id">
                  <td class="product-cell">
                    <img :src="product.image || '/images/placeholder.png'" alt="img" class="product-thumb">
                    <div class="product-name-col">
                        <span>{{ product.name }}</span>
                    </div>
                  </td>
                  <td>{{ formatCurrency(product.price) }}</td>
                  <td>{{ product.total_sold }}</td>
                  <td>{{ formatCurrency(product.total_revenue) }}</td>
                </tr>
                <tr v-if="stats.topProducts.length === 0">
                  <td colspan="4" class="text-center">Chưa có dữ liệu</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line, Bar, Doughnut } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
)

const loading = ref(true);
const period = ref('this_month');
const customStartDate = ref(new Date().toISOString().substring(0, 10));
const customEndDate = ref(new Date().toISOString().substring(0, 10));

// Refs for charts
const revenueChartRef = ref(null);
const statusChartRef = ref(null);
const paymentChartRef = ref(null);
const categoryChartRef = ref(null);

const stats = reactive({
  overview: {
    revenue: 0,
    orderCount: 0,
    activeCustomers: 0,
    averageOrderValue: 0,
    revenueGrowth: 0,
    orderCountGrowth: 0,
    activeCustomersGrowth: 0,
    avgOrderValueGrowth: 0
  },
  topProducts: [],
  topCustomers: [],
  recentOrders: [],
  lowStock: []
});

const chartData = reactive({
  revenue: null,
  status: null,
  category: null,
  paymentMethod: null
});

const errors = reactive({
  overview: false,
  revenue: false,
  status: false,
  paymentMethod: false,
  category: false,
  topProducts: false,
  topCustomers: false,
  recentActivities: false
});

const chartOptions = {
  revenue: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      title: { display: true, text: 'Doanh thu theo thời gian' }
    },
    scales: {
        y: { beginAtZero: true }
    }
  },
  status: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom' }
    }
  },
  category: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
       legend: { display: false }
    }
  },
  paymentMethod: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

const formatGrowth = (value) => {
    if (!value && value !== 0) return '0%';
    const prefix = value > 0 ? '+' : '';
    return `${prefix}${value.toFixed(1)}%`;
};

const translateStatus = (status) => {
    const map = {
        'pending': 'Chờ xử lý',
        'processing': 'Đang xử lý',
        'shipping': 'Đang giao',
        'delivered': 'Đã giao',
        'cancelled': 'Đã hủy',
        'returned': 'Hoàn trả'
    };
    return map[status] || status;
};

const fetchData = async () => {
  loading.value = true;
  Object.keys(errors).forEach(key => errors[key] = false);
  
  try {
    const params = { period: period.value };
    if (period.value === 'custom') {
        params.start_date = customStartDate.value;
        params.end_date = customEndDate.value;
    }
    
    const results = await Promise.allSettled([
      axios.get('/admin/statistics/overview', { params }),
      axios.get('/admin/statistics/revenue-over-time', { params }),
      axios.get('/admin/statistics/order-status-distribution', { params }),
      axios.get('/admin/statistics/payment-methods-distribution', { params }),
      axios.get('/admin/statistics/sales-by-category', { params }),
      axios.get('/admin/statistics/top-selling-products', { params }),
      axios.get('/admin/statistics/top-customers', { params }),
      axios.get('/admin/statistics/recent-activities') 
    ]);

    // 0. Overview
    if (results[0].status === 'fulfilled') {
      stats.overview = results[0].value.data;
    } else {
      errors.overview = true;
      console.error("Lỗi tải overview:", results[0].reason);
    }

    // 1. Revenue Chart
    if (results[1].status === 'fulfilled') {
      const data = results[1].value.data;
      chartData.revenue = {
        labels: data.map(item => item.date),
        datasets: [{
          label: 'Doanh thu',
          backgroundColor: '#10b981',
          borderColor: '#10b981',
          data: data.map(item => item.total),
          tension: 0.3
        }]
      };
    } else {
      errors.revenue = true;
      chartData.revenue = null;
      console.error("Lỗi tải biểu đồ doanh thu:", results[1].reason);
    }

    // 2. Status Chart
    if (results[2].status === 'fulfilled') {
      const data = results[2].value.data;
      const statusColors = {
          'pending': '#fbbf24', 
          'processing': '#3b82f6', 
          'shipping': '#8b5cf6', 
          'delivered': '#10b981', 
          'cancelled': '#ef4444',
          'returned': '#6b7280'
      };
      chartData.status = {
        labels: data.map(item => translateStatus(item.order_status)), 
        datasets: [{
          backgroundColor: data.map(item => statusColors[item.order_status] || '#ccc'),
          data: data.map(item => item.count)
        }]
      };
    } else {
      errors.status = true;
      chartData.status = null;
      console.error("Lỗi tải trạng thái đơn hàng:", results[2].reason);
    }

    // 2.1 Payment Method Chart
    if (results[3].status === 'fulfilled') {
      const data = results[3].value.data;
      const methodColors = {
          'cod': '#94a3b8', 
          'vnpay': '#0ea5e9', 
          'momo': '#d946ef', 
          'sepay': '#10b981'
      };
      const methodNames = {
          'cod': 'Tiền mặt (COD)',
          'vnpay': 'VNPAY',
          'momo': 'MoMo',
          'sepay': 'Chuyển khoản (Sepay)'
      };
      chartData.paymentMethod = {
        labels: data.map(item => methodNames[item.payment_method] || item.payment_method), 
        datasets: [{
          backgroundColor: data.map(item => methodColors[item.payment_method] || '#ccc'),
          data: data.map(item => item.count)
        }]
      };
    } else {
      errors.paymentMethod = true;
      chartData.paymentMethod = null;
      console.error("Lỗi tải phương thức thanh toán:", results[3].reason);
    }

    // 3. Category Chart
    if (results[4].status === 'fulfilled') {
      const data = results[4].value.data;
      chartData.category = {
        labels: data.map(item => item.name),
        datasets: [{
          label: 'Số lượng bán',
          backgroundColor: '#6366f1',
          data: data.map(item => item.total_quantity)
        }]
      };
    } else {
      errors.category = true;
      chartData.category = null;
      console.error("Lỗi tải doanh thu theo danh mục:", results[4].reason);
    }

    // 4. Top Products
    if (results[5].status === 'fulfilled') {
      stats.topProducts = results[5].value.data;
    } else {
      errors.topProducts = true;
      stats.topProducts = [];
      console.error("Lỗi tải sản phẩm bán chạy:", results[5].reason);
    }

    // 4.1 Top Customers
    if (results[6].status === 'fulfilled') {
      stats.topCustomers = results[6].value.data;
    } else {
      errors.topCustomers = true;
      stats.topCustomers = [];
      console.error("Lỗi tải khách hàng tiêu biểu:", results[6].reason);
    }

    // 5. Recent Activities
    if (results[7].status === 'fulfilled') {
      stats.recentOrders = results[7].value.data.recentOrders;
      stats.lowStock = results[7].value.data.lowStockProducts;
    } else {
      errors.recentActivities = true;
      stats.recentOrders = [];
      stats.lowStock = [];
      console.error("Lỗi tải hoạt động gần đây:", results[7].reason);
    }

  } catch (error) {
    console.error("Lỗi hệ thống:", error);
  } finally {
    loading.value = false;
  }
};

const exportReport = async () => {
  loading.value = true;
  try {
    const params = { period: period.value };
    if (period.value === 'custom') {
        params.start_date = customStartDate.value;
        params.end_date = customEndDate.value;
    }
    
    // Gọi API với responseType là 'blob' (quan trọng để tải file)
    // Chụp biểu đồ dưới dạng Base64
    const chartImages = {
        revenue: revenueChartRef.value?.chart?.toBase64Image() || null,
        status: statusChartRef.value?.chart?.toBase64Image() || null,
        payment: paymentChartRef.value?.chart?.toBase64Image() || null,
        category: categoryChartRef.value?.chart?.toBase64Image() || null
    };

    const response = await axios.post('/admin/statistics/export', { 
        ...params,
        charts: chartImages
    }, { 
        responseType: 'blob' 
    });

    // Tạo URL ảo cho file blob
    const url = window.URL.createObjectURL(new Blob([response.data]));
    
    // Tạo thẻ a ảo để kích hoạt download
    const link = document.createElement('a');
    link.href = url;
    
    // Đặt tên file (lấy từ header server hoặc đặt cứng)
    const fileName = `Bao_Cao_${period.value}_${new Date().toISOString().slice(0,10)}.pdf`;
    link.setAttribute('download', fileName);
    
    document.body.appendChild(link);
    link.click();
    
    // Dọn dẹp
    link.remove();
    window.URL.revokeObjectURL(url);
    
    console.log("Xuất báo cáo thành công!");

  } catch (error) {
    console.error("Lỗi xuất báo cáo:", error);
    alert("Có lỗi xảy ra khi xuất báo cáo. Vui lòng thử lại.");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.stats-dashboard {
  padding: 24px;
  background-color: #f3f4f6; /* Light gray bg */
  min-height: 100vh;
  
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  align-items: flex-start;
  margin-bottom: 32px;
}

.header-content h1 {
  font-size: 28px;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 4px 0;
  letter-spacing: -0.025em;
}

.header-content p {
  color: #64748b;
  font-size: 15px;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 16px;
  align-items: flex-end;
}

.date-filter {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.date-filter label {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    margin: 0;
}

.filter-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.custom-range {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    padding: 4px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
}

.custom-range input {
    border: none;
    font-size: 14px;
    color: #374151;
    outline: none;
    padding: 4px;
}

.custom-range span {
    color: #9ca3af;
    font-size: 12px;
}

.date-filter select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  background: white;
  font-size: 14px;
  cursor: pointer;
  min-width: 140px;
}

.btn-export {
  background: #6366f1;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);
}

.btn-export:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-1px);
  box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
}

.btn-export:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* KPI Cards */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.kpi-card {
  background: white;
  padding: 24px;
  border-radius: 20px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  display: flex;
  align-items: center;
  gap: 20px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid #f1f5f9;
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  border-color: #e2e8f0;
}

.kpi-icon {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
}

.kpi-card.revenue .kpi-icon { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); color: #059669; }
.kpi-card.orders .kpi-icon { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color: #2563eb; }
.kpi-card.customers .kpi-icon { background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); color: #7c3aed; }
.kpi-card.avg-order .kpi-icon { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); color: #ea580c; }

.kpi-info h3 { font-size: 13px; color: #64748b; font-weight: 600; margin: 0; text-transform: uppercase; letter-spacing: 0.025em; }
.kpi-value { font-size: 26px; font-weight: 800; color: #0f172a; margin: 6px 0; letter-spacing: -0.025em; }

.kpi-growth {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
}
.kpi-growth.up { color: #10b981; }
.kpi-growth.down { color: #ef4444; }
.kpi-growth small {
    color: #9ca3af;
    font-weight: 400;
    margin-left: 4px;
}

/* Charts */
.charts-grid-top {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

.charts-grid-bottom {
  margin-bottom: 32px;
}

.chart-card {
  background: white;
  padding: 24px;
  border-radius: 20px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
  border: 1px solid #f1f5f9;
}

.chart-card h3 {
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 24px;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 8px;
}

.chart-wrapper {
  position: relative;
  height: 300px;
  width: 100%;
}

/* Data Tables */
.data-tables-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.charts-grid-top.second-row {
  grid-template-columns: 1fr 2fr;
  margin-bottom: 24px;
}

.margin-top {
    margin-top: 24px;
}

.customer-info strong {
    display: block;
    font-size: 14px;
}
.customer-info small {
    font-size: 11px;
}

.table-card {
    background: white;
    padding: 28px;
    border-radius: 20px;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    border: 1px solid #f1f5f9;
}

.table-card h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.card-header {
    margin-bottom: 24px;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #f8fafc;
    border-radius: 8px;
}

td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
    color: #334155;
}

tr:hover td {
    background-color: #fcfdfe;
}

.product-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.product-thumb {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    background: #f3f4f6;
}

/* Stock Alerts & Activity Timeline */
.stock-alert {
    display: flex;
    gap: 16px;
    padding: 16px;
    border-radius: 14px;
    margin-bottom: 24px;
}

.stock-alert.warning {
    background: #fffbeb;
    border: 1px solid #fef3c7;
}

.stock-alert.success {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
}

.alert-icon {
    font-size: 20px;
}

.stock-alert.warning .alert-icon { color: #d97706; }
.stock-alert.success .alert-icon { color: #16a34a; }

.alert-content h4 {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 6px 0;
    color: #b45309;
}

.alert-content p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
    font-weight: 500;
}

.mini-stock-list {
    list-style: none;
    padding: 0;
    margin: 12px 0 0 0;
    font-size: 13px;
    color: #92400e;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mini-stock-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(217, 119, 6, 0.1);
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid rgba(217, 119, 6, 0.2);
}

.mini-stock-list .item-name {
    font-weight: 600;
    color: #b45309;
}

.mini-stock-list .item-stock {
    background: #ef4444;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
}

.recent-orders-timeline {
    margin-top: 10px;
}

.timeline-header h4 {
    font-size: 14px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 16px;
}

.timeline-items {
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 20px;
    padding-bottom: 20px;
    border-left: 2px solid #f1f5f9;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.item-marker {
    position: absolute;
    left: -6px;
    top: 4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #6366f1;
    border: 2px solid white;
}

.item-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}

.item-main {
    display: flex;
    flex-direction: column;
}

.order-code {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
}

.order-customer {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

.item-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.order-amount {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}

.badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}
.badge.red { background: #fee2e2; color: #ef4444; }

.status-badge {
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.025em;
}
.status-badge.completed { background: #dcfce7; color: #166534; }
.status-badge.shipping { background: #e0f2fe; color: #075985; }
.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.cancelled { background: #fee2e2; color: #991b1b; }
.status-badge.confirmed { background: #f1f5f9; color: #475569; }

/* Responsive */
@media (max-width: 1024px) {
  .charts-grid-top, .data-tables-grid {
    grid-template-columns: 1fr;
  }
}

.error-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #9ca3af;
    gap: 12px;
}

.error-placeholder i {
    font-size: 48px;
    opacity: 0.3;
}

.text-warning { color: #f59e0b; }
.text-danger { color: #ef4444; }

.kpi-card.has-error {
    border: 1px solid #fef3c7;
}

.loading-state {
    text-align: center;
    padding: 100px 0;
}
.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>