<template>
  <div class="cart-container">
    <h2>Giỏ hàng của bạn</h2>

    <div v-if="loading" class="loading">Đang tải giỏ hàng...</div>

    <div v-else-if="cartItems.length === 0" class="empty-cart">
      <p>Giỏ hàng đang trống.</p>
      <router-link to="/products" class="continue-shopping">Tiếp tục mua sắm</router-link>
    </div>

    <div v-else>
      <table class="cart-table">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Đơn giá</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in cartItems" :key="item.id">
            <td class="product-col">
              <div class="product-info">
                <img :src="item.image ? item.image : 'https://placehold.co/300x300?text=No+Image'" alt="Product Image" class="product-img" />
                <div class="product-details">
                  <span class="product-name">{{ item.name }}</span>
                  <div class="attributes" v-if="item.variant_info">
                    <span v-for="(val, key) in item.variant_info" :key="key" class="attr-item">
                      {{ key }}: <strong>{{ val }}</strong>
                    </span>
                  </div>
                </div>
              </div>
            </td>

            <td>
              <span class="price">{{ formatCurrency(item.unit_price) }}</span>
              <br>
              <small v-if="item.original_price > item.unit_price" class="old-price">
                {{ formatCurrency(item.original_price) }}
              </small>
            </td>

            <td>
              <div class="quantity-controls">
                <button class="btn-qty decrease" @click="updateQuantity(item.id, item.quantity - 1)"
                  :disabled="item.quantity <= 1 || isUpdating === item.id">
                  -
                </button>

                <input type="number" v-model.number="item.quantity" class="qty-input"
                  @change="updateQuantity(item.id, item.quantity)" :disabled="isUpdating === item.id" min="1">

                <button class="btn-qty increase" @click="updateQuantity(item.id, item.quantity + 1)"
                  :disabled="isUpdating === item.id">
                  +
                </button>
              </div>
            </td>

            <td>
              <strong class="line-total">{{ formatCurrency(item.unit_price * item.quantity) }}</strong>
            </td>

            <td>
              <button @click="openDeleteModal(item.id)" class="btn-remove" :disabled="isRemoving === item.id">{{
                isRemoving === item.id ? '...' : 'Xóa' }}</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="cart-summary">
        <div class="summary-row">
          <span>Tổng số lượng:</span>
          <span>{{ summary.total_items }} sản phẩm</span>
        </div>
        <div class="summary-row">
          <span>Tạm tính:</span>
          <span>{{ formatCurrency(clientTotal) }}</span>
        </div>
        <div class="coupon-section">
          <!-- Tab switcher (chỉ hiện khi đã login) -->
          <div v-if="isLoggedIn" class="coupon-tabs">
            <button
              class="coupon-tab"
              :class="{ active: couponTab === 'input' }"
              @click="couponTab = 'input'"
            >Nhập mã</button>
            <button
              class="coupon-tab"
              :class="{ active: couponTab === 'pick' }"
              @click="switchToPick"
            >Mã của tôi
              <span v-if="myCoupons.length" class="coupon-badge">{{ myCoupons.length }}</span>
            </button>
          </div>

          <!-- Panel: Nhập mã tay -->
          <div v-if="couponTab === 'input'" class="coupon-input-group">
            <input v-model="cartCouponCode" type="text" placeholder="Nhập mã khuyến mại">
            <button @click="applyCoupon" :disabled="isApplyingCoupon">Áp dụng</button>
          </div>

          <!-- Panel: Chọn mã từ danh sách -->
          <div v-if="couponTab === 'pick' && isLoggedIn" class="coupon-picker">
            <div v-if="loadingCoupons" class="coupon-loading">Đang tải mã...</div>
            <div v-else-if="myCoupons.length === 0" class="coupon-empty">Bạn chưa có mã khuyến mãi nào.</div>
            <div v-else class="coupon-list">
              <div
                v-for="c in myCoupons"
                :key="c.id"
                class="coupon-card"
                :class="{ selected: cartCouponCode === c.code, personal: personalCouponIds.includes(c.id), disabled: !isCouponApplicable(c) }"
                @click="isCouponApplicable(c) && selectCoupon(c)"
              >
                <div class="coupon-card-header">
                  <span class="coupon-code">{{ c.code }}</span>
                  <span v-if="personalCouponIds.includes(c.id)" class="badge-personal">Của tôi</span>
                </div>
                <p class="coupon-desc">{{ c.description }}</p>
                <div class="coupon-meta">
                  <span v-if="c.discount_type === 'percent'">Giảm {{ c.discount_value }}%{{ c.max_discount > 0 ? ` (tối đa ${formatCurrency(c.max_discount)})` : '' }}</span>
                  <span v-else>Giảm {{ formatCurrency(c.discount_value) }}</span>
                  <span v-if="c.min_order_value > 0" class="coupon-min"> • Đơn từ {{ formatCurrency(c.min_order_value) }}</span>
                </div>
                <div class="coupon-footer">
                  <span class="coupon-exp">HSD: {{ formatDate(c.end_date) }}</span>
                  <span v-if="!isCouponApplicable(c)" class="coupon-warning">Chưa đủ điều kiện</span>
                  <span v-else-if="cartCouponCode === c.code" class="coupon-selected-label">✓ Đã chọn</span>
                </div>
              </div>
            </div>
          </div>

          <small v-if="couponMessage" :class="{'text-success': discountAmount > 0, 'text-danger': discountAmount === 0}">{{ couponMessage }}</small>
        </div>
        <div class="summary-row" v-if="discountAmount > 0">
          <span>Khuyến mại:</span>
          <span>-{{ formatCurrency(discountAmount) }}</span>
        </div>
        <div class="summary-row total-row">
          <span>Tổng thanh toán:</span>
          <span class="total-price">{{ formatCurrency(finalTotal) }}</span>
        </div>
        <div class="actions">
          <button class="btn-checkout" @click="handleCheckoutClick">Đặt hàng</button>
        </div>

        <div v-if="showCheckoutForm" class="checkout-modal">
          <div class="checkout-content">
            <h3>Thông tin giao hàng</h3>

            <form @submit.prevent="submitOrder">
              <div class="form-group">
                <label>Họ tên:</label>
                <input v-model="form.full_name" type="text" required placeholder="Nhập họ tên">
              </div>

              <div class="form-group">
                <label>Email:</label>
                <input v-model="form.email" type="email" required placeholder="Nhập email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Vui lòng nhập đúng định dạng email">
              </div>

              <div class="form-group">
                <label>Số điện thoại:</label>
                <input v-model="form.phone" type="text" required placeholder="Nhập số điện thoại (10 số)" pattern="0[0-9]{9}" title="Số điện thoại phải bắt đầu bằng số 0 và bao gồm đúng 10 chữ số">
              </div>

              <div class="form-group">
                <label>Địa chỉ nhận hàng:</label>
                <textarea v-model="form.address" required placeholder="Nhập địa chỉ chi tiết"></textarea>
              </div>

              <div class="form-group">
                <label>Phương thức thanh toán:</label>
                <select v-model="form.payment_method">
                  <option v-if="isLoggedIn" value="cod">Thanh toán khi nhận hàng (COD)</option>
                  <option value="vnpay">Thanh toán VNPAY</option>
                </select>
                <p v-if="!isLoggedIn" class="payment-hint">
                  * Khách vãng lai bắt buộc thanh toán qua VNPay để xác nhận đơn hàng.
                </p>
              </div>

              <div class="form-group">
                <label>Ghi chú đơn hàng:</label>
                <textarea v-model="form.note" placeholder="Giao hàng bao giờ..."></textarea>
              </div>

              <div class="form-actions">
                <button type="button" @click="showCheckoutForm = false" :disabled="isProcessing">Hủy</button>
                <button type="submit" class="btn-confirm" :disabled="isProcessing">
                  {{ isProcessing ? 'Đang xử lý...' : 'Xác nhận thanh toán' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <Transition name="fade">
      <div v-if="notification.show" class="custom-notification">
        {{ notification.message }}
      </div>
    </Transition>

    <!-- Modal Xóa Sản Phẩm -->
    <div v-if="deleteModal.show" class="modal-overlay">
      <div class="modal-box">
        <h3 class="modal-title">Xác nhận xóa</h3>
        <p class="modal-desc">Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?</p>

        <div class="modal-actions">
          <button class="btn-modal btn-cancel" @click="closeDeleteModal">
            Không
          </button>

          <button class="btn-modal btn-confirm" @click="confirmDelete">
            Có, xóa ngay
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

// --- STATE: Giỏ hàng ---
const cartItems = ref([]);
const summary = ref({ total_items: 0, total_price: 0 });
const loading = ref(true);
const isRemoving = ref(null);
const isUpdating = ref(null);

// --- STATE: Checkout ---
const showCheckoutForm = ref(false);
const isLoggedIn = ref(false);
const isProcessing = ref(false);
const form = ref({
  full_name: '',
  email: '',
  phone: '',
  address: '',
  coupon_code: '',
  payment_method: 'cod'
});

// --- STATE: Notification (Thông báo tùy chỉnh) ---
const notification = ref({
  show: false,
  message: ''
});
let notificationTimeout = null;

// --- STATE: Modal Xóa ---
const deleteModal = ref({
  show: false,
  itemId: null // Lưu ID của sản phẩm đang muốn xóa
});

// --- STATE: Coupon ---
const cartCouponCode = ref('');
const discountAmount = ref(0);
const isApplyingCoupon = ref(false);
const couponMessage = ref('');
const couponTab = ref('input');       // 'input' | 'pick'
const myCoupons = ref([]);
const personalCouponIds = ref([]);
const loadingCoupons = ref(false);

// 1. Hàm được gọi khi bấm nút "Xóa" ở danh sách (Thay cho removeItem cũ)
const openDeleteModal = (id) => {
  deleteModal.value.itemId = id;
  deleteModal.value.show = true;
};

// 2. Hàm đóng Modal
const closeDeleteModal = () => {
  deleteModal.value.show = false;
  deleteModal.value.itemId = null;
};

const showNotification = (msg) => {
  // 1. Nếu đang có thông báo cũ thì xóa timeout cũ đi
  if (notificationTimeout) clearTimeout(notificationTimeout);

  // 2. Set nội dung và hiện lên
  notification.value.message = msg;
  notification.value.show = true;

  // 3. Tự động tắt sau 2 giây
  notificationTimeout = setTimeout(() => {
    notification.value.show = false;
  }, 2000);
};

// --- HELPER FUNCTIONS ---

// 1. Format tiền tệ
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// 2. Lấy Session ID (cho khách)
const getSessionId = () => {
  let sessionId = sessionStorage.getItem('cart_session_id');
  if (!sessionId) {
    sessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + Date.now();
    sessionStorage.setItem('cart_session_id', sessionId);
  }
  return sessionId;
};

// 3. Lấy Token (nếu đã login)
const getAuthToken = () => {
  return localStorage.getItem('token');
};

// 4. Tạo Header Auth
const getHeaders = () => {
  const token = getAuthToken();
  return token ? { Authorization: `Bearer ${token}` } : {};
};

// --- API FUNCTIONS ---

// 1. Lấy danh sách giỏ hàng
const fetchCart = async () => {
  // Chỉ hiện loading toàn trang lần đầu, các lần sau update ngầm
  if (cartItems.value.length === 0) loading.value = true;

  try {
    const config = {
      params: { session_id: getSessionId() },
      headers: getHeaders()
    };

    const response = await axios.get('/cart', config);

    if (response.data) {
      cartItems.value = response.data.data || [];
      summary.value = response.data.summary || { total_items: 0, total_price: 0 };
    }
  } catch (error) {
    console.error("Lỗi tải giỏ hàng:", error);
  } finally {
    loading.value = false;
  }
};

// --- COUPON HELPERS ---

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('vi-VN');
};

const isCouponApplicable = (coupon) => {
  return !coupon.min_order_value || clientTotal.value >= coupon.min_order_value;
};

const fetchMyCoupons = async () => {
  if (!isLoggedIn.value) return;
  loadingCoupons.value = true;
  try {
    const res = await axios.get('/cart/my-coupons', { headers: getHeaders() });
    if (res.data.status === 'success') {
      myCoupons.value = res.data.data;
      personalCouponIds.value = res.data.personal_ids || [];
    }
  } catch (e) {
    console.error('Lỗi tải mã coupon:', e);
  } finally {
    loadingCoupons.value = false;
  }
};

const switchToPick = async () => {
  couponTab.value = 'pick';
  if (myCoupons.value.length === 0) {
    await fetchMyCoupons();
  }
};

const selectCoupon = (coupon) => {
  cartCouponCode.value = coupon.code;
  applyCoupon();
};

const applyCoupon = async () =>{
  if (!cartCouponCode.value || !cartCouponCode.value.trim()) {
    couponMessage.value = 'Vui lòng nhập mã khuyến mại';
    discountAmount.value = 0;
    return;
  }

  isApplyingCoupon.value = true;
  couponMessage.value = '';

  try {
    const payload = {
      coupon_code: cartCouponCode.value,
      subtotal: clientTotal.value
    };
    
    const response = await axios.post('/cart/check-coupon', payload, { headers: getHeaders() });
    
    if (response.data.status === 'success') {
      discountAmount.value = response.data.discount_amount;
      couponMessage.value = response.data.message;
      showNotification('Áp dụng mã giảm giá thành công!');
    }
  } catch (error) {
    discountAmount.value = 0;
    if (error.response && error.response.data && error.response.data.message) {
      couponMessage.value = error.response.data.message;
    } else {
      couponMessage.value = 'Mã giảm giá không hợp lệ hoặc đã hết hạn';
    }
  } finally {
    isApplyingCoupon.value = false;
  }
};

// Giúp số nhảy ngay lập tức khi bấm tăng giảm mà không cần chờ Server
const clientTotal = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.unit_price * item.quantity);
  }, 0);
});

const finalTotal = computed(() => {
  return Math.max(0, clientTotal.value - discountAmount.value);
});

watch(clientTotal, () => {
  if (discountAmount.value > 0 || cartCouponCode.value) {
    applyCoupon();
  }
});

// 2. Cập nhật số lượng
const updateQuantity = async (cartId, newQuantity) => {
  // 1. Validate số lượng tối thiểu
  if (newQuantity < 1) return;

  // 2. Validate tồn kho ngay tại Client (Tránh gọi API nếu đã biết là lố)
  const currentItem = cartItems.value.find(item => item.id === cartId);
  if (currentItem && newQuantity > currentItem.stock_quantity) {
    showNotification(`Kho chỉ còn ${currentItem.stock_quantity} sản phẩm!`);
    return;
  }

  // Bật trạng thái loading cho item này
  isUpdating.value = cartId;

  try {
    const payload = {
      id: cartId,
      quantity: newQuantity,
      session_id: getSessionId()
    };

    await axios.post('/cart/update',
    { ...payload, _method: 'PUT' }, // Gộp payload cũ với _method
    { headers: getHeaders() }       // Giữ nguyên headers
);

    // Thành công -> Load lại giỏ
    await fetchCart();

  } catch (error) {
    console.error("Lỗi cập nhật số lượng:", error);

    // [QUAN TRỌNG] Bắt lỗi từ Backend trả về (400 Bad Request)
    if (error.response && error.response.status === 400) {
      // Backend trả về message lỗi cụ thể
      showNotification(error.response.data.message); // <--- THAY ALERT

      // Nếu backend trả về tồn kho thực tế, ta update lại UI luôn cho đúng
      if (currentItem && error.response.data.current_stock !== undefined) {
        currentItem.quantity = error.response.data.current_stock;
        await fetchCart();
      }
    } else {
      showNotification("Có lỗi xảy ra, vui lòng thử lại!"); // <--- THAY ALERT
    }
  } finally {
    isUpdating.value = null;
  }
};

// 3. Xóa sản phẩm
const confirmDelete = async () => {
  // Lấy ID từ biến state đã lưu ở Hàm 1
  const cartId = deleteModal.value.itemId;

  // Nếu không có ID thì dừng (đề phòng lỗi)
  if (!cartId) return;

  // Đóng modal ngay cho mượt
  closeDeleteModal();

  // Bắt đầu xử lý xóa
  isRemoving.value = cartId;

  try {
    const config = {
      params: { session_id: getSessionId() },
      headers: getHeaders()
    };

    // Gọi API xóa
   await axios.post(`/cart/remove/${cartId}`, 
    {
        _method: 'DELETE' // Dòng này quan trọng: đánh lừa server đây là lệnh DELETE
    }, 
    config
);

    // Load lại giỏ hàng
    await fetchCart();

    // Update số lượng trên header (nếu có)
    window.dispatchEvent(new Event('cart-updated'));

    // Hiện thông báo thành công
    showNotification("Đã xóa sản phẩm thành công!");

  } catch (error) {
    console.error("Lỗi xóa:", error);
    showNotification("Lỗi khi xóa sản phẩm!");
  } finally {
    isRemoving.value = null;
  }
};

// --- LOGIC CHECKOUT ---

// 4. Click nút "Đặt hàng"
const handleCheckoutClick = async () => {
  form.value = {
    full_name: '', email: '', phone: '', address: '',
    payment_method: 'cod'
  };

  try {
    const response = await axios.get('/checkout/info', { headers: getHeaders() });
    const data = response.data;

    if (data.is_logged_in && data.customer_info) {
      isLoggedIn.value = true;
      form.value.full_name = data.customer_info.full_name || '';
      form.value.email = data.customer_info.email || '';
      form.value.phone = data.customer_info.phone || '';
      form.value.address = data.customer_info.address || '';
      form.value.payment_method = 'cod'; // Mặc định COD cho User
    } else {
      isLoggedIn.value = false;
      form.value.payment_method = 'vnpay'; // Bắt buộc VNPay cho Khách
    }
    showCheckoutForm.value = true;
  } catch (error) {
    console.error("Lỗi lấy thông tin user:", error);
    showCheckoutForm.value = true;
  }
};

// 5. Submit form "Xác nhận thanh toán"
const submitOrder = async () => {
  if (cartItems.value.length === 0) {
    showNotification('Giỏ hàng trống!');
    return;
  }

  // Validate Email Regex
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.value.email)) {
    showNotification('Vui lòng nhập địa chỉ email hợp lệ!');
    return;
  }

  // Validate Phone (Exactly 10 digits starting with 0)
  const phoneRegex = /^0\d{9}$/;
  if (!phoneRegex.test(form.value.phone)) {
    showNotification('Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 số (Bắt đầu bằng số 0).');
    return;
  }

  isProcessing.value = true;
  try {
    // --- [SỬA ĐỔI 1]: Bổ sung dữ liệu còn thiếu ---
    const payload = {
      ...form.value, // Gồm: full_name, email, phone, address, payment_method...
      session_id: getSessionId(),
      coupon_code: cartCouponCode.value,
      
      // Gửi thêm tổng tiền (Dù backend sẽ tính lại để bảo mật, nhưng vẫn cần gửi để đối chiếu)
      total_amount: finalTotal.value, 

      // Gửi danh sách sản phẩm (chỉ lấy những trường cần thiết để gọn nhẹ)
      items: cartItems.value.map(item => ({
        id: item.product_id,
        quantity: item.quantity,
        attributes: item.variant_info, // Dùng variant_info cho hệ thống mới
        price: item.unit_price
      }))
    };

    const response = await axios.post('/checkout/process', payload, { headers: getHeaders() });

    if (response.data.success) {
      
      // --- [SỬA ĐỔI 2]: Xử lý Logic SePay / COD ---
      
     if (form.value.payment_method === 'vnpay' && response.data.payment_url) {
        // Redirect sang trang VNPAY Sandbox
        window.location.href = response.data.payment_url;
        return;
      }
      
      // Nếu là COD hoặc thành công bình thường
      showNotification('Đặt hàng thành công!');
      showCheckoutForm.value = false;
      
      // Reset giỏ hàng (CHỈ dành cho COD, VNPay sẽ đợi callback)
      if (form.value.payment_method === 'cod') {
        cartItems.value = [];
        summary.value = { total_items: 0, total_price: 0 };
        window.dispatchEvent(new Event('cart-updated'));
      }
    }

  } catch (error) {
    console.error("Lỗi đặt hàng:", error);
    if (error.response && error.response.data.errors) {
      showNotification('Vui lòng kiểm tra lại thông tin nhập vào.');
    } else {
      showNotification(error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại sau.');
    }
  } finally {
    isProcessing.value = false;
  }
};

// --- Lifecycle ---
onMounted(async () => {
  // Phát hiện trạng thái đăng nhập ngay khi tải trang
  const token = localStorage.getItem('token');
  if (token) {
    try {
      const res = await axios.get('/checkout/info', { headers: { Authorization: `Bearer ${token}` } });
      isLoggedIn.value = !!(res.data && res.data.is_logged_in);
    } catch {
      isLoggedIn.value = false;
    }
  }
  fetchCart();
});
</script>
<style scoped>
/* --- CẤU TRÚC CHUNG & FONT --- */
.cart-container {
  max-width: 1200px;
  margin: 0 auto;
  /* Giữ khoảng cách lớn phía trên để tránh Header */
  padding-top: 120px;
  padding-bottom: 80px;
  padding-left: 20px;
  padding-right: 20px;
  /* Phông chữ hệ thống hiện đại, dễ đọc nhất */
  font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  color: #333;
  /* Màu chữ xám đậm dễ chịu hơn đen tuyền */
  background-color: #fff;
}

h2 {
  font-size: 1.8rem;
  font-weight: 700;
  color: #333333;
  margin-bottom: 30px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e5e5e5;
  /* Đường kẻ mỏng tinh tế */
}

/* --- TRẠNG THÁI LOADING / EMPTY --- */
.loading,
.empty-cart {
  text-align: center;
  padding: 80px 0;
  font-size: 1.1rem;
  color: #666;
}

.continue-shopping {
  display: inline-block;
  margin-top: 15px;
  color: #333333;
  text-decoration: underline;
  font-weight: 600;
}

/* --- BẢNG GIỎ HÀNG --- */
.cart-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 40px;
}

.cart-table th {
  text-align: left;
  padding: 15px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #555;
  background-color: #f9f9f9;
  /* Nền header bảng xám nhẹ */
  border-bottom: 2px solid #eee;
}

.cart-table td {
  padding: 20px 15px;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
  /* Căn giữa theo chiều dọc */
}

/* --- CỘT SẢN PHẨM --- */
.product-info {
  display: flex;
  align-items: center;
  /* Căn giữa ảnh và chữ */
  gap: 20px;
}

.product-img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 4px;
  /* Bo góc nhẹ cho mềm mại */
  border: 1px solid #eee;
}

.product-details {
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #333333;
  margin-bottom: 5px;
  text-decoration: none;
}

.attributes {
  margin-top: 6px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.attr-item {
  font-size: 13px;
  color: #666;
  background: #f8f8f8;
  padding: 2px 8px;
  border-radius: 4px;
  border: 1px solid #eee;
}

.attr-item strong {
  color: #333;
}

/* --- GIÁ & SỐ LƯỢNG --- */
.price {
  font-weight: 600;
  color: #333;
}

.old-price {
  font-size: 0.85rem;
  text-decoration: line-through;
  color: #999;
  margin-left: 8px;
}

.qty-badge {
  display: inline-block;
  padding: 5px 12px;
  background: #f5f5f5;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.9rem;
  color: #333;
}

.line-total {
  font-size: 1.1rem;
  font-weight: 700;
  color: #333333;
}

/* --- NÚT XÓA --- */
.btn-remove {
  background: transparent;
  border: none;
  color: #080808;
  cursor: pointer;
  font-size: 0.9rem;
  transition: color 0.2s;
}

.btn-remove:hover {
  color: #ee2629;
  /* Hover vào hiện màu đỏ cảnh báo */
  text-decoration: underline;
}

.btn-remove:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* --- TỔNG KẾT GIỎ HÀNG --- */
.cart-summary {
  margin-left: auto;
  /* Đẩy sang phải */
  width: 100%;
  max-width: 350px;
  background: #fcfcfc;
  padding: 25px;
  border-radius: 8px;
  border: 1px solid #eee;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 0.95rem;
  color: #555;
}

.coupon-section {
  margin: 15px 0;
  padding: 15px 0;
  border-top: 1px dashed #eee;
  border-bottom: 1px dashed #eee;
}

.coupon-input-group {
  display: flex;
  gap: 8px;
  margin-bottom: 5px;
}

.coupon-input-group input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 0.9rem;
  outline: none;
}

.coupon-input-group input:focus {
  border-color: #A08B7A;
}

.coupon-input-group button {
  padding: 8px 15px;
  background-color: #333;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.coupon-input-group button:hover:not(:disabled) {
  background-color: #555;
}

.coupon-input-group button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.text-success {
  color: #28a745;
  font-size: 0.85rem;
}

.text-danger {
  color: #dc3545;
  font-size: 0.85rem;
}

.total-row {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
  font-weight: 700;
  font-size: 1.2rem;
  color: #333333;
  align-items: center;
}

.total-price {
  color: #d32f2f;
  /* Màu đỏ nổi bật cho tổng tiền */
}


/* Container chính: Viền đen mỏng, không bo góc */
.quantity-controls {
  display: flex;
  align-items: center;
  border: 1px solid #E6E0D8;
  width: fit-content;
  /* Ôm sát nội dung */
  height: 36px;
  /* Chiều cao cố định */
}

/* Các nút bấm (+/-) */
.btn-qty {
  width: 36px;
  /* Vuông 36x36 */
  height: 100%;
  background-color: #fff;
  color: #333333;
  border: none;
  font-size: 18px;
  font-weight: 300;
  /* Nét chữ mảnh hiện đại */
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0;
}

/* Hiệu ứng Hover: Đảo màu (Nền đen chữ trắng) */
.btn-qty:hover:not(:disabled) {
  background-color: #A08B7A;
  color: #fff;
}

/* Trạng thái Disabled (khi số lượng = 1 hoặc đang loading) */
.btn-qty:disabled {
  color: #d1d1d1;
  /* Màu xám nhạt */
  background-color: #fafafa;
  cursor: not-allowed;
}

/* Ô input nhập số */
.qty-input {
  width: 45px;
  height: 100%;
  border: none;
  /* Kẻ vạch ngăn cách giữa 2 nút */
  border-left: 1px solid #A08B7A;
  border-right: 1px solid #E6E0D8;

  text-align: center;
  font-size: 14px;
  font-weight: 600;
  /* Số đậm hơn một chút để rõ ràng */
  color: #333333;
  background: transparent;
  outline: none;
  /* Bỏ viền xanh mặc định khi click vào */

  /* Ẩn mũi tên tăng giảm mặc định của trình duyệt */
  -moz-appearance: textfield;
}

/* Ẩn mũi tên trên Chrome/Safari/Edge */
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* --- NÚT CHECKOUT --- */
.actions {
  margin-top: 25px;
}

.btn-checkout {
  width: 100%;
  background-color: #A08B7A;
  /* Nút đen */
  color: #fff;
  /* Chữ trắng */
  border: none;
  padding: 16px;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 6px;
  /* Bo góc hiện đại */
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-checkout:hover {
  background-color: #333;
  /* Hover sáng hơn 1 chút */
}

/* 1. Lớp phủ mờ (Overlay) */
.checkout-modal {
  position: fixed;
  /* Cố định vị trí so với cửa sổ trình duyệt */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  /* Màu đen mờ 50% */
  display: flex;
  justify-content: center;
  /* Căn giữa ngang */
  align-items: center;
  /* Căn giữa dọc */
  z-index: 9999;
  /* Đảm bảo luôn nổi lên trên cùng (trên Header/Cart) */
  animation: fadeIn 0.3s ease-in-out;
  /* Hiệu ứng hiện dần */
}

/* 2. Hộp chứa nội dung form */
.checkout-content {
  background-color: #fff;
  width: 600px;
  max-width: 90%;
  /* Để không bị tràn màn hình trên mobile */
  max-height: 90vh;
  /* Chiều cao tối đa 90% màn hình */
  overflow-y: auto;
  /* Nếu form dài quá thì hiện thanh cuộn */
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  /* Đổ bóng tạo chiều sâu */
  position: relative;
}

/* Tiêu đề form */
.checkout-content h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333333;
  margin-bottom: 25px;
  text-align: center;
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
}

/* 3. Các trường nhập liệu */
.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  font-size: 0.95rem;
  color: #333;
}

/* Input, Textarea, Select */
.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 12px;
  font-size: 0.95rem;
  font-family: inherit;
  /* Kế thừa font hệ thống */
  border: 1px solid #e5e5e5;
  border-radius: 6px;
  background-color: #f9f9f9;
  transition: border-color 0.2s, background-color 0.2s;
  box-sizing: border-box;
  /* Đảm bảo padding không làm vỡ layout */
}

.form-group textarea {
  resize: vertical;
  /* Chỉ cho phép kéo giãn chiều dọc */
  min-height: 80px;
}

/* Hiệu ứng khi click vào ô nhập */
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #333333;
  /* Viền đen khi focus */
  background-color: #fff;
}

/* 4. Nút bấm (Hủy & Xác nhận) */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 30px;
}

.payment-hint {
  font-size: 12px;
  color: #d32f2f;
  margin-top: 5px;
  font-style: italic;
}

/* Nút Hủy */
.form-actions button[type="button"] {
  padding: 12px 24px;
  background-color: #fff;
  border: 1px solid #ddd;
  color: #555;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.form-actions button[type="button"]:hover {
  background-color: #f1f1f1;
  color: #333333;
}

/* Nút Xác nhận (Style giống nút Checkout ở ngoài) */
.btn-confirm {
  padding: 12px 30px;
  background-color: #A08B7A;
  color: #fff;
  border: none;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-confirm:hover {
  opacity: 0.8;
}

.btn-confirm:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}


/* Container thông báo */
.custom-notification {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  /* Căn chính giữa tuyệt đối */

  background-color: rgba(0, 0, 0, 0.9);
  /* Màu đen, hơi trong suốt nhẹ */
  color: #fff;
  padding: 20px 40px;

  font-size: 16px;
  font-weight: 500;
  text-align: center;

  z-index: 9999;
  /* Đảm bảo luôn nổi trên cùng */
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  /* Đổ bóng nhẹ */

  /* Vuông vức, không bo góc */
  border: 1px solid #333;
  min-width: 300px;
}

/* Hiệu ứng Fade (Vue Transition) */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Lớp phủ làm mờ nền */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8);
  /* Nền trắng mờ hiện đại */
  backdrop-filter: blur(2px);
  /* Hiệu ứng làm mờ nhẹ phía sau */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9000;
}

/* Hộp Modal chính */
.modal-box {
  background: #fff;
  border: 2px solid #E6E0D8;
  /* Viền đen đậm vuông vức */
  padding: 30px;
  width: 400px;
  max-width: 90%;
  text-align: center;
  box-shadow: 10px 10px 0px #000;
  /* Đổ bóng cứng (Hard shadow) */
  animation: slideIn 0.3s ease;
}

.modal-title {
  font-size: 20px;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 10px;
  color: #333333;
}

.modal-desc {
  font-size: 15px;
  color: #333;
  margin-bottom: 25px;
}

/* Khu vực chứa nút */
.modal-actions {
  display: flex;
  justify-content: center;
  gap: 15px;
}

/* Style chung cho nút Modal */
.btn-modal {
  padding: 10px 25px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid #E6E0D8;
  text-transform: uppercase;
  transition: all 0.2s;
}

/* Nút Hủy */
.btn-cancel {
  background: #fff;
  color: #333333;
}

.btn-cancel:hover {
  background: #f0f0f0;
}

/* Nút Đồng ý */
.btn-confirm {
  background: #A08B7A;
  color: #fff;
}

.btn-confirm:hover {
  background: #333;
  transform: translateY(-2px);
  /* Nhảy lên nhẹ khi hover */
}

/* Animation xuất hiện */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* 5. Animation (Tùy chọn) */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* --- RESPONSIVE MOBILE --- */
@media (max-width: 768px) {
  .cart-container {
    padding-top: 100px;
  }

  .cart-table thead {
    display: none;
    /* Ẩn header bảng trên mobile */
  }

  .cart-table tr {
    display: block;
    border-bottom: 1px solid #eee;
    padding-bottom: 20px;
    margin-bottom: 20px;
  }

  .cart-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border: none;
  }

  /* Riêng cột sản phẩm thì căn trái */
  .cart-table td.product-col {
    justify-content: flex-start;
  }

  /* Thêm label giả để người dùng hiểu trên mobile */
  .cart-table td::before {
    content: attr(data-label);
    /* Cần thêm data-label vào HTML nếu muốn xịn hơn */
    font-weight: 600;
    color: #999;
    font-size: 0.85rem;
  }

  .cart-summary {
    max-width: 100%;
    border: none;
    background: transparent;
    padding: 0;
    margin-top: 20px;
  }
}

/* --- RESPONSIVE CHO MODAL --- */
@media (max-width: 500px) {
  .checkout-content {
    padding: 20px;
    width: 95%;
  }

  .form-actions {
    flex-direction: column-reverse;
    /* Nút xác nhận lên trên, hủy xuống dưới trên mobile */
  }

  .form-actions button {
    width: 100%;
  }
}

/* ── COUPON TABS & PICKER ── */
.coupon-tabs {
  display: flex;
  gap: 0;
  margin-bottom: 10px;
  border: 1px solid #E6E0D8;
  border-radius: 6px;
  overflow: hidden;
}
.coupon-tab {
  flex: 1;
  padding: 9px 12px;
  font-size: 13px;
  font-weight: 600;
  border: none;
  background: #f9f7f5;
  color: #777;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}
.coupon-tab.active {
  background: #A08B7A;
  color: #fff;
}
.coupon-tab:not(:last-child) {
  border-right: 1px solid #E6E0D8;
}
.coupon-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #fff;
  color: #A08B7A;
  font-size: 11px;
  font-weight: 800;
}
.coupon-tab.active .coupon-badge {
  background: rgba(255,255,255,0.25);
  color: #fff;
}

.coupon-picker {
  margin-top: 4px;
}
.coupon-loading, .coupon-empty {
  padding: 14px;
  font-size: 13px;
  color: #999;
  text-align: center;
}

.coupon-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 280px;
  overflow-y: auto;
  padding: 2px 0;
}

.coupon-card {
  border: 1.5px solid #E6E0D8;
  border-radius: 8px;
  padding: 12px 14px;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  background: #fff;
  position: relative;
}
.coupon-card:hover:not(.disabled) {
  border-color: #A08B7A;
  box-shadow: 0 2px 8px rgba(160,139,122,0.15);
}
.coupon-card.selected {
  border-color: #A08B7A;
  background: #fdf8f5;
  box-shadow: 0 2px 12px rgba(160,139,122,0.2);
}
.coupon-card.personal {
  border-left: 4px solid #A08B7A;
}
.coupon-card.disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.coupon-card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}
.coupon-code {
  font-size: 14px;
  font-weight: 800;
  font-family: monospace;
  color: #333;
  letter-spacing: 1px;
  background: #f0ece8;
  padding: 2px 7px;
  border-radius: 4px;
}
.badge-personal {
  font-size: 10px;
  font-weight: 700;
  background: #A08B7A;
  color: #fff;
  padding: 1px 6px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.coupon-desc {
  font-size: 12px;
  color: #555;
  margin: 0 0 4px;
  line-height: 1.4;
}
.coupon-meta {
  font-size: 12px;
  font-weight: 600;
  color: #A08B7A;
}
.coupon-min {
  color: #999;
  font-weight: 400;
}
.coupon-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 6px;
}
.coupon-exp {
  font-size: 11px;
  color: #aaa;
}
.coupon-warning {
  font-size: 11px;
  color: #e53e3e;
  font-weight: 600;
}
.coupon-selected-label {
  font-size: 11px;
  color: #1a7a4a;
  font-weight: 700;
}
</style>