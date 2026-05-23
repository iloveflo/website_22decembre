<template>
  <div class="lookup-container">
    <div class="lookup-card">
      <div class="header">
        <h2>TRA CỨU LỊCH SỬ GIAO DỊCH</h2>
        <p>Nhập thông tin để xem lại nhật ký thanh toán của bạn</p>
      </div>

      <form @submit.prevent="handleLookup" class="lookup-form">
        <div class="form-group">
          <label>Mã đơn hàng</label>
          <input 
            v-model="form.order_code" 
            type="text" 
            placeholder="Ví dụ: ORD12345..." 
            required
          />
        </div>
        <div class="form-group">
          <label>Số điện thoại</label>
          <input 
            v-model="form.phone" 
            type="text" 
            placeholder="Số điện thoại lúc đặt hàng" 
            required
          />
        </div>
        
        <button type="submit" :disabled="loading" class="btn-lookup">
          {{ loading ? 'ĐANG TÌM KIẾM...' : 'XEM HÓA ĐƠN' }}
        </button>
      </form>

      <div v-if="error" class="error-msg">{{ error }}</div>
    </div>

    <!-- Hiển thị kết quả (Hóa đơn) khi tìm thấy -->
    <div v-if="order" class="receipt-wrapper animate-in">
       <div class="receipt-header">
          <h3>HÓA ĐƠN THANH TOÁN</h3>
          <span class="badge" :class="order.payment_status">{{ order.payment_status === 'paid' ? 'ĐÃ THANH TOÁN' : 'CHỜ THANH TOÁN' }}</span>
       </div>
       
       <div class="receipt-body">
          <div class="info-row">
            <span>Khách hàng:</span>
            <strong>{{ order.full_name }}</strong>
          </div>
          <div class="info-row">
            <span>Ngày đặt:</span>
            <strong>{{ formatDate(order.created_at) }}</strong>
          </div>
          
          <div class="divider"></div>
          
          <div class="items-list">
             <div v-for="item in order.order_items" :key="item.id" class="item">
                <span>{{ item.product_name }} x{{ item.quantity }}</span>
                <span>{{ formatCurrency(item.price * item.quantity) }}</span>
             </div>
          </div>
          
          <div class="divider"></div>
          
          <div class="total-row">
             <span>TỔNG CỘNG:</span>
             <span class="total-amount">{{ formatCurrency(order.total_amount) }}</span>
          </div>

          <!-- Lịch sử thanh toán -->
          <div v-if="order.payments && order.payments.length > 0" class="payment-history">
             <p class="section-label">Lịch sử giao dịch:</p>
             <div v-for="pay in order.payments" :key="pay.id" class="pay-log">
                <div class="pay-info">
                   <span class="bank">{{ pay.bank_code }}</span>
                   <span class="time">{{ formatDate(pay.created_at) }}</span>
                </div>
                <span class="status" :class="pay.status">{{ pay.status === 'success' ? 'Thành công' : 'Thất bại' }}</span>
             </div>
          </div>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const loading = ref(false);
const error = ref('');
const order = ref(null);

const form = reactive({
  order_code: '',
  phone: ''
});

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleString('vi-VN');
};

const handleLookup = async () => {
  loading.value = true;
  error.value = '';
  order.value = null;

  try {
    const response = await axios.get('/orders/lookup', {
      params: form
    });
    order.value = response.data.data;
  } catch (err) {
    error.value = err.response?.data?.message || 'Không tìm thấy thông tin đơn hàng';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  // Nếu có query params từ trang khác nhảy sang (ví dụ từ PaymentResult)
  if (route.query.code && route.query.phone) {
    form.order_code = route.query.code;
    form.phone = route.query.phone;
    handleLookup();
  }
});
</script>

<style scoped>
.lookup-container {
  max-width: 600px;
  margin: 150px auto;
  padding: 0 20px;
}

.lookup-card {
  background: #fff;
  padding: 30px;
  border: 1px solid #E6E0D8;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  margin-bottom: 30px;
}

.header { text-align: center; margin-bottom: 25px; }
.header h2 { font-size: 20px; font-weight: 800; letter-spacing: 2px; margin-bottom: 10px; }
.header p { font-size: 14px; color: #999; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px; color: #333; }
.form-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #E6E0D8;
  background: #fafafa;
  font-family: inherit;
}

.btn-lookup {
  width: 100%;
  padding: 15px;
  background: #333;
  color: #fff;
  border: none;
  font-weight: 800;
  cursor: pointer;
  letter-spacing: 1px;
}

.error-msg { color: #d00; font-size: 13px; text-align: center; margin-top: 15px; font-weight: 600; }

/* Receipt Style */
.receipt-wrapper {
  background: #fff;
  border: 1px solid #E6E0D8;
  animation: slideUp 0.4s ease-out;
}

.receipt-header {
  background: #A08B7A;
  color: #fff;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.receipt-header h3 { font-size: 16px; font-weight: 800; margin: 0; }

.badge { font-size: 10px; font-weight: 800; padding: 4px 10px; background: rgba(0,0,0,0.2); }
.badge.paid { background: #1e7e34; }

.receipt-body { padding: 30px; }
.info-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; }
.divider { border-top: 1px dashed #E6E0D8; margin: 15px 0; }

.item { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; }

.total-row { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
.total-row span { font-weight: 800; font-size: 18px; }
.total-amount { color: #A08B7A; }

.payment-history { margin-top: 30px; }
.section-label { font-size: 11px; font-weight: 800; color: #999; text-transform: uppercase; margin-bottom: 15px; }

.pay-log {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f9f9f9;
  margin-bottom: 8px;
}
.pay-info .bank { display: block; font-weight: 800; font-size: 13px; }
.pay-info .time { font-size: 11px; color: #999; }
.status { font-size: 10px; font-weight: 800; }
.status.success { color: #1e7e34; }

@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
