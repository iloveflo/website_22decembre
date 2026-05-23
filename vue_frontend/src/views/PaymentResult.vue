<template>
  <div class="payment-result-wrapper">
    <!-- Nền trang trí mờ ảo -->
    <div class="background-blobs">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
      <p>Đang xác thực giao dịch cao cấp...</p>
    </div>

    <div v-else class="content-container animate-fade-in">
      
      <!-- Cột chính: Kết quả & Chi tiết -->
      <main class="main-card">
        
        <!-- Header: Trạng thái & Hero -->
        <header class="status-hero" :class="{ 'is-success': isSuccess, 'is-failed': !isSuccess }">
          <div class="status-icon-wrap">
            <div class="icon-circle">
              <svg v-if="isSuccess" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              <svg v-else width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </div>
          </div>
          
          <h1 class="status-title">
            {{ isSuccess ? 'Thanh Toán Thành Công' : (route.query.status === 'failed_guest' ? 'Thanh Toán Thất Bại' : 'Giao Dịch Bị Gián Đoạn') }}
          </h1>
          <p class="status-subtitle">
            <template v-if="isSuccess">
              Cảm ơn bạn đã tin tưởng và lựa chọn 22.Décembre
            </template>
            <template v-else-if="route.query.status === 'failed_guest'">
              Đơn hàng của bạn chưa được đặt và các sản phẩm đã được hoàn trả lại về giỏ hàng. Vui lòng thử lại.
            </template>
            <template v-else>
              Đơn hàng của bạn <strong>đã được đặt thành công</strong> nhưng thanh toán chưa hoàn tất. Vui lòng thử lại hoặc đổi sang COD.
            </template>
          </p>
        </header>

        <div class="card-body">
          <!-- Thông tin khách hàng & Giao nhận -->
          <section class="info-grid">
            <div class="info-block">
              <label>Khách hàng</label>
              <p class="font-bold">{{ resultData.order?.full_name }}</p>
              <p class="text-sm opacity-70">{{ resultData.order?.email }}</p>
              <p class="text-sm opacity-70">{{ resultData.order?.phone }}</p>
            </div>
            <div class="info-block">
              <label>Địa chỉ nhận hàng</label>
              <p class="text-sm line-clamp-2">{{ resultData.order?.address }}</p>
            </div>
          </section>

          <div class="divider"></div>

          <!-- Danh sách sản phẩm -->
          <section class="items-list">
            <h3 class="section-label">Chi tiết đơn hàng #{{ resultData.order?.order_code }}</h3>
            <div v-for="item in resultData.order?.order_items" :key="item.id" class="product-row">
              <div class="product-info">
                <span class="product-name">{{ item.product_name }}</span>
                <span class="product-meta" v-if="item.size || item.color">
                  {{ item.size ? 'Size: ' + item.size : '' }} {{ item.color ? ' | Màu: ' + item.color : '' }}
                </span>
              </div>
              <div class="product-price">
                <span class="qty">x{{ item.quantity }}</span>
                <span class="price">{{ formatCurrency(item.price * item.quantity) }}</span>
              </div>
            </div>
          </section>

          <div class="divider"></div>

          <!-- Bảng tính tiền -->
          <section class="billing-summary">
            <div class="bill-row">
              <span>Tạm tính</span>
              <span>{{ formatCurrency(resultData.order?.subtotal) }}</span>
            </div>
            <div class="bill-row" v-if="resultData.order?.discount_amount > 0">
              <span>Giảm giá</span>
              <span class="text-discount">- {{ formatCurrency(resultData.order?.discount_amount) }}</span>
            </div>
            <div class="bill-row">
              <span>Phí vận chuyển</span>
              <span>{{ formatCurrency(resultData.order?.shipping_fee || 0) }}</span>
            </div>
            <div class="bill-row grand-total">
              <span>TỔNG THANH TOÁN</span>
              <span class="amount">{{ formatCurrency(resultData.order?.total_amount) }}</span>
            </div>
          </section>
        </div>

        <!-- Footer: Thành công hoặc Khách vãng lai thất bại -->
        <footer v-if="isSuccess || route.query.status === 'failed_guest'" 
          :class="['card-actions', { 'is-single': route.query.status === 'failed_guest' }]">
          <button v-if="isSuccess" @click="goHome" class="btn-outline">Quay về trang chủ</button>
          <router-link
            v-if="isSuccess && resultData.order"
            :to="{ path: '/user/order/' + resultData.order.order_code, query: resultData.order.session_id ? { session_id: resultData.order.session_id } : {} }"
            class="btn-filled"
          >Theo dõi đơn hàng</router-link>

          <router-link v-if="route.query.status === 'failed_guest'" to="/user/cart" class="btn-filled btn-retry">
            Quay lại giỏ hàng & Thử lại
          </router-link>
        </footer>

        <!-- Footer: User đã login bị thất bại → BẮT BUỘC chọn + Đếm ngược -->
        <footer v-else-if="!isSuccess && resultData.order" class="card-actions-forced">
          <!-- Banner đếm ngược -->
          <div class="auto-cod-banner">
            <div class="banner-icon">⏱️</div>
            <div class="banner-text">
              <strong>Đơn hàng đã được ghi nhận — Email xác nhận đã gửi!</strong>
              <p>
                Nếu bạn <u>thoát hoặc rời khỏi trang này</u>, hệ thống sẽ <strong>tự động chuyển đơn hàng sang Thanh toán khi nhận hàng (COD)</strong>.<br>
                Còn lại: <strong>{{ countdownDisplay }}</strong> để hoàn tất phương thức thanh toán.
              </p>
            </div>
          </div>
          <div class="forced-buttons">
            <button @click="handleRetryVnpay" class="btn-forced btn-vnpay" :disabled="isRetrying">
              <span class="btn-text">
                <strong>Thử lại VNPay</strong>
                <small>Thanh toán an toàn qua cổng VNPAY</small>
              </span>
            </button>
            <button @click="showCodModal = true" class="btn-forced btn-cod" :disabled="isRetrying">
              <span class="btn-text">
                <strong>Thanh toán khi nhận hàng</strong>
                <small>Trả tiền mặt khi nhận được hàng</small>
              </span>
            </button>
          </div>
        </footer>
      </main>
    </div>

    <!-- Custom Modal xác nhận đổi sang COD -->
    <Transition name="modal-fade">
      <div v-if="showCodModal" class="modal-overlay" @click.self="showCodModal = false">
        <div class="modal-box">
          <h3 class="modal-title">Xác nhận đổi phương thức</h3>
          <p class="modal-desc">Bạn muốn chuyển đơn hàng <strong>#{{ resultData.order?.order_code }}</strong> sang thanh toán khi nhận hàng (COD)?</p>
          <p class="modal-sub">Nhân viên giao hàng sẽ thu tiền khi giao đến tay bạn.</p>
          <div class="modal-actions">
            <button class="modal-btn modal-cancel" @click="showCodModal = false">Hủy bỏ</button>
            <button class="modal-btn modal-confirm" @click="confirmChangeToCod" :disabled="isRetrying">
              {{ isRetrying ? 'Đang xử lý...' : 'Xác nhận đổi sang COD' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const resultData = ref({});
const isSuccess = ref(false);
const isValidSignature = ref(false);
const isRetrying = ref(false);
const showCodModal = ref(false);
const countdownSeconds = ref(30 * 60); // 30 phút
let countdownTimer = null;
let beaconSent = false;

const countdownDisplay = computed(() => {
  const m = Math.floor(countdownSeconds.value / 60).toString().padStart(2, '0');
  const s = (countdownSeconds.value % 60).toString().padStart(2, '0');
  return `${m}:${s}`;
});

const formatCurrency = (value) => {
  if (!value && value !== 0) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleString('vi-VN', {
    hour: '2-digit',
    minute: '2-digit',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const goHome = () => router.push('/');

const handleRetryVnpay = async () => {
  isRetrying.value = true;
  try {
    const response = await axios.post('/checkout/retry-vnpay', {
      order_code: resultData.value.order.order_code
    });
    if (response.data.payment_url) {
      window.location.href = response.data.payment_url;
    }
  } catch (error) {
    alert(error.response?.data?.message || "Lỗi tạo link thanh toán");
  } finally {
    isRetrying.value = false;
  }
};

const autoSwitchToCod = async (orderCode) => {
  if (beaconSent) return;
  beaconSent = true;
  const token = localStorage.getItem('auth_token') || '';
  const apiBase = axios.defaults.baseURL || '';
  const url = apiBase + '/checkout/change-to-cod';
  const data = JSON.stringify({ order_code: orderCode });
  // Dùng fetch keepalive: true — hoạt động cả khi đóng tab/trình duyệt
  fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token ? { Authorization: 'Bearer ' + token } : {})
    },
    body: data,
    keepalive: true
  }).catch(() => {});
};

const startCountdown = (orderCode) => {
  countdownTimer = setInterval(() => {
    countdownSeconds.value--;
    if (countdownSeconds.value <= 0) {
      clearInterval(countdownTimer);
      autoSwitchToCod(orderCode);
      // Sau 3s redirect sang trang đơn hàng
      setTimeout(() => router.push('/user/order/' + orderCode), 3000);
    }
  }, 1000);
};

const confirmChangeToCod = async () => {
  isRetrying.value = true;
  try {
    const response = await axios.post('/checkout/change-to-cod', {
      order_code: resultData.value.order.order_code
    });
    if (response.data.success) {
      showCodModal.value = false;
      // Redirect sang trang đơn hàng thay vì reload (reload sẽ hiện lại 'failed')
      router.push('/user/order/' + resultData.value.order.order_code);
    }
  } catch (error) {
    showCodModal.value = false;
    alert(error.response?.data?.message || 'Lỗi chuyển đổi. Vui lòng thử lại.');
  } finally {
    isRetrying.value = false;
  }
};

// Handler được đặt tên để có thể removeEventListener đúng cách
let _beforeUnloadHandler = null;
let _autoSwitchOrderCode = null;

onMounted(async () => {
  const orderCode = route.query.order_code;
  if (!orderCode) {
    loading.value = false;
    return;
  }

  let attempts = 0;
  const maxAttempts = 3; 

  const fetchResult = async () => {
    try {
      const response = await axios.get('/payment/vnpay-result', {
        params: { order_code: orderCode }
      });

      const apiRes = response.data;
      resultData.value = apiRes.data;

      if (apiRes.status === 'success') {
        isSuccess.value = true;
        isValidSignature.value = true;
        loading.value = false;
        return;
      }

      if (route.query.status === 'success' && attempts < maxAttempts) {
        attempts++;
        setTimeout(fetchResult, 2000);
        return;
      }

      isSuccess.value = route.query.status === 'success' || apiRes.status === 'success';
      isValidSignature.value = route.query.signature_valid === '1' || apiRes.signature_valid;
      loading.value = false;

    } catch (error) {
      console.error("Lỗi xác thực:", error);
      isSuccess.value = route.query.status === 'success';
      isValidSignature.value = route.query.signature_valid === '1';
      loading.value = false;
    }
  };

  fetchResult();

  // Chỉ khởi động countdown và beforeunload cho User login thất bại
  if (route.query.status !== 'success' && route.query.status !== 'failed_guest' && orderCode) {
    _autoSwitchOrderCode = orderCode;
    
    // Đợi tải xong mới start đếm ngược
    setTimeout(() => startCountdown(orderCode), 1500);

    // beforeunload: Dùng named handler để có thể remove đúng cách
    _beforeUnloadHandler = () => autoSwitchToCod(orderCode);
    window.addEventListener('beforeunload', _beforeUnloadHandler);
  }
});

onUnmounted(() => {
  if (countdownTimer) clearInterval(countdownTimer);
  
  // Xóa đúng cách với named handler
  if (_beforeUnloadHandler) {
    window.removeEventListener('beforeunload', _beforeUnloadHandler);
  }

  // [QUAN TRỌNG] Khi rời trang qua Vue Router (click back, navigate),
  // cũng tự động chuyển sang COD (khác với đóng tab/trình duyệt)
  if (_autoSwitchOrderCode && !isSuccess.value && route.query.status !== 'failed_guest') {
    autoSwitchToCod(_autoSwitchOrderCode);
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');

.payment-result-wrapper {
  font-family: 'Outfit', sans-serif;
  min-height: calc(100vh - 100px); /* Trừ hao chiều cao header/footer ước tính */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 100px 20px 80px; /* Tăng padding trên để tránh header che */
  background-color: #f8f7f4;
  position: relative;
  overflow: hidden;
}

/* Background Blobs */
.background-blobs {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  z-index: 0;
  overflow: hidden;
}
.blob {
  position: absolute;
  filter: blur(80px);
  border-radius: 50%;
  opacity: 0.4;
}
.blob-1 {
  width: 400px; height: 400px;
  background: #e6e0d8;
  top: -100px; right: -50px;
}
.blob-2 {
  width: 500px; height: 500px;
  background: #f0eae3;
  bottom: -150px; left: -100px;
}

.content-container {
  position: relative;
  z-index: 1;
  max-width: 650px; /* Độ rộng tối ưu cho 1 cột để trông sang trọng */
  width: 100%;
  margin: 0 auto;
  display: flex;
  justify-content: center;
}

@media (max-width: 700px) {
  .content-container { 
    padding: 0 10px;
  }
}

/* Main Card */
.main-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(15px);
  border: 1px solid rgba(230, 224, 216, 0.6);
  box-shadow: 0 50px 100px rgba(0,0,0,0.08);
  border-radius: 32px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Status Hero */
.status-hero {
  padding: 80px 40px 40px;
  text-align: center;
  position: relative;
}
.status-hero.is-success { background: linear-gradient(180deg, #f0f4f1 0%, transparent 100%); }
.status-hero.is-failed { background: linear-gradient(180deg, #fff5f5 0%, transparent 100%); }

.status-icon-wrap {
  display: inline-flex;
  margin-bottom: 30px;
}
.icon-circle {
  width: 110px; height: 110px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.is-success .icon-circle { background: #1e7e34; color: #fff; box-shadow: 0 25px 50px rgba(30, 126, 52, 0.25); }
.is-failed .icon-circle { background: #d93025; color: #fff; box-shadow: 0 25px 50px rgba(217, 48, 37, 0.25); }

.status-title {
  font-size: 36px;
  font-weight: 800;
  margin-bottom: 12px;
  letter-spacing: -1px;
  color: #5a4d44;
}
.status-subtitle {
  font-size: 17px;
  color: #888;
  max-width: 450px;
  margin: 0 auto;
}

/* Card Body */
.card-body {
  padding: 0 50px 50px;
  flex: 1;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  margin-bottom: 40px;
}
.info-block label {
  display: block;
  font-size: 11px;
  text-transform: uppercase;
  font-weight: 800;
  color: #a08b7a;
  margin-bottom: 10px;
  letter-spacing: 2px;
}
.font-bold { font-weight: 700; color: #5a4d44; font-size: 18px; }
.text-sm { font-size: 15px; }
.opacity-70 { opacity: 0.8; }

.divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, #f0eae3, transparent);
  margin: 40px 0;
}

.section-label {
  font-size: 15px;
  font-weight: 800;
  color: #5a4d44;
  margin-bottom: 25px;
  text-transform: uppercase;
  letter-spacing: 2px;
}

/* Product Row */
.product-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.product-info { display: flex; flex-direction: column; }
.product-name { font-weight: 700; font-size: 16px; color: #5a4d44; }
.product-meta { font-size: 13px; color: #999; margin-top: 2px; }
.product-price { display: flex; gap: 30px; align-items: center; }
.qty { font-size: 15px; color: #666; font-weight: 700; }
.price { font-weight: 700; font-size: 17px; color: #5a4d44; }

/* Billing */
.billing-summary {
  background: #fdfcfb;
  padding: 30px;
  border-radius: 20px;
  margin-bottom: 40px;
  border: 1px solid #f0eae3;
}
.bill-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  font-size: 16px;
  color: #666;
}
.text-discount { color: #dc3545; font-weight: 700; }
.grand-total {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e6e0d8;
  color: #5a4d44;
  font-weight: 800;
}
.grand-total .amount {
  font-size: 28px;
  color: #a08b7a;
}

/* Timeline */
.transaction-history { margin-top: 30px; }
.timeline {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.timeline-item {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}
.dot {
  width: 12px; height: 12px;
  border-radius: 50%;
  margin-top: 8px;
  flex-shrink: 0;
}
.dot.success { background: #28a745; box-shadow: 0 0 0 5px rgba(40, 167, 69, 0.1); }
.dot.failed { background: #dc3545; box-shadow: 0 0 0 5px rgba(220, 53, 69, 0.1); }

.timeline-content {
  flex: 1;
  background: #fff;
  padding: 16px 20px;
  border: 1px solid #f0eae3;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.02);
}
.flex-between { display: flex; justify-content: space-between; align-items: center; }
.bank-name { font-weight: 800; font-size: 15px; color: #5a4d44; }
.timestamp { font-size: 13px; color: #999; }
.tx-id { font-size: 13px; color: #666; margin-top: 6px; font-family: 'Courier New', Courier, monospace; }

/* Actions */
.card-actions {
  padding: 0 50px 50px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.card-actions.is-single {
  grid-template-columns: 1fr;
  max-width: 300px;
  margin: 0 auto;
}
.btn-filled, .btn-outline {
  padding: 18px;
  border-radius: 16px;
  font-weight: 800;
  font-size: 15px;
  cursor: pointer;
  text-align: center;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  text-decoration: none;
}
.btn-filled { background: #a08b7a; color: #fff; border: none; }
.btn-filled:hover { background: #5a4d44; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(160, 139, 122, 0.2); }
.btn-filled.btn-vnpay { background: #1e7e34; }
.btn-filled.btn-cod { background: #5a4d44; }
.btn-outline { background: transparent; border: 2px solid #5a4d44; color: #5a4d44; }
.btn-outline:hover { background: #f8f7f4; border-color: #a08b7a; color: #a08b7a; }



/* Animations */
.animate-fade-in { animation: fadeIn 1s cubic-bezier(0.23, 1, 0.32, 1); }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.loading-overlay {
  text-align: center;
  z-index: 10;
}
.spinner {
  width: 60px; height: 60px;
  border: 4px solid rgba(160, 139, 122, 0.1);
  border-left-color: #a08b7a;
  border-radius: 50%;
  animation: spin 1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
  margin: 0 auto 25px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Banner đếm ngược tự động COD */
.auto-cod-banner {
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, #fff8e1, #fff3cd);
  border: 1.5px solid #ffc107;
  border-radius: 16px;
  padding: 18px 20px;
  margin-bottom: 20px;
}
.auto-cod-banner .banner-icon { font-size: 28px; flex-shrink: 0; }
.auto-cod-banner .banner-text { flex: 1; }
.auto-cod-banner .banner-text strong { display: block; font-size: 14px; color: #856404; margin-bottom: 4px; }
.auto-cod-banner .banner-text p { font-size: 13px; color: #6c5a00; line-height: 1.5; margin: 0; }

/* Footer BẮT BUỘC cho User thất bại */
.card-actions-forced {
  padding: 0 50px 50px;
}
.forced-note {
  text-align: center;
  font-size: 14px;
  color: #888;
  margin-bottom: 20px;
  font-style: italic;
}
.forced-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.btn-forced {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px 24px;
  border: 2px solid transparent;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
  font-family: 'Outfit', sans-serif;
}
.btn-forced .btn-icon { font-size: 28px; flex-shrink: 0; }
.btn-forced .btn-text { display: flex; flex-direction: column; gap: 2px; }
.btn-forced .btn-text strong { font-size: 15px; font-weight: 800; }
.btn-forced .btn-text small { font-size: 12px; opacity: 0.8; }
.btn-forced.btn-vnpay { background: #1e7e34; color: #fff; }
.btn-forced.btn-vnpay:hover:not(:disabled) { background: #155a25; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(30,126,52,0.25); }
.btn-forced.btn-cod { background: #5a4d44; color: #fff; }
.btn-forced.btn-cod:hover:not(:disabled) { background: #3d3330; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(90,77,68,0.25); }
.btn-forced:disabled { opacity: 0.6; cursor: not-allowed; transform: none !important; }

/* Custom Modal COD */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}
.modal-box {
  background: #fff;
  border-radius: 28px;
  padding: 48px 40px 40px;
  max-width: 420px;
  width: 100%;
  text-align: center;
  box-shadow: 0 40px 80px rgba(0,0,0,0.2);
}
.modal-icon { font-size: 52px; margin-bottom: 20px; }
.modal-title { font-size: 22px; font-weight: 800; color: #5a4d44; margin-bottom: 12px; }
.modal-desc { font-size: 15px; color: #555; line-height: 1.6; margin-bottom: 8px; }
.modal-sub { font-size: 13px; color: #999; margin-bottom: 32px; }
.modal-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.modal-btn { padding: 16px; border-radius: 14px; font-size: 14px; font-weight: 800; cursor: pointer; border: none; transition: all 0.3s ease; font-family: 'Outfit', sans-serif; }
.modal-cancel { background: #f0eae3; color: #5a4d44; }
.modal-cancel:hover { background: #e6ddd4; }
.modal-confirm { background: #5a4d44; color: #fff; }
.modal-confirm:hover:not(:disabled) { background: #3d3330; transform: translateY(-2px); }
.modal-confirm:disabled { opacity: 0.6; cursor: not-allowed; }

/* Modal animation */
.modal-fade-enter-active, .modal-fade-leave-active { transition: all 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-from .modal-box, .modal-fade-leave-to .modal-box { transform: scale(0.9) translateY(20px); }
</style>