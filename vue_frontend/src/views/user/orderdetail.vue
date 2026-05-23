<template>
  <div class="order-detail-page">
    <div class="container">
      
      <div v-if="loading" class="loading-box">
        <div class="spinner"></div>
        <p>Đang tải chi tiết đơn hàng...</p>
      </div>

      <div v-else-if="error" class="error-box">
        <div class="icon">⚠️</div>
        <h3>{{ error }}</h3>
        <button @click="$router.push('/user/orders')" class="btn-back">Quay lại danh sách</button>
      </div>

      <div v-else-if="order" class="content-wrapper">
        
        <div class="section-card header-card">
          <div class="header-left">
             <button @click="$router.go(-1)" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                TRỞ LẠI
             </button>
             <span class="order-code">MÃ ĐƠN: {{ order.order_code }}</span>
          </div>
          <div class="header-right">
             <span class="status-text" :class="order.order_status">{{ getStatusLabel(order.order_status) }}</span>
          </div>
        </div>

        <div class="section-card status-stepper">
           <div :class="['step-item', isStepActive('pending') ? 'active' : '', order.order_status === 'pending' ? 'current' : '']">
              <div class="step-circle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
              </div>
              <div class="step-label">Đặt Hàng</div>
           </div>
           <div :class="['step-line', isStepActive('confirmed') ? 'active' : '']"></div>
           
           <div :class="['step-item', isStepActive('confirmed') ? 'active' : '', order.order_status === 'confirmed' ? 'current' : '']">
              <div class="step-circle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
              <div class="step-label">Đã Xác Nhận</div>
           </div>
           <div :class="['step-line', isStepActive('shipping') ? 'active' : '']"></div>

           <div :class="['step-item', isStepActive('shipping') ? 'active' : '', order.order_status === 'shipping' ? 'current' : '']">
              <div class="step-circle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polyline points="16 8 20 8 23 11 23 16 16 16"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              </div>
              <div class="step-label">Vận Chuyển</div>
           </div>
           <div :class="['step-line', isStepActive('completed') ? 'active' : '']"></div>

           <div :class="['step-item', isStepActive('completed') ? 'active' : '', order.order_status === 'completed' ? 'current' : '']">
              <div class="step-circle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
              </div>
              <div class="step-label">Hoàn Thành</div>
           </div>
        </div>

        <div class="section-card address-card">
           <h3>Địa Chỉ Nhận Hàng</h3>
           <div class="address-info">
              <p class="name">{{ order.full_name }}</p>
              <p class="phone">{{ order.phone }}</p>
              <p class="address">{{ order.address }}</p>
              <p class="note" v-if="order.note">Ghi chú: {{ order.note }}</p>
           </div>
        </div>

        <div class="section-card products-card">
           <div class="product-list">
              <div v-for="item in order.order_items" :key="item.id" class="product-item">
                 <div class="img-wrapper">
                    <img :src="getImageUrl(item.product_image)" alt="sp">
                 </div>
                 <div class="info-wrapper">
                    <div class="name">{{ item.product_name }}</div>
                    <div class="variant">Phân loại: {{ item.color }}, {{ item.size }}</div>
                    <div class="qty">x{{ item.quantity }}</div>
                 </div>
                 <div class="price-wrapper">
                    {{ formatCurrency(item.price) }}
                 </div>
              </div>
           </div>
        </div>

        <div class="section-card summary-card">
           <div class="summary-row">
              <span>Tổng tiền hàng</span>
              <span>{{ formatCurrency(order.subtotal) }}</span>
           </div>
           <div class="summary-row">
              <span>Phí vận chuyển</span>
              <span>{{ formatCurrency(order.shipping_fee) }}</span>
           </div>
           <div class="summary-row" v-if="order.discount_amount > 0">
              <span>Giảm giá Voucher</span>
              <span class="discount">-{{ formatCurrency(order.discount_amount) }}</span>
           </div>
           <div class="summary-row total">
              <span>Thành tiền</span>
              <span class="total-price">{{ formatCurrency(order.total_amount) }}</span>
           </div>
           
            <div class="payment-method-info">
               Phương thức thanh toán: <strong>{{ order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : order.payment_method }}</strong>
               <div class="payment-status-tag" :class="order.payment_status">
                  Trạng thái: {{ getPaymentStatusLabel(order.payment_status) }}
               </div>
            </div>

            <!-- PHẦN MỚI: LỊCH SỬ GIAO DỊCH (chỉ hiện Online) -->
            <div v-if="onlinePayments.length > 0" class="payment-history-section">
               <div class="section-title">LỊCH SỬ GIAO DỊCH</div>
               <div class="payment-log-list">
                  <div v-for="pay in onlinePayments" :key="pay.id" class="payment-log-item">
                     <div class="log-main">
                        <span class="method-name">{{ getPaymentMethodLabel(pay.payment_method) }}</span>
                        <span class="amount">{{ formatCurrency(pay.amount) }}</span>
                     </div>
                     <div class="log-sub">
                        <span class="time">{{ formatDate(pay.created_at) }}</span>
                        <span :class="['status-badge', pay.status === 'success' ? 'success' : 'failed']">
                          {{ pay.status === 'success' ? 'Thành công' : 'Thất bại' }}
                        </span>
                     </div>
                  </div>
               </div>
            </div>
         </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      order: null,
      loading: true,
      error: null
    };
  },
  computed: {
    // Chỉ hiện các giao dịch Online thực sự (loại bỏ COD vì không có giao dịch điện tử)
    onlinePayments() {
      if (!this.order || !this.order.payments) return [];
      // Hiện toàn bộ lịch sử giao dịch online của đơn hàng
      return this.order.payments.filter(p => p.payment_method !== 'cod');
    }
  },
  mounted() {
    this.fetchOrderDetail();
  },
  methods: {
    async fetchOrderDetail() {
      this.loading = true;
      this.error = null;
      try {
        // 1. Lấy mã đơn hàng từ URL
        const token = localStorage.getItem('token'); // Lấy Token nếu có
        const orderCode = this.$route.params.order_code;
        const urlSessionId = this.$route.query.session_id; 
        const localSessionId = sessionStorage.getItem('cart_session_id');
        const sessionId = urlSessionId || localSessionId; // Ưu tiên URL
        // 3. Chuẩn bị Config
        const params = {};
        
        // LOGIC KHỚP VỚI CONTROLLER:
        // Nếu không có Token (Khách vãng lai), Gửi session_id vào params
        if (!token && sessionId) {
            params.session_id = sessionId;
        }

        const config = {
            params: params,
            headers: {}
        };

        // Nếu có Token (User), Gửi vào Header
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        // 4. Gọi API
        // Lưu ý: Route phải khớp với api.php (VD: /api/orders/{code})
        const response = await axios.get(`/orders/${orderCode}`, config);

        this.order = response.data.data;

      } catch (err) {
        console.error(err);
        if (err.response && err.response.status === 401) {
             this.error = "Bạn không có quyền xem đơn hàng này.";
        } else if (err.response && err.response.status === 404) {
             this.error = "Không tìm thấy đơn hàng.";
        } else {
             this.error = "Có lỗi xảy ra khi tải dữ liệu.";
        }
      } finally {
        this.loading = false;
      }
    },

    // --- CÁC HÀM HELPER ---
    formatCurrency(val) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    },
    
    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleString('vi-VN');
    },
    
    getImageUrl(path) {
      if (!path) return 'https://placehold.co/80';
      if (path.startsWith('http')) return path;
      // Dùng Proxy /uploads
      return path.startsWith('/') ? path : '/' + path;
    },

    getStatusLabel(status) {
       const map = {
        'pending': 'CHỜ XÁC NHẬN',
        'confirmed': 'ĐÃ XÁC NHẬN',
        'shipping': 'ĐANG VẬN CHUYỂN',
        'completed': 'HOÀN THÀNH',
        'cancelled': 'ĐÃ HỦY'
      };
      return map[status] || status;
    },

    getPaymentStatusLabel(status) {
      const map = {
        'pending': 'Chưa thanh toán',
        'paid': 'Đã thanh toán',
        'failed': 'Thanh toán thất bại'
      };
      return map[status] || 'Chưa thanh toán';
    },

    getPaymentMethodLabel(method) {
      const map = {
        'vnpay': 'VNPay',
        'momo': 'MoMo',
        'sepay': 'SePay',
        'cod': 'Thanh toán khi nhận hàng'
      };
      return map[method] || method;
    },

    // Helper đơn giản để highlight timeline
    isStepActive(stepName) {
        if (!this.order) return false;
        const status = this.order.order_status;
        const steps = ['pending', 'confirmed', 'shipping', 'completed'];
        // Nếu đã hủy thì không active gì cả hoặc logic riêng
        if (status === 'cancelled') return false;
        
        return steps.indexOf(status) >= steps.indexOf(stepName);
    }
  }
};
</script>

<style scoped>
.order-detail-page {
    background-color: #f8f7f4;
    min-height: 100vh;
    padding: 120px 0;
    font-family: 'Outfit', sans-serif;
    color: #5a4d44;
}
.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 15px;
}

/* CARDS */
.section-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    padding: 24px;
    margin-bottom: 20px;
    border: 1px solid #f0eae3;
}

/* HEADER */
.header-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.back-link {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #888;
    font-size: 14px;
    font-weight: 700;
    transition: color 0.3s;
}
.back-link:hover {
    color: #a08b7a;
}
.order-code {
    margin-left: 15px;
    font-weight: 800;
    font-size: 16px;
    color: #5a4d44;
}
.status-text {
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 16px;
}
.status-text.pending { color: #a08b7a; }
.status-text.confirmed, .status-text.completed { color: #28a745; }
.status-text.shipping { color: #007bff; }
.status-text.cancelled, .status-text.failed { color: #dc3545; }

/* STEPPER */
.status-stepper {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 40px 60px;
    background: #fff;
}
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    flex: 1;
    position: relative;
    z-index: 1;
}
.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #f0eae3;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #bbb;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.step-label {
    font-size: 14px;
    color: #bbb;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.4s;
    white-space: nowrap;
}

/* Active & Current States */
.step-item.active .step-circle {
    background: #a08b7a;
    border-color: #a08b7a;
    color: #fff;
    box-shadow: 0 10px 20px rgba(160, 139, 122, 0.2);
}
.step-item.active .step-label {
    color: #a08b7a;
}

.step-item.current .step-circle {
    background: #5a4d44;
    border-color: #5a4d44;
    transform: scale(1.15);
    box-shadow: 0 0 0 6px rgba(90, 77, 68, 0.1);
}
.step-item.current .step-label {
    color: #5a4d44;
    font-weight: 800;
}

/* Lines */
.step-line {
    flex: 1;
    height: 2px;
    background: #f0eae3;
    margin-top: 25px; /* Căn giữa với vòng tròn */
    position: relative;
    transition: all 0.4s;
}
.step-line.active {
    background: #a08b7a;
}

/* ADDRESS */
.address-card h3 {
    margin: 0 0 15px 0;
    font-size: 20px;
    font-weight: 800;
    color: #5a4d44;
}
.address-info p {
    margin: 10px 0;
    font-size: 15px;
    color: #666;
}
.address-info .name {
    font-weight: 800;
    color: #5a4d44;
    font-size: 16px;
}

/* PRODUCTS */
.product-item {
    display: flex;
    gap: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f8f7f4;
    margin-bottom: 20px;
}
.product-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.img-wrapper img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #f0eae3;
}
.info-wrapper {
    flex: 1;
}
.info-wrapper .name {
    font-size: 18px;
    color: #5a4d44;
    font-weight: 700;
    margin-bottom: 8px;
}
.info-wrapper .variant {
    font-size: 14px;
    color: #888;
}
.info-wrapper .qty {
    margin-top: 8px;
    font-size: 15px;
    color: #555;
}
.price-wrapper {
    font-weight: 800;
    color: #5a4d44;
    font-size: 17px;
}

/* SUMMARY */
.summary-card {
    background: #fdfcfb;
    border-top: 1px solid #f0eae3;
}
.summary-row {
    display: flex;
    justify-content: flex-end;
    gap: 30px;
    margin-bottom: 12px;
    font-size: 15px;
    color: #666;
}
.summary-row span:last-child {
    width: 150px;
    text-align: right;
    color: #5a4d44;
    font-weight: 600;
}
.summary-row.total {
    margin-top: 25px;
    font-size: 20px;
    align-items: center;
    border-top: 1px dashed #e6e0d8;
    padding-top: 20px;
}
.total-price {
    color: #a08b7a !important;
    font-size: 32px;
    font-weight: 800;
}
.payment-method-info {
    text-align: right;
    margin-top: 20px;
    font-size: 14px;
    color: #888;
}
.payment-status-tag {
    margin-top: 8px;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 13px;
}
.payment-status-tag.paid { color: #28a745; }
.payment-status-tag.pending { color: #a08b7a; }
.payment-status-tag.failed { color: #dc3545; }

/* TRANSACTION HISTORY */
.payment-history-section {
    margin-top: 40px;
    border-top: 1px solid #f0eae3;
    padding-top: 25px;
}
.payment-history-section .section-title {
    font-size: 14px;
    font-weight: 800;
    margin-bottom: 20px;
    color: #a08b7a;
    letter-spacing: 2px;
}
.payment-log-item {
    background: #fff;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 12px;
    border: 1px solid #f0eae3;
    transition: transform 0.2s;
}
.payment-log-item:hover { transform: translateX(5px); }
.log-main {
    display: flex;
    justify-content: space-between;
    font-weight: 800;
    font-size: 16px;
    color: #5a4d44;
}
.log-sub {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #999;
    margin-top: 5px;
}
.status-badge {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 4px;
}
.status-badge.success { background: #e6f4ea; color: #28a745; border: 1px solid #c3e6cb; }
.status-badge.failed { background: #fce8e6; color: #dc3545; }

/* LOADING & ERROR */
.spinner {
    border-top: 3px solid #a08b7a;
}
.btn-back {
    margin-top: 20px;
    padding: 12px 35px;
    background: #a08b7a;
    color: #fff;
    border-radius: 8px;
    font-weight: 700;
    font-size: 15px;
    transition: all 0.3s;
}
.btn-back:hover { background: #5a4d44; transform: translateY(-2px); }
</style>