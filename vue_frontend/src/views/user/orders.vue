<template>
  <div class="shopee-container">
    <div class="main-content">
      
      <div class="tabs-header">
        <div 
          v-for="tab in tabs" 
          :key="tab.value"
          @click="changeTab(tab.value)"
          :class="['tab-item', currentStatus === tab.value ? 'active' : '']"
        >
          {{ tab.label }}
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div> Đang tải đơn hàng...
      </div>

      <div v-else class="order-list">
        
        <div v-if="orders.length === 0" class="empty-state">
          <div v-if="isLoggedIn || hasSessionParam">
             <img src="https://deo.shopeemobile.com/shopee/shopee-pcmall-live-sg/orderlist/5fafbb923393b712b96488590b8f781f.png" alt="Empty">
             <p>Chưa có đơn hàng nào</p>
             <button class="btn-solid" @click="$router.push('/')">Mua sắm ngay</button>
          </div>

          <div v-else>
             <img src="https://cdni.iconscout.com/illustration/premium/thumb/login-3305943-2757111.png" alt="Login" style="width: 150px">
             <p>Vui lòng đăng nhập để xem lịch sử đơn hàng của bạn</p>
             <button class="btn-solid" @click="$router.push('/login')">Đăng Nhập Ngay</button>
          </div>
        </div>

        <div v-for="order in orders" :key="order.id" class="order-card">
          
          <div class="card-header">
            <div class="shop-info">
              <span class="favorite-badge">Yêu thích</span>
              <span class="shop-name">Mã đơn: {{ order.order_code }}</span>
              <button class="btn-view-shop">Xem Shop</button>
            </div>
            <div class="order-status" :class="order.order_status">
              {{ getStatusLabel(order.order_status) }}
            </div>
          </div>

          <div class="card-body">
            <div v-for="item in order.order_items" :key="item.id" class="product-item">
              <div class="img-wrapper">
                <img :src="getImageUrl(item.product_image)" alt="Product Image">
              </div>
              <div class="product-info">
                <h3 class="product-name">{{ item.product_name }}</h3>
                <div class="product-variant">Phân loại: {{ item.color }}, {{ item.size }}</div>
                <div class="product-qty">x{{ item.quantity }}</div>
              </div>
              <div class="product-price">
                <span class="old-price">{{ formatCurrency(item.price * 1.2) }}</span>
                <span class="current-price">{{ formatCurrency(item.price) }}</span>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="total-section">
              <span class="total-label">Thành tiền:</span>
              <span class="total-price">{{ formatCurrency(order.total_amount) }}</span>
            </div>

            <div class="action-buttons">
              <div class="btn-group">
                <span class="note-text" v-if="order.order_status === 'cancelled'">Đã hủy</span>
                
                <button v-if="order.order_status === 'completed'"class="btn-solid"@click="handleReview(order.order_code)">Đánh Giá</button>
                <button v-if="order.order_status === 'completed' || order.order_status === 'cancelled'"class="btn-solid"@click="openBuyAgainModal(order)">Mua Lại</button>
                <button v-if="order.order_status === 'pending'" class="btn-outline danger" @click="openCancelModal(order.order_code)">Hủy Đơn</button>
                <button class="btn-outline" @click="viewDetail(order.order_code)">Xem Chi Tiết</button>
              </div>
            </div>
          </div>

        </div>
      </div>
      
      </div>
  </div>

  <!-- Modal Xác Nhận Mua Lại -->
  <div v-if="showConfirmModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="modal-title">Xác Nhận Mua Lại</h3>
        <p class="modal-desc">
          Bạn có chắc muốn thêm tất cả sản phẩm từ đơn <b>{{ selectedOrder?.order_code }}</b> vào giỏ hàng không?
        </p>
        <div class="modal-actions">
          <button @click="closeModals" class="btn-cancel">Hủy Bỏ</button>
          <button @click="executeBuyAgain" class="btn-confirm">Đồng Ý</button>
        </div>
      </div>
    </div>

    <!-- Modal Xác Nhận Hủy Đơn (MỚI) -->
    <div v-if="showCancelConfirmModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="modal-title">Xác Nhận Hủy Đơn</h3>
        <p class="modal-desc">
          Bạn có chắc chắn muốn hủy đơn hàng <b>{{ orderCodeToCancel }}</b> không? Hành động này không thể hoàn tác.
        </p>
        <div class="modal-actions">
          <button @click="closeModals" class="btn-cancel">Quay Lại</button>
          <button @click="executeCancelOrder" class="btn-confirm danger">Xác Nhận Hủy</button>
        </div>
      </div>
    </div>

    <!-- Modal Kết Quả -->
    <div v-if="showResultModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="modal-title">{{ resultTitle }}</h3>
        <p class="modal-desc">{{ resultMessage }}</p>
        <div class="modal-actions">
          <button @click="closeResultModal" class="btn-confirm full-width">Đã Hiểu</button>
        </div>
      </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      orders: [],
      loading: false,
      currentStatus: 'all',
      tabs: [
        { label: 'Tất cả', value: 'all' },
        { label: 'Chờ xác nhận', value: 'pending' },
        { label: 'Đang vận chuyển', value: 'shipping' },
        { label: 'Hoàn thành', value: 'completed' },
        { label: 'Đã hủy', value: 'cancelled' },
        
      ],
      showConfirmModal: false,
      showCancelConfirmModal: false, // Thêm mới
      showResultModal: false,
      selectedOrder: null,   
      orderCodeToCancel: '',  // Thêm mới
      resultTitle: '',       
      resultMessage: '',     
      isSuccess: false,      

      isLoggedIn: false,     
      hasSessionParam: false 
    };
  },
  mounted() {
    this.checkAuthStatus();
    this.fetchOrders();
  },
  methods: {
    checkAuthStatus() {
        this.isLoggedIn = !!localStorage.getItem('token');
        const urlSession = this.$route.query.session_id;
        const localSession = sessionStorage.getItem('cart_session_id');
        this.hasSessionParam = !!(urlSession || localSession);
    },
    async fetchOrders() {
      this.loading = true;
      try {
        const token = localStorage.getItem('token'); 
        this.isLoggedIn = !!token;
        const urlSessionId = this.$route.query.session_id;
        const localSessionId = sessionStorage.getItem('cart_session_id');
        this.hasSessionParam = !!(urlSessionId || localSessionId);
        const params = { 
          status: this.currentStatus,
          page: 1
        };
        if (urlSessionId) {
            params.session_id = urlSessionId;
        } 
        else if (!token && localSessionId) {
            params.session_id = localSessionId;
        }
        const config = {
          params: params,
          headers: {}
        };
        if (token) {
          config.headers['Authorization'] = `Bearer ${token}`;
        }
        const response = await axios.get('/orders', config);
        this.orders = response.data.data.data;
      } catch (error) {
        console.error("Lỗi tải đơn hàng:", error);
      } finally {
        this.loading = false;
      }
    },

    changeTab(val) {
      this.currentStatus = val;
      this.fetchOrders();
    },

    formatCurrency(val) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    },

    getImageUrl(path) {
      if (!path) return 'https://placehold.co/80';
      if (path.startsWith('http')) return path;
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

    viewDetail(orderCode) {
       const currentSession = this.$route.query.session_id;
       this.$router.push({
           path: `/user/order/${orderCode}`,
           query: currentSession ? { session_id: currentSession } : {}
       });
    },

    handleReview(orderCode) {
       this.$router.push({path: `/reviews/${orderCode}`,})
    },
    
    openBuyAgainModal(order) {
      this.selectedOrder = order;
      this.showConfirmModal = true;
    },

    async executeBuyAgain() {
      this.showConfirmModal = false;
      this.loading = true;

      try {
        const token = localStorage.getItem('token');
        const urlSessionId = this.$route.query.session_id;
        const localSessionId = sessionStorage.getItem('cart_session_id');
        const sessionId = urlSessionId || localSessionId;

        // Nếu đang dùng session từ URL email, đồng bộ vào sessionStorage ngay
        // để giỏ hàng tìm đúng sản phẩm sau khi redirect
        if (urlSessionId && !token) {
          sessionStorage.setItem('cart_session_id', urlSessionId);
          window.dispatchEvent(new Event('cart-updated'));
        }

        const payload = {
          order_id: this.selectedOrder.id,
          session_id: sessionId
        };

        const config = { headers: {} };
        if (token) config.headers['Authorization'] = `Bearer ${token}`;

        const response = await axios.post('/cart/buy-again', payload, config);

        this.showResultModal = true;
        this.resultTitle = 'Thành Công';
        this.resultMessage = response.data.message;
        this.isSuccess = true;

      } catch (error) {
        console.error(error);
        this.showResultModal = true;
        this.resultTitle = 'Thất Bại';
        this.resultMessage = error.response?.data?.message || 'Có lỗi xảy ra.';
        this.isSuccess = false;
      } finally {
        this.loading = false;
      }
    },

    closeModals() {
      this.showConfirmModal = false;
      this.showCancelConfirmModal = false; // Mới
      this.selectedOrder = null;
      this.orderCodeToCancel = ''; // Mới
    },

    closeResultModal() {
      this.showResultModal = false;
      if (this.isSuccess) {
        this.$router.push('/user/cart');
      }
    },

    openCancelModal(orderCode) {
        this.orderCodeToCancel = orderCode;
        this.showCancelConfirmModal = true;
    },

    async executeCancelOrder() {
        this.showCancelConfirmModal = false;
        this.loading = true;
        try {
            const orderCode = this.orderCodeToCancel;
            const token = localStorage.getItem('token');
            const localSessionId = sessionStorage.getItem('cart_session_id');
            const urlSessionId = this.$route.query.session_id;
            const sessionId = urlSessionId || localSessionId;

            const config = { headers: {} };
            if (token) config.headers['Authorization'] = `Bearer ${token}`;
            
            const params = {};
            if (!token && sessionId) params.session_id = sessionId;

            await axios.post(`/orders/${orderCode}/cancel`, {}, { 
                ...config,
                params: params
            });

            this.showResultModal = true;
            this.resultTitle = 'Thành Công';
            this.resultMessage = 'Đơn hàng đã được hủy thành công.';
            this.isSuccess = false; 
            this.fetchOrders(); 
        } catch (error) {
            console.error(error);
            this.showResultModal = true;
            this.resultTitle = 'Lỗi';
            this.resultMessage = error.response?.data?.message || 'Không thể hủy đơn hàng.';
        } finally {
            this.loading = false;
        }
    }
  }
};
</script>

<style scoped>
.loading-state {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    padding: 20px;
    color: #555;
}
.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-top-color: #a08b7a;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}

.shopee-container {
    background-color: #f8f7f4;
    min-height: 100vh;
    padding-top: 120px;
    font-family: 'Outfit', sans-serif;
}
.main-content {
    max-width: 980px;
    margin: 0 auto;
}
.tabs-header {
    background: #fff;
    display: flex;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,.04);
}
.tab-item {
    flex: 1;
    text-align: center;
    padding: 16px 0;
    cursor: pointer;
    font-size: 15px;
    color: #666;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    transition: all 0.3s;
}
.tab-item:hover {
    color: #a08b7a;
}
.tab-item.active {
    color: #5a4d44;
    border-bottom: 2px solid #a08b7a;
    font-weight: 800;
}
.empty-state {
    background: #fff;
    text-align: center;
    padding: 100px 0;
    width: 100%;
    border-radius: 12px;
}
.empty-state img {
    width: 100px;
    margin-bottom: 20px;
    opacity: 0.6;
}
.order-card {
    background: #fff;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,.04);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #f0eae3;
}
.card-header {
    padding: 20px;
    border-bottom: 1px solid #f0eae3;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.shop-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.favorite-badge {
    background: #a08b7a;
    color: #fff;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.shop-name {
    font-weight: 700;
    color: #5a4d44;
    font-size: 16px;
}
.btn-view-shop {
    background: #fff;
    border: 1px solid #e6e0d8;
    color: #888;
    padding: 4px 10px;
    font-size: 12px;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s;
}
.btn-view-shop:hover {
    background: #f8f7f4;
    color: #a08b7a;
}
.order-status {
    text-transform: uppercase;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 1px;
}
.order-status.pending { color: #a08b7a; }
.order-status.confirmed, .order-status.completed { color: #28a745; }
.order-status.shipping { color: #007bff; }
.order-status.cancelled, .order-status.failed { color: #dc3545; }

.card-body {
    padding: 20px;
}
.product-item {
    display: flex;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f8f7f4;
    margin-bottom: 15px;
}
.product-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.img-wrapper img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #f0eae3;
}
.product-info {
    flex: 1;
}
.product-name {
    font-size: 18px;
    margin: 0 0 8px;
    font-weight: 700;
    color: #5a4d44;
}
.product-variant {
    color: #888;
    font-size: 14px;
}
.product-qty {
    font-size: 15px;
    color: #666;
    margin-top: 5px;
}
.product-price {
    text-align: right;
}
.old-price {
    text-decoration: line-through;
    color: #bbb;
    font-size: 13px;
    margin-right: 8px;
}
.current-price {
    color: #5a4d44;
    font-size: 18px;
    font-weight: 700;
}
.card-footer {
    background: #fdfcfb;
    padding: 20px 24px;
    border-top: 1px solid #f0eae3;
}
.total-section {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.total-label {
    font-size: 15px;
    color: #666;
}
.total-price {
    font-size: 26px;
    color: #a08b7a;
    font-weight: 800;
}
.action-buttons {
    display: flex;
    justify-content: flex-end;
}
.btn-group {
    display: flex;
    gap: 12px;
    align-items: center;
}
.btn-solid {
    background: #a08b7a;
    color: #fff;
    border: none;
    padding: 12px 35px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 700;
    transition: all 0.3s;
}
.btn-solid:hover {
    background: #5a4d44;
    transform: translateY(-2px);
}
.btn-outline {
    background: #fff;
    color: #5a4d44;
    border: 1px solid #e6e0d8;
    padding: 12px 25px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 700;
    transition: all 0.3s;
}
.btn-outline:hover {
    background: #f8f7f4;
    border-color: #a08b7a;
    color: #a08b7a;
}
.btn-outline.danger {
    color: #dc3545;
    border-color: #ffccc7;
}
.btn-outline.danger:hover {
    background: #fff1f0;
    border-color: #dc3545;
    color: #dc3545;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(90, 77, 68, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease-out;
}
.modal-content {
  background: #fff;
  width: 90%;
  max-width: 450px;
  border-radius: 24px;
  border: 1px solid #e6e0d8;
  padding: 40px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.1);
  text-align: center;
}
.modal-title {
    color: #5a4d44;
    font-size: 22px;
}
.btn-confirm {
  background: #a08b7a;
  border-color: #a08b7a;
}
.btn-confirm.danger {
  background: #dc3545;
}
.btn-confirm:hover {
  opacity: 0.9;
}

.full-width {
  width: 100%;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>