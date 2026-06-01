<template>
  <div class="contact-page">

    <h1 class="title">Liên hệ</h1>

    <section class="card">
      <h2>Thông tin liên hệ</h2>
      <p>22.Décembre luôn sẵn sàng hỗ trợ bạn trong mọi thắc mắc về sản phẩm, đổi trả hoặc đơn hàng.</p>

      <div class="info-block">
        <div class="info-item">
          <span class="label">Email</span>
          <span class="value">support@22decembre.vn</span>
        </div>
        <div class="info-item">
          <span class="label">Hotline</span>
          <span class="value">0900 000 000</span>
        </div>
        <div class="info-item">
          <span class="label">Giờ làm việc</span>
          <span class="value">08:00 – 21:00, Thứ 2 – Chủ Nhật</span>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Gửi tin nhắn</h2>

      <form class="contact-form" @submit.prevent="submitForm">
        <div class="form-row">
          <label>Họ tên</label>
          <input type="text" v-model="form.name" placeholder="Tên của bạn" required>
        </div>

        <div class="form-row">
          <label>Email</label>
          <input type="email" v-model="form.email" placeholder="Email của bạn" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Vui lòng nhập đúng định dạng email">
        </div>

        <div class="form-row">
          <label>Nội dung</label>
          <textarea v-model="form.message" placeholder="Bạn muốn gửi điều gì?" required></textarea>
        </div>

        <button type="submit" class="submit-btn" :disabled="loading">
          {{ loading ? 'Đang gửi...' : 'Gửi Tin Nhắn' }}
        </button>
      </form>
    </section>

  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const form = reactive({
  name: '',
  email: '',
  message: ''
});

const loading = ref(false);

const submitForm = async () => {
  if (!form.name.trim() || !form.message.trim()) {
    Swal.fire('Cảnh báo', 'Vui lòng không để trống thông tin.', 'warning');
    return;
  }
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!emailRegex.test(form.email)) {
    Swal.fire('Cảnh báo', 'Vui lòng nhập địa chỉ email hợp lệ.', 'warning');
    return;
  }

  loading.value = true;
  
  try {
    const response = await axios.post('/contact', form);
    if (response.data.status === 'success') {
      Swal.fire('Thành công', response.data.message || 'Tin nhắn của bạn đã được gửi đi.', 'success');
      form.name = '';
      form.email = '';
      form.message = '';
    } else {
      Swal.fire('Lỗi', response.data.message || 'Có lỗi xảy ra.', 'error');
    }
  } catch (error) {
    Swal.fire('Lỗi kết nối', 'Vui lòng thử lại sau.', 'error');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* ===== PAGE LAYOUT ===== */
.contact-page {
  max-width: 820px;
  margin: 120px auto;
  padding: 0 20px;
  color: #111;
  font-family: var(--font-body);
}

/* ===== TITLE ===== */
.title {
  font-size: 34px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  text-align: left;
  margin-bottom: 40px;
  border-left: 6px solid #A08B7A;
  padding-left: 16px;
}

/* ===== CARD BLOCK ===== */
.card {
  background: #fff;
  border: 2px solid #E6E0D8;
  border-radius: 0;
  padding: 28px 30px;
  margin-bottom: 28px;
  transition: 0.3s ease;
}

.card:hover {
  background: #f7f7f7;
  transform: translateY(-2px);
}

/* ===== SUBTITLE ===== */
.card h2 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 14px;
  text-transform: uppercase;
}

/* ===== TEXT ===== */
.card p {
  font-size: 15px;
  line-height: 1.6;
  margin-bottom: 18px;
  color: #222;
}

/* ===== INFO BLOCK ===== */
.info-block {
  margin-top: 10px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #cfcfcf;
}

.info-item:last-child {
  border-bottom: none;
}

.label {
  font-size: 14px;
  text-transform: uppercase;
  font-weight: 600;
}

.value {
  font-size: 14px;
  color: #333;
}

/* ===== FORM ===== */
.contact-form .form-row {
  margin-bottom: 16px;
}

label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 6px;
}

input,
textarea {
  width: 100%;
  border: 2px solid #E6E0D8;
  border-radius: 0;
  padding: 10px 12px;
  font-size: 14px;
  background: #fff;
  transition: 0.2s ease;
}

input:focus,
textarea:focus {
  outline: none;
  background: #fafafa;
}

textarea {
  min-height: 120px;
  resize: vertical;
}

/* ===== SUBMIT BUTTON ===== */
.submit-btn {
  background: #A08B7A;
  color: #fff;
  border: 2px solid #A08B7A;
  padding: 12px 18px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 0;
  cursor: pointer;
  transition: 0.25s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.submit-btn:hover:not(:disabled) {
  background: #fff;
  color: #333333;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}


</style>
